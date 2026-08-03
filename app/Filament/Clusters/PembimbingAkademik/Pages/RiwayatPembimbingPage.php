<?php

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikStatus;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Models\PembimbingAkademik;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

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
            ->query(PembimbingAkademik::query()->withTrashed())
            ->columns([
                TextColumn::make('jenis')
                    ->badge()
                    ->formatStateUsing(fn(PembimbingAkademikJenis $state) => $state->label()),
                TextColumn::make('mahasiswa.nim')
                    ->label('Mahasiswa')
                    ->placeholder('-')
                    ->description(fn(?PembimbingAkademik $record) => $record?->mahasiswa?->person?->nama_lengkap)
                    ->searchable(),
                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('dosen.person.nama_lengkap')
                    ->label('Dosen')
                    ->description(fn(?PembimbingAkademik $record) => $record?->dosen?->nidn)
                    ->searchable(),
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
                IconColumn::make('is_deleted')
                    ->label('Dihapus')
                    ->boolean()
                    ->getStateUsing(fn(PembimbingAkademik $record) => $record->deleted_at !== null)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('jenis')
                    ->options(PembimbingAkademikJenis::options()),
                SelectFilter::make('status')
                    ->options(PembimbingAkademikStatus::options()),
                TrashedFilter::make(),
            ])
            ->defaultSort('tanggal_mulai', 'desc');
    }
}
