<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBook extends CreateRecord
{
    use HandlesBookPdf;

    protected static string $resource = BookResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->extractPdfFields($data);
    }

    protected function afterCreate(): void
    {
        $this->syncPdfFile($this->record);
    }
}
