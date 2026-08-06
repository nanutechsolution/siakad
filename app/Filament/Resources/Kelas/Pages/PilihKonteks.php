<?php

namespace App\Filament\Resources\Kelas\Pages;

use App\Domain\Authorization\Services\FormResolver;
use App\Filament\Resources\Kelas\KelasResource;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class PilihKonteks extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = KelasResource::class;

    protected static ?string $title = 'Pilih Konteks';

    protected string $view = 'filament.resources.kelas.pages.pilih-konteks';
    public ?array $data = [];

    public function mount(): void
    {
        // Prefill dari query string kalau operator kembali ke sini lewat
        // tombol "Ubah Konteks" di ListKelas, supaya tidak perlu isi ulang dari nol.
        $this->form->fill([
            'tahun_akademik_id' => request()->integer('tahun_akademik_id') ?: null,
            'prodi_id' => request()->integer('prodi_id') ?: null,
            'program_id' => request()->integer('program_id') ?: null,
            'angkatan_id' => request()->integer('angkatan_id') ?: null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Sesuaikan nama tabel/kolom referensi ini dengan skema Anda.
                Select::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->options(fn() => DB::table('ref_tahun_akademik')->pluck('nama', 'id'))
                    ->required(),

                Select::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user()))
                    ->required(),

                Select::make('program_id')
                    ->label('Program')
                    ->options(fn() => DB::table('ref_program')->pluck('nama_program', 'id'))
                    ->required(),

                Select::make('angkatan_id')
                    ->label('Angkatan')
                    ->options(fn() => DB::table('ref_angkatan')->pluck('id_tahun', 'id_tahun'))
                    ->required(),
            ])
            ->statePath('data');
    }

    public function terapkan(): void
    {
        $data = $this->form->getState();

        $this->redirect(KelasResource::getUrl('list', array_filter($data)));
    }
}
