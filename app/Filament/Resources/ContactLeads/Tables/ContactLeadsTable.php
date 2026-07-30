<?php

namespace App\Filament\Resources\ContactLeads\Tables;

use App\Models\ContactLead;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactLeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('intent')->badge()->sortable(),
                TextColumn::make('company')->searchable()->toggleable(),
                TextColumn::make('score')->sortable()->label('Score'),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'won' => 'success',
                        'qualified', 'proposal', 'discovery_scheduled' => 'info',
                        'spam', 'lost' => 'danger',
                        'archived' => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('priority')->badge(),
                TextColumn::make('assignee.name')->label('Assignee')->toggleable(),
                TextColumn::make('follow_up_at')->dateTime()->sortable()->toggleable(),
                TextColumn::make('utm.campaign')->label('Campaign')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(array_combine(ContactLead::STATUSES, ContactLead::STATUSES)),
                SelectFilter::make('intent')
                    ->options(array_combine(ContactLead::INTENTS, ContactLead::INTENTS)),
                SelectFilter::make('priority')
                    ->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High']),
                SelectFilter::make('assigned_to')
                    ->relationship('assignee', 'name')
                    ->label('Assignee'),
                Filter::make('overdue_follow_up')
                    ->label('Overdue follow-up')
                    ->query(function (Builder $query) {
                        /** @var Builder<ContactLead> $query */
                        return $query->overdueFollowUp();
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_spam')
                        ->label('Mark as spam')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->authorize(fn (): bool => auth()->user()?->hasRole('admin') ?? false)
                        ->action(fn (Collection $records) => $records->each->update(['status' => ContactLead::STATUS_SPAM]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('archive')
                        ->label('Archive')
                        ->icon('heroicon-o-archive-box')
                        ->requiresConfirmation()
                        ->authorize(fn (): bool => auth()->user()?->hasRole('admin') ?? false)
                        ->action(fn (Collection $records) => $records->each->update(['status' => ContactLead::STATUS_ARCHIVED]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('export_csv')
                        ->label('Export CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records): StreamedResponse {
                            /** @var Collection<int, ContactLead> $records */
                            return response()->streamDownload(function () use ($records) {
                                $out = fopen('php://output', 'w');
                                fputcsv($out, ['name', 'email', 'intent', 'company', 'country', 'status', 'priority', 'score', 'budget_range', 'timeline', 'source_page', 'utm_campaign', 'created_at']);
                                foreach ($records as $lead) {
                                    fputcsv($out, [
                                        $lead->name, $lead->email, $lead->intent, $lead->company,
                                        $lead->country, $lead->status, $lead->priority, $lead->score,
                                        $lead->budget_range, $lead->timeline, $lead->source_page,
                                        $lead->utm['campaign'] ?? null, $lead->created_at?->toDateTimeString(),
                                    ]);
                                }
                                fclose($out);
                            }, 'leads-'.now()->format('Ymd-His').'.csv');
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
