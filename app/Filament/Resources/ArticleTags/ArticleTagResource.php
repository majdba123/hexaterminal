<?php

namespace App\Filament\Resources\ArticleTags;

use App\Filament\Resources\ArticleTags\Pages\CreateArticleTag;
use App\Filament\Resources\ArticleTags\Pages\EditArticleTag;
use App\Filament\Resources\ArticleTags\Pages\ListArticleTags;
use App\Filament\Resources\ArticleTags\Schemas\ArticleTagForm;
use App\Filament\Resources\ArticleTags\Tables\ArticleTagsTable;
use App\Models\ArticleTag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;

class ArticleTagResource extends Resource
{
    use Translatable;

    protected static ?string $model = ArticleTag::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ArticleTagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArticleTagsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticleTags::route('/'),
            'create' => CreateArticleTag::route('/create'),
            'edit' => EditArticleTag::route('/{record}/edit'),
        ];
    }
}
