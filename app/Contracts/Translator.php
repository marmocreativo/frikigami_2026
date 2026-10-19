<?php

namespace App\Contracts;

interface Translator
{
    /**
     * Traduce de inglés a español varios textos en una sola operación.
     * Conserva las claves del arreglo. Devuelve solo los que se tradujeron, o null si falla todo.
     *
     * @param  array<int|string, string>  $texts
     * @return array<int|string, string>|null
     */
    public function toSpanish(array $texts): ?array;
}