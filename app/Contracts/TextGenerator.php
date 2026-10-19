<?php

namespace App\Contracts;

interface TextGenerator
{
    /**
     * Genera texto. Devuelve null si el proveedor no está configurado o falla.
     *
     * @param  array{max_tokens?: int, temperature?: float, json?: bool, model?: string}  $options
     */
    public function generate(string $system, string $prompt, array $options = []): ?string;
}