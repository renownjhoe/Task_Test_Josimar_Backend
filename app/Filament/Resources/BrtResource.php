<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrtResource\Pages;
use App\Models\Brt;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class BrtResource extends Resource
{
    protected static ?string $model = Brt::class;
    protected static ?string $navigationIcon = 'heroicon-o-document';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Hidden::make('user_id')
                ->default(fn () => Auth::id()),
            Forms\Components\TextInput::make('brt_code')
                ->label('BRT Code')
                ->required(),
            Forms\Components\TextInput::make('reserved_amount')
                ->label('Reserved Amount')
                ->numeric()
                ->required(),
            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'active'  => 'Active',
                    'expired' => 'Expired',
                ])
                ->required(),
        ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('brt_code')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('reserved_amount'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrts::route('/'),
            'create' => Pages\CreateBrt::route('/create'),
            'edit' => Pages\EditBrt::route('/{record}/edit'),
        ];
    }
}
