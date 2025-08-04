<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Team extends Model
{
    use HasSlug;

    protected $fillable = [
        'name',
        'board_name',
		'logo',
        'slug',
        'config',
        'is_active',
    ];

	protected function casts(): array
    {
        return [
            'logo' => 'array',
            'config' => 'json',
        ];
    }

    public function getBrandLogo(): ?string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return null;
    }

    public function getPrimaryColorCode()
    {
        return Arr::get($this->config ?? [], 'colors.primary') ?? '#0099a8';
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user');
    }

    public function taskStatuses(): HasMany
    {
        return $this->hasMany(TaskStatus::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function colors(): HasMany
    {
        return $this->hasMany(Color::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->preventOverwrite();
    }
}
