<?php

namespace App\Filament\Resources\PublicNotices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PublicNoticeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('নোটিশের তথ্য')
                    ->description('নোটিশের শিরোনাম ও বিস্তারিত বিবরণ লিখুন।')
                    ->schema([

                        TextInput::make('title')
                            ->label('নোটিশের শিরোনাম')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('notice_type')
                            ->label('নোটিশের ধরন')
                            ->options([
                                'general' => 'সাধারণ নোটিশ',
                                'application' => 'আবেদন সংক্রান্ত',
                                'allocation' => 'প্লট বরাদ্দ সংক্রান্ত',
                                'payment' => 'পেমেন্ট সংক্রান্ত',
                                'urgent' => 'জরুরি বিজ্ঞপ্তি',
                            ])
                            ->default('general')
                            ->required(),

                        RichEditor::make('body')
                            ->label('নোটিশের বিস্তারিত বিবরণ')
                            ->required()
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

                Section::make('প্রকাশনা ও সংযুক্তি')
                    ->description('নোটিশ প্রকাশের তারিখ, মেয়াদ ও PDF ফাইল নির্ধারণ করুন।')
                    ->schema([

                        DatePicker::make('published_at')
                            ->label('প্রকাশের তারিখ')
                            ->default(now('Asia/Dhaka'))
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('expires_at')
                            ->label('মেয়াদ শেষ হওয়ার তারিখ')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->afterOrEqual('published_at')
                            ->helperText('খালি রাখলে নোটিশের মেয়াদ শেষ হবে না।'),

                        FileUpload::make('attachment')
                            ->label('PDF সংযুক্তি')
                            ->disk('public')
                            ->directory('notices')
                            ->acceptedFileTypes([
                                'application/pdf',
                            ])
                            ->maxSize(10240)
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull()
                            ->helperText('শুধুমাত্র PDF ফাইল। সর্বোচ্চ ১০ MB।'),

                    ])
                    ->columns(2),

                Section::make('নোটিশ প্রকাশের সেটিংস')
                    ->schema([

                        Toggle::make('is_important')
                            ->label('গুরুত্বপূর্ণ নোটিশ')
                            ->default(false)
                            ->helperText('চালু করলে নোটিশে গুরুত্বপূর্ণ ব্যাজ দেখাবে।'),

                        Toggle::make('is_published')
                            ->label('নোটিশ প্রকাশ করুন')
                            ->default(false)
                            ->helperText('চালু করলে নির্ধারিত তারিখ অনুযায়ী Homepage-এ দেখাবে।'),

                    ])
                    ->columns(2),
            ]);
    }
}
