<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'last_name', 'email', 'password', 'role', 'avatar', 'google_id', 'google_avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Nombre y apellidos. */
    public function getFullNameAttribute(): string
    {
        return trim($this->name . ' ' . $this->last_name);
    }

    /** Foto de perfil: la subida, o la de Google, o null (se muestran iniciales). */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? Storage::url($this->avatar) : $this->google_avatar;
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(mb_substr($this->name, 0, 1) . mb_substr((string) $this->last_name, 0, 1));
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}