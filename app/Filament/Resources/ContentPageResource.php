<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentPageResource\Pages;
use App\Models\Category;
use App\Models\ContentPage;
use App\Models\City;
use Closure;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContentPageResource extends Resource
{
    protected static ?string $model = ContentPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')->required()->maxLength(180),
            TextInput::make('slug')->required()->maxLength(180)->unique(ignoreRecord: true)->rules([
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (Category::where('slug', $value)->exists() || City::where('slug', $value)->exists()) {
                        $fail('This slug is already used by another public page.');
                    }
                },
            ]),
            MarkdownEditor::make('body')->required()->columnSpanFull(),
            TextInput::make('meta_title')->maxLength(180),
            Textarea::make('meta_description')->maxLength(320)->columnSpanFull(),
            Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable(),
            TextColumn::make('slug')->searchable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->filters([
            SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
        ])->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContentPages::route('/'),
            'create' => Pages\CreateContentPage::route('/create'),
            'edit' => Pages\EditContentPage::route('/{record}/edit'),
        ];
    }
}
