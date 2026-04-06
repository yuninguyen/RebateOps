<?php

namespace App\Filament\Resources\Shared;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Filament\Support\Enums\FontWeight;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';
    
    protected static ?string $recordTitleAttribute = 'description';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('system.nav.logs');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->label(__('system.labels.description'))
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('properties')
                    ->label(__('system.labels.detail'))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('system.labels.date'))
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->description(fn ($record) => $record->created_at->diffForHumans()),
                
                Tables\Columns\TextColumn::make('causer.name')
                    ->label(__('system.labels.user'))
                    ->default('System')
                    ->weight(FontWeight::Bold),
                
                Tables\Columns\TextColumn::make('description')
                    ->label(__('system.labels.description'))
                    ->wrap(),
                
                Tables\Columns\IconColumn::make('properties')
                    ->label(__('system.labels.detail'))
                    ->icon('heroicon-m-information-circle')
                    ->color('info')
                    ->action(
                        Tables\Actions\Action::make('view_properties')
                            ->modalHeading(__('system.labels.detail'))
                            ->modalContent(fn ($record) => view('filament.components.activity-log-details', ['properties' => $record->properties]))
                    ),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
