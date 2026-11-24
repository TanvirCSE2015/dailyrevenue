<?php

namespace App\Filament\Resources\Applicants\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlotAllocationsRelationManager extends RelationManager
{
    protected static string $relationship = 'allocations';


    protected static ?string $title = 'বরাদ্দের ইতিহাস';

    protected static ?string $recordTitleAttribute = 'allocated_at';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with([
                'plot',
                'allocator',
            ]))

            ->columns([

                TextColumn::make('plot.plot_number')
                    ->label('প্লট নম্বর')
                    ->sortable()
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('allocated_at')
                    ->label('বরাদ্দের তারিখ')
                    ->dateTime('d/m/Y h:i A')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('start_date')
                    ->label('ভাড়া শুরুর তারিখ')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('end_date')
                    ->label('ভাড়া শেষের তারিখ')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('চলমান'),

                TextColumn::make('status')
                    ->label('বরাদ্দের অবস্থা')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'active' => 'সক্রিয়',
                        'surrendered' => 'সমর্পিত',
                        'cancelled' => 'বাতিল',
                        'expired' => 'মেয়াদ শেষ',
                        default => $state ?? '—',
                    })
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'active' => 'success',
                        'surrendered' => 'info',
                        'cancelled' => 'danger',
                        'expired' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('allocator.name')
                    ->label('বরাদ্দ প্রদানকারী')
                    ->placeholder('—'),

                // TextColumn::make('remarks')
                //     ->label('মন্তব্য')
                //     ->placeholder('—')
                //     ->wrap(),

            ])

            ->defaultSort('allocated_at', 'desc')

            ->striped()

            ->emptyStateHeading('কোনো প্লট বরাদ্দের ইতিহাস নেই')

            ->emptyStateDescription(
                'এই আবেদনকারীর কোনো প্লট বরাদ্দের তথ্য পাওয়া যায়নি।'
            )

            ->paginated([10, 25, 50]);
    }

    // public function form(Schema $schema): Schema
    // {
    //     return $schema
    //         ->components([
    //             TextInput::make('allocated_at')
    //                 ->required()
    //                 ->maxLength(255),
    //         ]);
    // }

    // public function table(Table $table): Table
    // {
    //     return $table
    //         ->recordTitleAttribute('allocated_at')
    //         ->columns([
    //             TextColumn::make('allocated_at')
    //                 ->searchable(),
    //         ])
    //         ->filters([
    //             TrashedFilter::make(),
    //         ])
    //         ->headerActions([
    //             CreateAction::make(),
    //             AssociateAction::make(),
    //         ])
    //         ->recordActions([
    //             EditAction::make(),
    //             DissociateAction::make(),
    //             DeleteAction::make(),
    //             ForceDeleteAction::make(),
    //             RestoreAction::make(),
    //         ])
    //         ->toolbarActions([
    //             BulkActionGroup::make([
    //                 DissociateBulkAction::make(),
    //                 DeleteBulkAction::make(),
    //                 ForceDeleteBulkAction::make(),
    //                 RestoreBulkAction::make(),
    //             ]),
    //         ])
    //         ->modifyQueryUsing(fn (Builder $query) => $query
    //             ->withoutGlobalScopes([
    //                 SoftDeletingScope::class,
    //             ]));
    // }
}
