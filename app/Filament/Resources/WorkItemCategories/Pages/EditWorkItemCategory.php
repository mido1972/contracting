<?php

namespace App\Filament\Resources\WorkItemCategories\Pages;

use App\Filament\Resources\WorkItemCategories\WorkItemCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkItemCategory extends EditRecord
{
    protected static string $resource = WorkItemCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
