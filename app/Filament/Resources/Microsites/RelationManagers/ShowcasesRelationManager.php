<?php

namespace App\Filament\Resources\Microsites\RelationManagers;

use App\Models\Showcase;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShowcasesRelationManager extends RelationManager
{
    protected static string $relationship = 'showcases';

    protected static ?string $title = 'Showcases';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('Clinic tour, About doctor, ...'),

                Radio::make('showcase_type')
                    ->label('Type')
                    ->options([
                        'text' => 'Text',
                        'media' => 'Photo / video',
                    ])
                    ->required()
                    ->dehydrated(false)
                    ->live()
                    ->inline()
                    ->inlineLabel(false),

                RichEditor::make('description')
                    ->label('Description')
                    ->visible(fn ($get) => $get('showcase_type') === 'text')
                    ->required(fn ($get) => $get('showcase_type') === 'text')
                    ->disableToolbarButtons(['attachFiles', 'codeBlock', 'blockquote', 'link', 'strike']),

                FileUpload::make('media_url')
                    ->label('Upload media')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('microsite/showcases')
                    ->maxFiles(1)
                    ->visible(fn ($get) => $get('showcase_type') === 'media')
                    ->required(fn ($get) => $get('showcase_type') === 'media')
                    ->maxSize(Showcase::MAX_VIDEO_KB)
                    ->acceptedFileTypes([
                        'image/jpeg', 'image/png', 'image/gif',
                        'video/mp4', 'video/quicktime', 'video/x-msvideo',
                    ])
                    ->helperText('JPG, PNG, GIF, MP4, MOV. Images up to '.(Showcase::MAX_IMAGE_KB / 1024).'MB, videos up to '.(Showcase::MAX_VIDEO_KB / 1024).'MB.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->recordTitleAttribute('title')
            ->description('Doctor intro videos, clinic photos, or text sections shown on the public website.')
            ->emptyStateIcon('heroicon-o-photo')
            ->emptyStateDescription('No showcases yet. Add one to get started.')
            ->columns([
                TextColumn::make('title')
                    ->limit(20),

                TextColumn::make('media_type')
                    ->badge(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('New showcase')
                    ->icon(Heroicon::OutlinedPlus)
                    ->outlined()
                    ->createAnother(false)
                    ->before(function (CreateAction $action) {
                        $doctor = $this->getOwnerRecord()->doctor;

                        if ($doctor->showcases()->count() >= Showcase::MAX_PER_DOCTOR) {
                            Notification::make()
                                ->danger()
                                ->title('You can only add up to '.Showcase::MAX_PER_DOCTOR.' showcases.')
                                ->send();
                            $action->cancel();
                        }
                    })
                    ->using(function (array $data): Showcase {
                        $data['doctor_id'] = $this->getOwnerRecord()->doctor_id;

                        if (! empty($data['media_url'])) {
                            $mime = Storage::disk('public')->mimeType($data['media_url']);
                            $data['media_type'] = Str::startsWith((string) $mime, 'video') ? 'video' : 'image';
                        } elseif (! empty($data['description'])) {
                            $data['media_type'] = 'text';
                        }

                        return Showcase::create($data);
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
