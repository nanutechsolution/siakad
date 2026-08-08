<?php

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Domain\Authorization\Services\FormResolver;
use App\Domain\Authorization\Services\OrganizationResolver;
use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikStatus;
use App\Exports\PembimbingAkademikExport;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Models\PembimbingAkademik;
use App\Models\RefProdi;
use App\Support\Utf8;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class RiwayatPembimbingPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Riwayat Pembimbing Akademik';
    protected static ?string $modelLabel = 'Riwayat Pembimbing Akademik';
    protected string $view = 'filament.clusters.pembimbing-akademik.pages.riwayat-pembimbing-page';
    protected static ?int $navigationSort = 2;
    protected static ?string $clusterBreadcrumb = 'Riwayat Pembimbing Akademik';
    protected static ?string $title = 'Riwayat Pembimbing Akademik';
    protected static ?string $description = 'Halaman ini menampilkan riwayat pembimbing akademik yang telah ditugaskan kepada mahasiswa. Anda dapat melihat daftar pembimbing, periode penugasan, dan informasi terkait lainnya. Halaman ini membantu dalam memantau dan mengelola penugasan pembimbing akademik secara efektif.';
    protected static ?string $slug = 'riwayat-pembimbing-akademik';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $cluster = PembimbingAkademikCluster::class;


    public function table(Table $table): Table
    {
        return $table
            ->query(
                PembimbingAkademik::query()
                    ->with([
                        'mahasiswa.person',
                        'kelas',
                        'dosen.person',
                    ])
                    ->withTrashed()
                    ->visibleTo(auth()->user())
            )
            ->columns([
                TextColumn::make('jenis')
                    ->badge()
                    ->formatStateUsing(fn(PembimbingAkademikJenis $state) => $state->label()),
                TextColumn::make('mahasiswa.nim')
                    ->label('Mahasiswa')
                    ->placeholder('-')
                    ->description(fn(?PembimbingAkademik $record) => Utf8::clean($record?->mahasiswa?->person?->nama_lengkap))
                    ->searchable(),
                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->placeholder('-')
                    ->formatStateUsing(fn(?string $state) => $state ? Utf8::clean($state) : null)
                    ->searchable(),
                TextColumn::make('dosen_nama')
                    ->label('Dosen')
                    ->getStateUsing(fn(PembimbingAkademik $record) => Utf8::clean($record->dosen?->person?->nama_lengkap))
                    ->description(fn(?PembimbingAkademik $record) => $record?->dosen?->nidn)
                    ->searchable(query: fn($query, string $search) => $query->whereHas('dosen.person', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"))),
                TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('tanggal_selesai')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(PembimbingAkademikStatus $state) => match ($state) {
                        PembimbingAkademikStatus::AKTIF => 'success',
                        PembimbingAkademikStatus::SELESAI => 'gray',
                        PembimbingAkademikStatus::DIBATALKAN => 'danger',
                    })
                    ->formatStateUsing(fn(PembimbingAkademikStatus $state) => $state->label()),
                TextColumn::make('nomor_sk')
                    ->label('No. SK')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Diinput Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_deleted')
                    ->label('Dihapus')
                    ->boolean()
                    ->getStateUsing(fn(PembimbingAkademik $record) => $record->deleted_at !== null)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user()))
                    ->searchable()
                    ->query(function (Builder $query, array $data) {
                        if (! $data['value']) {
                            return $query;
                        }

                        return $query->where(fn(Builder $q) => $q
                            ->whereHas('kelas', fn(Builder $k) => $k->where('prodi_id', $data['value']))
                            ->orWhereHas('mahasiswa', fn(Builder $m) => $m->where('prodi_id', $data['value'])));
                    }),
                SelectFilter::make('jenis')
                    ->options(PembimbingAkademikJenis::options()),
                SelectFilter::make('status')
                    ->options(PembimbingAkademikStatus::options()),
                TrashedFilter::make(),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn() => Excel::download(
                        new PembimbingAkademikExport(PembimbingAkademik::query()->withTrashed()),
                        'riwayat-pembimbing-' . now()->format('Ymd-His') . '.xlsx'
                    )),
            ])
            ->emptyStateHeading('Belum ada riwayat penugasan')
            ->emptyStateDescription('Riwayat akan muncul di sini setelah ada penugasan pembimbing yang dibuat.')
            ->emptyStateIcon('heroicon-o-clock')
            ->recordActions([
                Action::make('cetakSk')
                    ->label('Cetak SK')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->visible(fn(PembimbingAkademik $record) => ! $record->trashed())
                    ->url(
                        fn(PembimbingAkademik $record): string =>
                        route('pembimbing-akademik.sk', $record)
                    )
                    ->openUrlInNewTab(),
                Action::make('pulihkan')
                    ->label('Pulihkan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->visible(fn(PembimbingAkademik $record) => $record->trashed())
                    ->requiresConfirmation()
                    ->modalDescription('Data riwayat ini akan dimunculkan kembali di daftar aktif/histori.')
                    ->action(function (PembimbingAkademik $record): void {
                        $record->restore();

                        Notification::make()
                            ->title('Data berhasil dipulihkan')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
