<?php

namespace App\Filament\Resources\LpmAmiPrograms\RelationManagers;

use App\Models\LpmAmiChecklistItem;
use App\Models\LpmAmiFinding;
use App\Models\RefProdi;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChecklistJawabansRelationManager extends RelationManager
{
    protected static string $relationship = 'checklistJawabans';
    protected static ?string $title = 'Pengisian Checklist Audit';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('checklist_item_id')
                    ->label('Pertanyaan Checklist')
                    ->options(fn() => LpmAmiChecklistItem::query()
                        ->with('checklist.standar')
                        ->get()
                        ->mapWithKeys(fn(LpmAmiChecklistItem $item) => [
                            $item->id => "[{$item->checklist->standar->kode_standar}] {$item->pertanyaan}",
                        ]))
                    ->searchable()
                    ->required(),
                Select::make('jawaban')
                    ->label('Jawaban')
                    ->options([
                        'SESUAI' => 'Sesuai',
                        'TIDAK_SESUAI' => 'Tidak Sesuai',
                        'OBSERVASI' => 'Observasi',
                    ])
                    ->required(),
                Textarea::make('catatan')
                    ->label('Catatan Auditor')
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('checklistItem.checklist.standar.kode_standar')->label('Standar'),
                TextColumn::make('checklistItem.pertanyaan')->label('Pertanyaan')->wrap()->limit(60),
                TextColumn::make('jawaban')
                    ->label('Jawaban')
                    ->badge()
                    ->color(fn(?string $state) => match ($state) {
                        'SESUAI' => 'success',
                        'TIDAK_SESUAI' => 'danger',
                        'OBSERVASI' => 'warning',
                        default => 'gray',
                    }),
                IconColumn::make('finding_id')
                    ->label('Temuan Dibuat')
                    ->boolean()
                    ->getStateUsing(fn($record) => $record->finding_id !== null),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('buatTemuan')
                    ->label('Buat Temuan')
                    ->icon('heroicon-o-flag')
                    ->color('danger')
                    ->visible(fn($record) => $record->memicuTemuan() && $record->finding_id === null)
                    ->schema([
                        Select::make('prodi_id')
                            ->label('Program Studi Terdampak')
                            ->options(fn() => RefProdi::query()->pluck('nama_prodi', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('auditor_name')
                            ->label('Nama Auditor')
                            ->required(),
                        Select::make('klasifikasi')
                            ->label('Klasifikasi')
                            ->options(fn($livewire) => $livewire->getMountedTableActionRecord()?->jawaban === 'OBSERVASI'
                                ? ['OB' => 'Observasi']
                                : ['KTS_MINOR' => 'KTS Minor', 'KTS_MAYOR' => 'KTS Mayor'])
                            ->default(fn($livewire) => $livewire->getMountedTableActionRecord()?->jawaban === 'OBSERVASI' ? 'OB' : 'KTS_MINOR')
                            ->required(),
                        Textarea::make('deskripsi_temuan')
                            ->label('Deskripsi Temuan')
                            ->required()
                            ->rows(3),
                        Textarea::make('rekomendasi')
                            ->label('Rekomendasi')
                            ->rows(2),
                        Textarea::make('akar_masalah')
                            ->label('Akar Masalah (Root Cause Analysis)')
                            ->rows(2),
                        Textarea::make('rencana_tindak_lanjut')
                            ->label('Corrective Action (Rencana Tindak Lanjut)')
                            ->rows(2),
                        Textarea::make('preventive_action')
                            ->label('Preventive Action')
                            ->rows(2),
                        DatePicker::make('deadline_perbaikan')
                            ->label('Deadline Perbaikan'),
                    ])
                    ->action(function ($record, array $data): void {
                        $checklistItem = $record->checklistItem()->with('checklist')->first();
                        $program = $this->getOwnerRecord();

                        $finding = LpmAmiFinding::create([
                            'periode_id' => $program->periode_id,
                            'program_id' => $program->id,
                            'prodi_id' => $data['prodi_id'],
                            'standar_id' => $checklistItem->checklist->standar_id,
                            'jenis_temuan' => $record->jawaban === 'OBSERVASI' ? 'OBSERVASI' : 'KTS',
                            'auditor_name' => $data['auditor_name'],
                            'klasifikasi' => $data['klasifikasi'],
                            'deskripsi_temuan' => $data['deskripsi_temuan'],
                            'rekomendasi' => $data['rekomendasi'] ?? null,
                            'akar_masalah' => $data['akar_masalah'] ?? null,
                            'rencana_tindak_lanjut' => $data['rencana_tindak_lanjut'] ?? null,
                            'preventive_action' => $data['preventive_action'] ?? null,
                            'deadline_perbaikan' => $data['deadline_perbaikan'] ?? null,
                            'status_workflow' => 'OPEN',
                            'is_closed' => false,
                        ]);

                        $record->update(['finding_id' => $finding->id]);
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
