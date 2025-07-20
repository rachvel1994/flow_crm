<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Mail\MassMessageMail;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Mail;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;
use Spatie\Permission\Models\Role;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
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
                            return User::all()
                                ->filter(fn($user) => filled($user->phones))
                                ->mapWithKeys(fn($user) => [
                                    $user->id => collect($user->phones)->pluck('phone')->join(', '),
                                ]);
                        })
                        ->searchable(),

                    Select::make('roles')
                        ->label('როლით არჩევა')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->relationship('roles', 'name')
                ])
                ->action(function (array $data) {
                    $manualUsers = User::with('phones')
                        ->when(!empty($data['recipients']), fn($q) => $q->whereIn('id', $data['recipients']))
                        ->get();

                    $roleUsers = collect();
                    if (!empty($data['roles'])) {
                        $roleUsers = User::with('phones')
                            ->whereIn('role', $data['roles'])
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
                        sms(
                            number: $entry['number'],
                            message: $data['message'],
                            sender: $data['sender'],
                        );
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
                        ->relationship('roles', 'name')
                ])
                ->action(function (array $data) {

                    $contactEmails = \App\Models\User::query()
                        ->when($data['contacts'], fn($q) => $q->whereIn('id', $data['contacts']))
                        ->pluck('email')
                        ->toArray();

                    $roleEmails = User::query()
                        ->when($data['roles'], fn($q) => $q->whereIn('role', $data['roles']))
                        ->pluck('email')
                        ->toArray();

                    $recipients = collect([...$contactEmails, ...$roleEmails])
                        ->filter()
                        ->unique()
                        ->values();
                    foreach ($recipients as $email) {
                        Mail::to($email)->send(new MassMessageMail(
                            title: $data['title'],
                            message: $data['message']
                        ));
                    }

                    Notification::make()
                        ->title('ელ. ფოსტები გაიგზავნა')
                        ->success()
                        ->send();
                })
        ];
    }

    public function getTabs(): array
    {
        $tabs = [];

        $tabs['ყველა'] = Tab::make('ყველა')
            ->badge(User::query()->count());

        $roles = Role::query()->get();

        foreach ($roles as $role) {
            $tabs[$role->name] = Tab::make($role->name)
                ->badge($role->users()->count())
                ->modifyQueryUsing(function ($query) use ($role) {
                    $query->whereHas('roles', fn($q) => $q->where('name', $role->name));
                });
        }

        return $tabs;
    }
}
