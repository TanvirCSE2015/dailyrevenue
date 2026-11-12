<?php

namespace App\Filament\Resources\Plots\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('প্লটের সাধারণ তথ্য')
                    ->description('প্লটের পরিচিতি ও অবস্থান সংক্রান্ত তথ্য প্রদান করুন।')
                    ->schema([
                        TextInput::make('plot_number')
                            ->label('প্লট নম্বর')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('যেমন: P-001')
                            ->columnSpan(1),

                        TextInput::make('name')
                            ->label('প্লটের নাম')
                            ->maxLength(255)
                            ->placeholder('প্লটের নাম লিখুন')
                            ->columnSpan(1),

                        
                        TextInput::make('length')
                            ->label('দৈর্ঘ্য')
                            ->numeric()
                            ->minValue(0)
                            ->step('0.01')
                            ->suffix('ফুট'),

                        TextInput::make('width')
                            ->label('প্রস্থ')
                            ->numeric()
                            ->minValue(0)
                            ->step('0.01')
                            ->suffix('ফুট'),
                        TextInput::make('location')
                            ->label('প্লটের অবস্থান')
                            ->maxLength(255)
                            ->placeholder('প্লটের অবস্থান লিখুন'),
                        Select::make('status')
                            ->label('প্লটের বর্তমান অবস্থা')
                            ->options([
                                'available' => 'খালি',
                                'reserved' => 'সংরক্ষিত',
                                'allocated' => 'বরাদ্দকৃত',
                                'maintenance' => 'রক্ষণাবেক্ষণে',
                                'inactive' => 'নিষ্ক্রিয়',
                            ])
                            ->default('available')
                            ->required()
                            ->native(false),

                        // Textarea::make('description')
                        //     ->label('বিস্তারিত বিবরণ')
                        //     ->rows(2),
                    ])
                    ->columnSpanFull()
                    ->columns(5),

                // Section::make('প্লটের পরিমাপ')
                //     ->description('প্লটের দৈর্ঘ্য ও প্রস্থ প্রদান করুন।')
                //     ->schema([
                //         TextInput::make('length')
                //             ->label('দৈর্ঘ্য')
                //             ->numeric()
                //             ->minValue(0)
                //             ->step('0.01')
                //             ->suffix('ফুট'),

                //         TextInput::make('width')
                //             ->label('প্রস্থ')
                //             ->numeric()
                //             ->minValue(0)
                //             ->step('0.01')
                //             ->suffix('ফুট'),
                //     ])
                //     ->columns(2),

                // Section::make('প্লটের ব্যবস্থাপনা')
                //     ->schema([
                //         Select::make('status')
                //             ->label('প্লটের বর্তমান অবস্থা')
                //             ->options([
                //                 'available' => 'খালি',
                //                 'reserved' => 'সংরক্ষিত',
                //                 'allocated' => 'বরাদ্দকৃত',
                //                 'maintenance' => 'রক্ষণাবেক্ষণে',
                //                 'inactive' => 'নিষ্ক্রিয়',
                //             ])
                //             ->default('available')
                //             ->required()
                //             ->native(false),

                //         Textarea::make('description')
                //             ->label('বিস্তারিত বিবরণ')
                //             ->rows(2),
                //     ])
                //     ->columns(2)
                //     ->columnSpanFull(),
            ]);
    }
}
