<?php

namespace App\Filament\Resources\LpmAmiFindings\Schemas;

use App\Models\RefProdi;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LpmAmiFindingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Temuan')
                    ->columns(2)
                    ->schema([
                        Select::make('periode_id')
                            ->label('Periode Audit')
                            ->relationship('periode', 'nama_periode')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('program_id')
                            ->label('Program Audit (opsional)')
                            ->relationship('program', 'id')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->periode?->nama_periode} - {$record->unitKerja?->nama_unit}")
                            ->searchable()
                            ->preload(),
                        Select::make('prodi_id')
                            ->label('Program Studi Terdampak')
                            ->options(fn() => RefProdi::query()->pluck('nama_prodi', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('standar_id')
                            ->label('Standar')
                            ->relationship('standar', 'nama_standar')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('auditor_name')
                            ->label('Nama Auditor')
                            ->required()
                            ->maxLength(255),
                        Select::make('auditor_id')
                            ->label('Auditor (Relasional, opsional)')
                            ->relationship('auditor', 'no_sertifikat_auditor')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->person?->nama_lengkap ?? $record->no_sertifikat_auditor)
                            ->searchable()
                            ->preload(),
                        Select::make('klasifikasi')
                            ->label('Klasifikasi')
                            ->options([
                                'OB' => 'Observasi',
                                'KTS_MINOR' => 'KTS Minor',
                                'KTS_MAYOR' => 'KTS Mayor',
                            ])
                            ->required(),
                        Select::make('status_workflow')
                            ->label('Status Workflow')
                            ->options([
                                'OPEN' => 'Open',
                                'ACTION_PLAN' => 'Action Plan',
                                'VERIFICATION' => 'Verification',
                                'CLOSED' => 'Closed',
                            ])
                            ->default('OPEN')
                            ->required(),
                        DatePicker::make('deadline_perbaikan')
                            ->label('Deadline Perbaikan'),
                        Toggle::make('is_closed')
                            ->label('Sudah Ditutup'),
                    ]),
                Section::make('Detail & Tindak Lanjut')
                    ->schema([
                        Textarea::make('deskripsi_temuan')
                            ->label('Deskripsi Temuan')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('rekomendasi')
                            ->label('Rekomendasi')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('akar_masalah')
                            ->label('Akar Masalah (Root Cause Analysis)')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('rencana_tindak_lanjut')
                            ->label('Corrective Action (Rencana Tindak Lanjut)')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('preventive_action')
                            ->label('Preventive Action')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
