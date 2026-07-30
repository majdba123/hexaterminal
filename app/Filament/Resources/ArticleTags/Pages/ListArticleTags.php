<?php

namespace App\Filament\Resources\ArticleTags\Pages;

use App\Filament\Resources\ArticleTags\ArticleTagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArticleTags extends ListRecords
{
    protected static string $resource = ArticleTagResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
