<?php

namespace App\View\Components\Admin;

use App\Models\Anime;
use App\Services\AnimeContentTranslator;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AnimeTranslatePanel extends Component
{
    /** @var array<string, array{count: int, chars: int}> */
    public array $summary;

    public function __construct(public Anime $anime, AnimeContentTranslator $translator)
    {
        $this->summary = collect($translator->pending($anime))
            ->map(fn (array $texts) => [
                'count' => count($texts),
                'chars' => array_sum(array_map('mb_strlen', $texts)),
            ])
            ->all();
    }

    public function render(): View
    {
        return view('components.admin.anime-translate-panel');
    }
}