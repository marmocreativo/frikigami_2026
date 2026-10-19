<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'meta_title',
        'meta_description',
        'slug',
        'body',
        'cover_image',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function animes(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, 'anime_news')
            ->withTimestamps();
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Cuerpo en HTML (Markdown con el HTML escapado). Conserva los saltos de línea simples.
     */
    protected function bodyHtml(): Attribute
    {
        return Attribute::get(fn () => $this->renderMarkdown((string) $this->body));
    }

    protected function seoTitle(): Attribute
    {
        return Attribute::get(fn () => $this->meta_title ?: $this->title);
    }

    protected function seoDescription(): Attribute
    {
        return Attribute::get(function () {
            if ($this->meta_description) {
                return $this->meta_description;
            }

            $text = trim(preg_replace('/\s+/', ' ', strip_tags($this->renderMarkdown((string) $this->body))));

            return Str::limit($text, 155);
        });
    }

    private function renderMarkdown(string $text): string
    {
        return Str::markdown($text, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'renderer' => ['soft_break' => "<br>\n"],
        ]);
    }
}