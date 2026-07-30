<?php

namespace App\Filament\Resources\ArticleTags\Pages;

use App\Filament\Resources\ArticleTags\ArticleTagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditArticleTag extends EditRecord
{
    protected static string $resource = ArticleTagResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
