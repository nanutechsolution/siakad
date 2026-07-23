<?php

namespace App\Filament\Clusters\Migration\Resources\MigrationHistories\Schemas;

use Filament\Schemas\Schema;

class MigrationHistoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
