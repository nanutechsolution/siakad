<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Enums\NavigationGroup;
use App\Models\KeuanganSaldo;
use App\Models\KeuanganSaldoTransaction;
use App\Models\Mahasiswa;
use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class SaldoMahasiswaPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    protected string $view = 'filament.mahasiswa.pages.saldo-mahasiswa-page';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEUANGAN->value;
    protected static ?string $navigationLabel = 'Saldo Deposit';
    protected static ?string $title = 'Saldo Deposit Mahasiswa';
    public function getSaldoAttribute(): float
    {
        $mahasiswa = Mahasiswa::where('person_id', Auth::user()->person_id)->first();
        if (!$mahasiswa) return 0;

        $saldo = KeuanganSaldo::where('mahasiswa_id', $mahasiswa->id)->first();
        return $saldo ? $saldo->getSaldoAkhirAttribute() : 0;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                KeuanganSaldoTransaction::query()
                    ->whereHas('saldo', function ($query) {
                        $mahasiswa = Mahasiswa::where('person_id', Auth::user()->person_id)->first();
                        $query->where('mahasiswa_id', $mahasiswa?->id ?? 0);
                    })
                    ->latest()
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i'),
                TextColumn::make('keterangan')
                    ->label('Keterangan'),
                TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state) => $state === 'IN' ? 'success' : 'danger'),
                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR')
                    ->alignment('right'),
            ]);
    }
    public function getTableQuery(): Builder
    {
        $mahasiswaId = Mahasiswa::where('person_id', Auth::user()->person_id)->first()->id;
        return KeuanganSaldoTransaction::query()
            ->whereHas('saldo', fn($q) => $q->where('mahasiswa_id', $mahasiswaId));
    }
}
