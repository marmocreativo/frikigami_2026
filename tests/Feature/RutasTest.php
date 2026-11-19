<?php

use App\Models\Anime;
use App\Models\AnimeSeason;
use App\Models\Episode;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->estreno = Season::create(['name' => 'Otoño', 'year' => 2025]);
    $this->anime = Anime::create(['title' => 'Serie Demo', 'slug' => 'serie-demo', 'status' => 'ongoing', 'season_id' => $this->estreno->id]);
    $this->temporada = AnimeSeason::create(['anime_id' => $this->anime->id, 'number' => 2, 'title' => null]);
    Episode::create(['anime_season_id' => $this->temporada->id, 'user_id' => $this->admin->id, 'number' => 5, 'title' => 'Cinco']);
});

it('responde en las URLs públicas nuevas', function (string $url) {
    $this->get($url)->assertOk();
})->with([
    '/', '/animes', '/animes/estreno/otono-2025', '/animes/serie-demo',
    '/animes/serie-demo/reparto', '/animes/serie-demo/personajes',
    '/animes/serie-demo/noticias', '/animes/serie-demo/episodios',
    '/animes/serie-demo/temporada-2/episodios',
    '/animes/serie-demo/temporada-2/episodios/5',
    '/noticias', '/personajes', '/personas', '/encuestas', '/tienda',
]);

it('devuelve 404 a temporada o episodio inexistentes', function () {
    $this->get('/animes/serie-demo/temporada-9/episodios')->assertNotFound();
    $this->get('/animes/serie-demo/temporada-2/episodios/99')->assertNotFound();
});

it('redirige 301 las URLs viejas', function () {
    $this->get('/animes/temporada/otono-2025')->assertStatus(301)->assertRedirect('/animes/estreno/otono-2025');
    $this->get('/animes/serie-demo/cast')->assertStatus(301)->assertRedirect('/animes/serie-demo/reparto');
});

it('responde en el admin en español', function (string $url) {
    $this->actingAs($this->admin)->get($url)->assertOk();
})->with([
    '/admin', '/admin/animes', '/admin/animes/serie-demo', '/admin/animes/serie-demo/edit',
    '/admin/temporadas', '/admin/episodios', '/admin/estrenos', '/admin/generos',
    '/admin/personajes', '/admin/personas', '/admin/noticias', '/admin/comentarios', '/admin/usuarios',
    '/admin/noticias/create', '/admin/estrenos/create', '/admin/generos/create',
]);

it('reserva los slugs genero y estreno', function () {
    $this->actingAs($this->admin)
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('/admin/animes', ['title' => 'Estreno', 'status' => 'upcoming'])
        ->assertSessionHasErrors('slug');
});
