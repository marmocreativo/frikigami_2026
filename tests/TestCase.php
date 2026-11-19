<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Seguro: los tests nunca deben correr contra la base de datos real,
     * porque RefreshDatabase ejecuta migrate:fresh.
     */
    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        if (config('database.default') !== 'sqlite') {
            throw new RuntimeException(
                'Los tests deben usar SQLite en memoria, pero la conexión es "'.config('database.default').'". Abortado para no borrar la base de datos real.'
            );
        }
    }
}
