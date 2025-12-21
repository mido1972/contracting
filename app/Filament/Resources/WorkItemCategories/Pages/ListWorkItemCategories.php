<?php

namespace App\Filament\Resources\WorkItemCategories\Pages;

use App\Filament\Resources\WorkItemCategories\WorkItemCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkItemCategories extends ListRecords
{
    protected static string $resource = WorkItemCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
