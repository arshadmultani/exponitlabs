<?php

namespace App\Filament\Resources\Microsites\RelationManagers;

use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $title = 'Reviews';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reviewer_name')
                    ->label('Patient name')
                    ->required()
                    ->minLength(3)
                    ->maxLength(50)
                    ->placeholder('John Doe'),

                Select::make('rating')
                    ->label('Rating')
                    ->options([
                        1 => '1 ★',
                        2 => '2 ★★',
                        3 => '3 ★★★',
                        4 => '4 ★★★★',
                        5 => '5 ★★★★★',
                    ])
                    ->native(false),

                Textarea::make('review_text')
                    ->label('Review text (optional)')
                    ->minLength(10)
                    ->maxLength(500)
                    ->placeholder("Patient's review goes here..."),

                FileUpload::make('media_url')
                    ->label('Photo / video (optional)')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('microsite/reviews')
                    ->maxFiles(1)
                    ->maxSize(Review::MAX_VIDEO_KB)
                    ->acceptedFileTypes([
                        'image/jpeg', 'image/png', 'image/gif',
                        'video/mp4', 'video/quicktime', 'video/x-msvideo',
                    ])
                    ->helperText('JPG, PNG, MP4, MOV. Images up to '.(Review::MAX_IMAGE_KB / 1024).'MB, videos up to '.(Review::MAX_VIDEO_KB / 1024).'MB.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->recordTitleAttribute('reviewer_name')
            ->description('Patient reviews. Approve a review to make it appear on the public website.')
            ->emptyStateIcon('heroicon-o-chat-bubble-bottom-center-text')
            ->emptyStateDescription('No reviews yet. Add one to get started.')
            ->columns([
                TextColumn::make('reviewer_name')
                    ->label('Patient')
                    ->limit(20),

                TextColumn::make('rating')
                    ->formatStateUsing(fn ($state) => $state ? str_repeat('★', (int) $state) : '—'),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                IconColumn::make('verified_at')
                    ->label('Live')
                    ->state(fn (Review $record) => (bool) $record->verified_at)
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('New review')
                    ->icon(Heroicon::OutlinedPlus)
                    ->outlined()
                    ->createAnother(false)
                    ->modalDescription('Add review text or upload a photo/video — at least one is required.')
                    ->before(function (CreateAction $action, array $data) {
                        $doctor = $this->getOwnerRecord()->doctor;

                        if ($doctor->reviews()->count() >= Review::MAX_PER_DOCTOR) {
                            Notification::make()
                                ->danger()
                                ->title('You can only add up to '.Review::MAX_PER_DOCTOR.' reviews.')
                                ->send();
                            $action->cancel();
                        }

                        if (empty($data['review_text']) && empty($data['media_url'])) {
                            Notification::make()
                                ->danger()
                                ->title('Add review text or upload a media file.')
                                ->send();
                            $action->halt();
                        }
                    })
                    ->using(function (array $data): Review {
                        $data['doctor_id'] = $this->getOwnerRecord()->doctor_id;
                        $data['submitted_by_name'] = auth()->user()?->name;
                        $data['status'] = 'pending';

                        if (! empty($data['media_url'])) {
                            $mime = Storage::disk('public')->mimeType($data['media_url']);
                            $data['media_type'] = Str::startsWith((string) $mime, 'video') ? 'video' : 'image';
                        }

                        return Review::create($data);
                    }),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (Review $record) => $record->status !== 'approved')
                    ->action(fn (Review $record) => $record->update([
                        'status' => 'approved',
                        'verified_at' => now(),
                    ])),

                Action::make('reject')
                    ->label('Reject')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (Review $record) => $record->status !== 'rejected')
                    ->requiresConfirmation()
                    ->action(fn (Review $record) => $record->update([
                        'status' => 'rejected',
                        'verified_at' => null,
                    ])),

                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
