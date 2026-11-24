<?php

namespace App\Filament\Resources\PlotApplications\Pages;

use App\Filament\Resources\PlotApplications\PlotApplicationResource;
use App\Models\Plot;
use App\Models\PlotAllocation;
use App\Models\PlotApplication;
use App\Services\PlotApplicationReviewService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EditPlotApplication extends EditRecord
{
    protected static string $resource = PlotApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {

            $application = PlotApplication::query()
                ->whereKey($record->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $requestedPlotId = $data['plot_id'] ?? null;

            unset($data['plot_id']);

            // Allocation তৈরি হয়ে থাকলে Plot পরিবর্তন নিষিদ্ধ
            if ($application->allocation()->exists()) {

                if (
                    filled($requestedPlotId)
                    && (int) $requestedPlotId !== (int) $application->plot_id
                ) {
                    throw ValidationException::withMessages([
                        'plot_id' => 'এই আবেদনের বিপরীতে ইতোমধ্যে প্লট বরাদ্দ হয়েছে। তাই প্লট পরিবর্তন করা যাবে না।',
                    ]);
                }

            } elseif (
                filled($requestedPlotId)
                && (int) $requestedPlotId !== (int) $application->plot_id
            ) {

                $plot = Plot::query()
                    ->whereKey($requestedPlotId)
                    ->lockForUpdate()
                    ->first();

                if (! $plot) {
                    throw ValidationException::withMessages([
                        'plot_id' => 'নির্বাচিত প্লটটি পাওয়া যায়নি।',
                    ]);
                }

                if ($plot->status !== 'available') {
                    throw ValidationException::withMessages([
                        'plot_id' => 'নির্বাচিত প্লটটি আর খালি নেই। অন্য একটি প্লট নির্বাচন করুন।',
                    ]);
                }

                $hasActiveAllocation = PlotAllocation::query()
                    ->where('plot_id', $plot->id)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->exists();

                if ($hasActiveAllocation) {
                    throw ValidationException::withMessages([
                        'plot_id' => 'এই প্লটটি ইতোমধ্যে অন্য একজন আবেদনকারীকে বরাদ্দ করা হয়েছে।',
                    ]);
                }

                $data['plot_id'] = $plot->id;
            }

            $application->fill($data);
            $application->save();

            return $application;

        }, 5);
    }


    /**
     * Actions displayed below the application details.
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('পরিবর্তন সংরক্ষণ করুন')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->size('lg')
                ->action(fn () => $this->save()),
            Action::make('underReview')
                ->label('যাচাই শুরু করুন')
                ->icon('heroicon-o-magnifying-glass')
                ->color('info')
                ->size('lg')
                ->requiresConfirmation()
                ->modalHeading('আবেদন যাচাই শুরু')
                ->modalDescription(
                    'আপনি কি এই আবেদনটির যাচাই শুরু করতে চান?'
                )
                ->visible(fn (): bool => in_array(
                    $this->getRecord()->status,
                    ['submitted', 'returned'],
                    true
                ))
                ->action(function (): void {

                    app(PlotApplicationReviewService::class)
                        ->markUnderReview($this->getRecord()->id);

                    Notification::make()
                        ->title('আবেদনের যাচাই শুরু হয়েছে')
                        ->success()
                        ->send();

                    $this->redirect(
                        PlotApplicationResource::getUrl('edit', [
                            'record' => $this->getRecord(),
                        ])
                    );
                }),
            Action::make('approve')
                ->label('আবেদন অনুমোদন করুন')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->size('lg')
                ->requiresConfirmation()
                ->modalHeading('আবেদন অনুমোদন')
                ->modalDescription(
                    'আপনি কি নিশ্চিত যে এই আবেদনটি অনুমোদন করতে চান?'
                )
                ->visible(fn (): bool => $this->canReview())
                ->action(function (): void {
                    app(PlotApplicationReviewService::class)
                        ->approve($this->getRecord()->id);

                    $this->getRecord()->refresh();

                    Notification::make()
                        ->title('আবেদন সফলভাবে অনুমোদিত হয়েছে')
                        ->body('আবেদনকারীর Active Status হালনাগাদ করা হয়েছে।')
                        ->success()
                        ->send();
                }),

            Action::make('return')
                ->label('সংশোধনের জন্য ফেরত')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->size('lg')
                ->visible(fn (): bool => $this->canReview())
                ->form([
                    Textarea::make('remarks')
                        ->label('সংশোধনের নির্দেশনা')
                        ->required()
                        ->rows(4)
                        ->maxLength(5000),
                ])
                ->modalHeading('আবেদন সংশোধনের জন্য ফেরত')
                ->modalSubmitActionLabel('ফেরত পাঠান')
                ->action(function (array $data): void {
                    app(PlotApplicationReviewService::class)
                        ->returnForCorrection(
                            $this->getRecord()->id,
                            $data['remarks']
                        );

                    $this->getRecord()->refresh();

                    Notification::make()
                        ->title('আবেদন সংশোধনের জন্য ফেরত পাঠানো হয়েছে')
                        ->success()
                        ->send();
                }),

            Action::make('reject')
                ->label('আবেদন বাতিল করুন')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->size('lg')
                ->visible(fn (): bool => $this->canReview())
                ->form([
                    Textarea::make('remarks')
                        ->label('আবেদন বাতিলের কারণ')
                        ->required()
                        ->rows(4)
                        ->maxLength(5000),
                ])
                ->modalHeading('আবেদন বাতিল')
                ->modalSubmitActionLabel('বাতিল নিশ্চিত করুন')
                ->action(function (array $data): void {
                    app(PlotApplicationReviewService::class)
                        ->reject(
                            $this->getRecord()->id,
                            $data['remarks']
                        );

                    $this->getRecord()->refresh();

                    Notification::make()
                        ->title('আবেদন বাতিল করা হয়েছে')
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * Determine whether the application can be reviewed.
     */
    protected function canReview(): bool
    {
        return in_array($this->getRecord()->status, [
            'submitted',
            'under_review',
            'returned',
        ], true);
    }
}
