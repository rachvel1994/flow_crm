<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Exports\UserExport;
use App\Models\Task;
use App\Models\TaskStatus;
use Filament\Facades\Filament;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Mail;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;


class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('addTask')
                ->label('დავალების დამატება')
                ->icon('heroicon-o-plus')
                ->modalHeading('დავალების დამატება კონტაქტზე')
                ->form([
                    TextInput::make('title')
                        ->label('დასახელება')
                        ->required(),

                    Select::make('status_id')
                        ->label('კანბანის სვეტი (ეტაპი)')
                        ->options(TaskStatus::pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    Select::make('assignees')
                        ->label('დავალებული პირები')
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->multiple()
                        ->required(),
                ])
                ->action(function (array $data, $record, $livewire) {
                    // დავალების შექმნა
                    $task = Task::create([
                        'title' => $data['title'],
                        'status_id' => $data['status_id'],
                        'created_by_id' => auth()->id(),
                    ]);

                    // დავალებულების დამაგრება
                    $task->assignees()->attach($data['assignees']);
                    // edit modal-ის გახსნა ახალი დავალებისთვის
                    session()->flash('notifySound');

                    $this->redirectRoute('filament.backend.pages.task-board', ['tenant' => Filament::getTenant()]);
                }),
			Action::make('export')
				->label('კონტაქტის ექსპორტი')
				->icon('heroicon-o-arrow-down-tray')
				->action(function () {
					return Excel::download(new UserExport(), 'contacts_export_' . now()->format('Ymd_His') . '.xlsx');
				}),
            Action::make('sendSms')
                ->label('SMS გაგზავნა')
                ->icon('heroicon-o-chat-bubble-left')
                ->form([
                    Select::make('sender')
                        ->label('გამომგზავნი')
                        ->options([
                            'MYDEPO' => 'MYDEPO',
                            'MELAGRO' => 'MELAGRO',
                            'TECSERVICE' => 'TECSERVICE',
                        ])
                        ->required(),

                    Textarea::make('message')
                        ->label('ტექსტი')
                        ->required()
                        ->rows(4),

                    Select::make('recipients')
                        ->label('კონკრეტული კონტაქტები')
                        ->multiple()
                        ->preload()
                        ->options(function () {
                            return User::with('phones')
                                ->get()
                                ->filter(fn($user) => filled($user->phones))
                                ->mapWithKeys(fn($user) => [
                                    $user->id => collect($user->phones)
                                        ->pluck('phone')
                                        ->filter()
                                        ->join(', ') ?: "{$user->name} {$user->surname}",
                                ]);
                        })
                        ->searchable(),

                    Select::make('roles')
                        ->label('როლით არჩევა')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->relationship('roles', 'name'),
                ])
                ->action(function (array $data) {
                    $manualUsers = User::with('phones')
                        ->when(!empty($data['recipients']), fn($q) => $q->whereIn('id', $data['recipients']))
                        ->get();

                    $roleUsers = collect();
                    if (!empty($data['roles'])) {
                        $roleUsers = User::with('phones')
                            ->whereHas('roles', fn($q) => $q->whereIn('name', $data['roles']))
                            ->get();
                    }

                    $allPhones = collect();
                    collect([$manualUsers, $roleUsers])
                        ->flatten()
                        ->each(function ($user) use (&$allPhones) {
                            foreach ($user->phones as $phone) {
                                $allPhones->put($phone->phone, [
                                    'number' => $phone->phone,
                                    'name' => $user->name,
                                ]);
                            }
                        });

                    foreach ($allPhones as $entry) {
                        // send_sms(
                        //     number: $entry['number'],
                        //     message: $data['message'],
                        //     sender: $data['sender'],
                        // );
                    }

                    Notification::make()
                        ->title('SMS წარმატებით გაიგზავნა ' . $allPhones->count() . ' ნომერზე')
                        ->success()
                        ->send();
                })
                ->modalHeading('SMS გაგზავნა')
                ->modalSubmitActionLabel('გაგზავნა'),

            Action::make('sendMail')
                ->label('ელ. ფოსტით დაგზავნა')
                ->icon('heroicon-m-envelope')
                ->form([
                    TextInput::make('title')
                        ->label('სათაური')
                        ->required(),

                    TinyEditor::make('message')
                        ->label('ტექსტი')
                        ->required(),

                    Select::make('contacts')
                        ->label('კონტაქტებით არჩევა')
                        ->multiple()
                        ->searchable()
                        ->options(User::pluck('email', 'id')->toArray()),

                    Select::make('roles')
                        ->label('როლით არჩევა')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->relationship('roles', 'name'),
                ])
                ->action(function (array $data) {
                    $contactEmails = User::query()
                        ->when(!empty($data['contacts']), fn($q) => $q->whereIn('id', $data['contacts']))
                        ->pluck('email')
                        ->toArray();

                    $roleEmails = [];
                    if (!empty($data['roles'])) {
                        $roleEmails = User::whereHas('roles', fn($q) =>
                        $q->whereIn('name', $data['roles'])
                        )->pluck('email')->toArray();
                    }

                    $recipients = collect([...$contactEmails, ...$roleEmails])
                        ->filter()
                        ->unique()
                        ->values();

                    foreach ($recipients as $email) {
                        Mail::to($email)->send(new \App\Mail\MassMessageMail(
                            title: $data['title'],
                            msg: $data['message']
                        ));
                    }

                    Notification::make()
                        ->title('ელ. ფოსტები გაიგზავნა')
                        ->success()
                        ->send();
                }),
        ];

    }

   public function getTabs(): array
    {
        $tabs = [];

        // Default tab - All users
        $tabs['ყველა'] = Tab::make('ყველა')
            ->badge(User::count());

        // Load all roles
        $roles = \Spatie\Permission\Models\Role::all();

        foreach ($roles as $role) {
            $tabs[$role->name] = Tab::make($role->name)
                ->badge(User::role($role->name)->count()) // Count users with that role
                ->modifyQueryUsing(function ($query) use ($role) {
                    $query->role($role->name); // Scope from Spatie package
                });
        }

        return $tabs;
    }
}
