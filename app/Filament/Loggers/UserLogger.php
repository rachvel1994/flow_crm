<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\RelationManager;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;
use Spatie\Activitylog\Models\Activity;

class UserLogger extends Logger
{
    public static ?string $model = User::class;

    public static function getLabel(): string|Htmlable|null
    {
        return UserResource::getModelLabel();
    }

    public function getSubjectRoute(Activity $activity): ?string
    {
        return UserResource::getUrl('edit', ['record' => $activity->subject_id]);
    }

    public function getRelationManagerRoute(Activity $activity): ?string
    {
        return $this->getSubjectRoute($activity) . '?activeRelationManager=0';
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('name')->label('სახელი'),
                Field::make('surname')->label('გვარი'),
                Field::make('type_id')
                    ->label('კონტაქტის ტიპი')
                    ->badge()
                    ->formatStateUsing(fn ($state) => UserType::firstWhere('id', $state)?->name),
                Field::make('roles.name')
                    ->label('როლები')
                    ->badge()
                    ->hasMany('roles'),
                Field::make('birthdate')->label('დაბ. თარიღი')->date(),
                Field::make('email')->label('ელ. ფოსტა'),
                Field::make('language')->label('ენა'),
                Field::make('teams.name')
                    ->label('ჯგუფები')
                    ->badge()
                    ->hasMany('teams'),
                Field::make('visibleContactTypes.name')
                    ->label('დასაშვები ტიპები')
                    ->badge()
                    ->hasMany('visibleContactTypes'),
                Field::make('mobile')->label('მობილური'),
                Field::make('address')->label('მისამართი'),
                Field::make('tags.name')
                    ->label('თეგები')
                    ->badge()
                    ->hasMany('tags'),
            ])
            ->relationManagers([
                RelationManager::make('phones')
                    ->label('ტელეფონის ნომრები')
                    ->fields([
                        Field::make('phone')->label('ნომერი'),
                    ]),

                RelationManager::make('socialLinks')
                    ->label('სოციალური ბმულები')
                    ->fields([
                        Field::make('label')->label('დასახელება'),
                        Field::make('url')->label('ლინკი'),
                    ]),

                RelationManager::make('locations')
                    ->label('ლოკაცია')
                    ->fields([
                        Field::make('location')->label('ლოკაცია'),
                    ]),
            ]);
    }
}
