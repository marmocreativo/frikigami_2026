<?php

namespace App\Console\Commands;

use App\Contracts\Translator;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Person;
use Illuminate\Console\Command;

class TranslateContent extends Command
{
    protected $signature = 'content:translate
                            {--only=* : Tipos a traducir: animes, episodes, characters, people}
                            {--limit=200 : Máximo de registros por tipo en esta ejecución}
                            {--dry-run : Solo cuenta lo que se traduciría}
                            {--yes : No pedir confirmación}';

    protected $description = 'Traduce al español las sinopsis y descripciones que estén en inglés';

    private const TARGETS = [
        'animes' => [Anime::class, 'synopsis'],
        'episodes' => [Episode::class, 'synopsis'],
        'characters' => [Character::class, 'description'],
        'people' => [Person::class, 'bio'],
    ];

    public function handle(Translator $translator): int
    {
        $only = $this->option('only') ?: array_keys(self::TARGETS);
        $limit = max(1, (int) $this->option('limit'));
        $pending = [];

        foreach ($only as $type) {
            if (! isset(self::TARGETS[$type])) {
                $this->error("Tipo desconocido: {$type}");

                return self::FAILURE;
            }

            [$model, $column] = self::TARGETS[$type];
            $found = [];

            $model::query()
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->orderBy('id')
                ->select(['id', $column])
                ->chunkById(200, function ($records) use (&$found, $column, $limit) {
                    foreach ($records as $record) {
                        if ($this->looksEnglish($record->{$column})) {
                            $found[$record->id] = $record->{$column};
                        }

                        if (count($found) >= $limit) {
                            return false;
                        }
                    }
                });

            $pending[$type] = $found;

            $this->line(sprintf(
                '%-11s %d por traducir (%s caracteres)',
                $type,
                count($found),
                number_format(array_sum(array_map('mb_strlen', $found)))
            ));
        }

        $total = array_sum(array_map('count', $pending));

        if ($total === 0) {
            $this->info('No hay nada en inglés por traducir.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        if (! $this->option('yes') && ! $this->confirm("¿Traducir {$total} textos?")) {
            return self::SUCCESS;
        }

        $done = 0;
        $failed = 0;

        foreach ($pending as $type => $texts) {
            [$model, $column] = self::TARGETS[$type];

            foreach (array_chunk($texts, 20, true) as $batch) {
                $translated = $translator->toSpanish($batch) ?? [];

                foreach ($translated as $id => $text) {
                    $model::whereKey($id)->update([$column => $text]);
                }

                $done += count($translated);
                $failed += count($batch) - count($translated);

                $this->line("  {$type}: " . count($translated) . '/' . count($batch));
            }
        }

        $this->info("Traducidos: {$done}. Sin traducir: {$failed}.");

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Heurística simple: más palabras comunes del inglés que del español.
     */
    private function looksEnglish(string $text): bool
    {
        $english = preg_match_all('/\b(the|and|of|to|in|is|with|his|her|that|for)\b/i', $text);
        $spanish = preg_match_all('/\b(el|la|los|las|de|que|y|en|con|su|para|una|un)\b/i', $text);

        return $english > $spanish;
    }
}