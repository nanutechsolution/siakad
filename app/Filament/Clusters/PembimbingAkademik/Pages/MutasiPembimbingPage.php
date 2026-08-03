<?php

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikStatus;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Models\PembimbingAkademik;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MutasiPembimbingPage extends Page implements HasTable
{
    use InteractsWithTable;
    protected string $view = 'filament.clusters.pembimbing-akademik.pages.mutasi-pembimbing-page';
    protected static ?string $navigationLabel = 'Mutasi Pembimbing Akademik';
    protected static ?string $modelLabel = 'Mutasi Pembimbing Akademik';
    protected static ?string $clusterBreadcrumb = 'Mutasi Pembimbing Akademik';
    protected static ?int $navigationSort = 3;
    protected static ?string $title = 'Mutasi Pembimbing Akademik';
    protected static ?string $description = 'Halaman ini digunakan untuk melakukan mutasi atau perubahan pembimbing akademik bagi mahasiswa. Anda dapat memindahkan mahasiswa dari satu pembimbing ke pembimbing lainnya, serta mengelola catatan terkait mutasi tersebut. Halaman ini membantu dalam memastikan penugasan pembimbing akademik tetap sesuai dengan kebutuhan mahasiswa dan kebijakan akademik.';
    protected static ?string $slug = 'mutasi-pembimbing-akademik';
    protected static ?string $cluster = PembimbingAkademikCluster::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    public function table(Table $table): Table
    {
        return $table
            ->query(PembimbingAkademik::query()->where('status', PembimbingAkademikStatus::AKTIF))
            ->columns([
                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn(PembimbingAkademikJenis $state) => $state->label()),
                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->placeholder('-'),
                TextColumn::make('mahasiswa.nim')
                    ->label('Mahasiswa')
                    ->placeholder('-')
                    ->description(fn(?PembimbingAkademik $record) => $record?->mahasiswa?->person?->nama_lengkap),
                TextColumn::make('dosen.person.nama_lengkap')
                    ->label('Dosen Saat Ini')
                    ->description(fn(?PembimbingAkademik $record) => $record?->dosen?->nidn),
                TextColumn::make('tanggal_mulai')
                    ->label('Sejak')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('jenis')
                    ->options(PembimbingAkademikJenis::options()),
            ])
            ->recordActions([
                Action::make('mutasi')
                    ->label('Mutasi')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('warning')
                    ->modalHeading(fn(PembimbingAkademik $record) => 'Mutasi Pembimbing: ' . ($record->mahasiswa?->nim ?? $record->kelas?->nama_kelas))
                    ->schema([
                        Select::make('dosen_id')
                            ->label('Dosen Pengganti')
                            ->searchable()
                            ->getSearchResultsUsing(fn(string $search) => TrxDosen::query()
                                ->where('nidn', 'like', "%{$search}%")
                                ->orWhereHas('person', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"))
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(fn(TrxDosen $d) => [$d->id => "{$d->person?->nama_lengkap} ({$d->nidn})"]))
                            ->getOptionLabelUsing(fn($value) => optional(TrxDosen::find($value))->nidn)
                            ->required(),
                        DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai Penugasan Baru')
                            ->default(now())
                            ->required(),
                        Select::make('semester_mulai_id')
                            ->label('Semester Mulai')
                            ->searchable()
                            ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                            ->required(),
                        TextInput::make('nomor_sk')
                            ->label('Nomor SK Mutasi')
                            ->maxLength(255),
                        DatePicker::make('tanggal_sk')
                            ->label('Tanggal SK'),
                        Textarea::make('alasan')
                            ->label('Alasan Mutasi')
                            ->rows(2)
                            ->required(),
                    ])
                    ->action(function (array $data, PembimbingAkademik $record): void {
                        $record->update([
                            'status' => PembimbingAkademikStatus::SELESAI,
                            'tanggal_selesai' => $data['tanggal_mulai'],
                            'semester_selesai_id' => $data['semester_mulai_id'],
                            'updated_by' => auth()->id(),
                        ]);

                        PembimbingAkademik::create([
                            'kelas_id' => $record->kelas_id,
                            'mahasiswa_id' => $record->mahasiswa_id,
                            'dosen_id' => $data['dosen_id'],
                            'jenis' => $record->jenis,
                            'is_primary' => $record->is_primary,
                            'semester_mulai_id' => $data['semester_mulai_id'],
                            'tanggal_mulai' => $data['tanggal_mulai'],
                            'nomor_sk' => $data['nomor_sk'] ?? null,
                            'tanggal_sk' => $data['tanggal_sk'] ?? null,
                            'alasan' => $data['alasan'],
                            'status' => PembimbingAkademikStatus::AKTIF,
                            'created_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Mutasi pembimbing berhasil disimpan')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('tanggal_mulai', 'desc');
    }
}
