<?php

namespace App\View\Components;

use App\Models\Episode;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

/**
 * Calificación (1 a 5 estrellas) y comentarios de un anime o un episodio.
 */
class ReviewSection extends Component
{
    public const PER_PAGE = 10;

    public string $type;
    public ?float $average;
    public int $count;
    public float $display;
    public ?int $userStars = null;
    public int $defaultStars;
    public Collection $comments;
    public int $commentsTotal;
    public array $starsByUser = [];

    public function __construct(public Model $item)
    {
        $this->type = $item instanceof Episode ? 'episodio' : 'anime';
        $this->defaultStars = $item::DEFAULT_STARS;

        $this->count = $item->ratings()->count();
        $this->average = $item->averageRating();
        $this->display = $item->displayRating();

        if ($user = auth()->user()) {
            $this->userStars = $item->ratings()->where('user_id', $user->id)->value('stars');
        }

        $this->commentsTotal = $item->comments()->count();
        $this->comments = $item->comments()->with('user')->latest()->take(self::PER_PAGE)->get();

        // Estrellas que dejó cada autor de los comentarios mostrados
        $this->starsByUser = $item->ratings()
            ->whereIn('user_id', $this->comments->pluck('user_id'))
            ->pluck('stars', 'user_id')
            ->all();
    }

    public function render(): View
    {
        return view('components.review-section');
    }
}
