<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticate;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Tags\HasTags;

class User extends Authenticate implements FilamentUser, HasTenants
{
    use HasFactory, Notifiable, HasRoles, HasTags;

    protected $fillable = [
        'name',
        'email',
		'address',
		'mobile',
        'type_id',
        'password',
        'surname',
        'birthdate',
        'language',
        'image',
        'location',
        'created_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'image' => 'array',
            'birthdate' => 'date',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
      return $this->can('can_access_panel_user');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }

    public function getTenants(Panel $panel): array|Collection
    {
        return $this->teams;
    }

    public function phones(): HasMany
    {
        return $this->hasMany(UserPhone::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(UserLocation::class);
    }

    public function socialLinks(): HasMany
    {
        return $this->hasMany(UserSocialLink::class);
    }

    public function user_type(): BelongsTo
    {
        return $this->belongsTo(UserType::class, 'type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function visibleContactTypes(): BelongsToMany
    {
        return $this->belongsToMany(UserType::class, 'contact_type_user_permission', 'user_id', 'contact_type_id');
    }

    public function permittedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'contact_type_user_permission', 'contact_type_id', 'user_id');
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->teams()->whereKey($tenant)->exists();
    }

	public function getFullNameAttribute(): string
    {
        return $this->name . ' ' . $this->surname;
    }
}
