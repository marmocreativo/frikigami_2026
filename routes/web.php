<?php

use App\Http\Controllers\AdminAniListController;
use App\Http\Controllers\AdminAniListSearchController;
use App\Http\Controllers\AdminAnimeCastSyncController;
use App\Http\Controllers\AdminAnimeController;
use App\Http\Controllers\AdminAnimeSeasonController;
use App\Http\Controllers\AdminAnimeSyncController;
use App\Http\Controllers\AdminAnimeTranslateController;
use App\Http\Controllers\AdminCharacterController;
use App\Http\Controllers\AdminCommentController;
use App\Http\Controllers\AdminEpisodeController;
use App\Http\Controllers\AdminGenreController;
use App\Http\Controllers\AdminNewsAiController;
use App\Http\Controllers\AdminNewsController;
use App\Http\Controllers\AdminPersonController;
use App\Http\Controllers\AdminSeasonController;
use App\Http\Controllers\AdminSlideController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AnimeController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

// ─── Rutas públicas ───────────────────────────────────────────────────────────

Route::get('/', HomeController::class)->name('home');

// Animes
// Los segmentos fijos (genero, estreno) van antes que {anime:slug}.
// Esos slugs están reservados (ver StoreAnimeRequest).
Route::controller(AnimeController::class)
    ->prefix('animes')
    ->name('animes.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('genero/{genre:slug}', 'genero')->name('genero');
        Route::get('estreno/{estreno}', 'estreno')->name('estreno');

        Route::get('{anime:slug}', 'show')->name('show');
        Route::get('{anime:slug}/temporadas', 'temporadas')->name('temporadas');
        Route::get('{anime:slug}/reparto', 'reparto')->name('reparto');
        Route::get('{anime:slug}/personajes', 'personajes')->name('personajes');
        Route::get('{anime:slug}/noticias', 'noticias')->name('noticias');

        Route::get('{anime:slug}/episodios', 'episodios')->name('episodios');
        Route::get('{anime:slug}/temporada-{numero}/episodios', 'temporada')
            ->whereNumber('numero')->name('temporada');
        Route::get('{anime:slug}/temporada-{numero}/episodios/{episodio}', 'episodio')
            ->whereNumber(['numero', 'episodio'])->name('episodio');
    });

// Redirecciones 301 desde las URLs anteriores
Route::get('animes/temporada/{valor}', fn (string $valor) => redirect()->route('animes.estreno', $valor, 301));
Route::get('animes/{anime:slug}/cast', fn (App\Models\Anime $anime) => redirect()->route('animes.reparto', $anime, 301));

// Noticias
Route::get('/noticias', [NewsController::class, 'index'])->name('noticias.index');
Route::get('/noticias/{news:slug}', [NewsController::class, 'show'])->name('noticias.show');

// Personas
Route::get('/personas', [PersonController::class, 'index'])->name('personas.index');
Route::get('/personas/{person:slug}', [PersonController::class, 'show'])->name('personas.show');

// Personajes
Route::get('/personajes', [CharacterController::class, 'index'])->name('personajes.index');
Route::get('/personajes/{character:slug}', [CharacterController::class, 'show'])->name('personajes.show');

// Encuestas y Tienda (placeholders)
Route::get('/encuestas', function () { return view('polls.index'); })->name('encuestas.index');
Route::get('/tienda', function () { return view('shop.index'); })->name('tienda.index');

// Login con Google
Route::middleware('guest')->prefix('auth/google')->name('google.')->group(function () {
    Route::get('/', [GoogleAuthController::class, 'redirect'])->name('redirect');
    Route::get('callback', [GoogleAuthController::class, 'callback'])->name('callback');
});

// ─── Rutas autenticadas (usuarios registrados) ────────────────────────────────

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Perfil
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('perfil.update');
    Route::delete('/perfil/foto', [ProfileController::class, 'destroyAvatar'])->name('perfil.foto.destroy');

    // Calificar (1 a 5 estrellas) y comentar animes y episodios
    Route::post('/calificaciones', [ReviewController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('calificaciones.store');

    // Comentarios
    Route::post('/comentarios', [CommentController::class, 'store'])->name('comentarios.store');
    Route::delete('/comentarios/{comment}', [CommentController::class, 'destroy'])->name('comentarios.destroy');

});

// ─── Rutas de admin ───────────────────────────────────────────────────────────

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Animes
    Route::resource('animes', AdminAnimeController::class);

    // Buscar en AniList: franquicia (precuelas y secuelas) e importación
    Route::prefix('anilist')->name('anilist.')->group(function () {
        Route::get('buscar', [AdminAniListSearchController::class, 'buscar'])->name('buscar');
        Route::get('franquicia/{id}', [AdminAniListSearchController::class, 'franquicia'])->whereNumber('id')->name('franquicia');
        Route::post('franquicia/{id}/importar', [AdminAniListSearchController::class, 'importar'])->whereNumber('id')->name('importar');
    });

    // Sincronización con AniList
    Route::prefix('animes/{anime}/anilist')->name('animes.anilist')->group(function () {
        Route::get('/', [AdminAnimeSyncController::class, 'show'])->name('');
        Route::post('actualizar', [AdminAnimeSyncController::class, 'refresh'])->name('.actualizar');
        Route::post('vincular', [AdminAnimeSyncController::class, 'linkAnime'])->name('.vincular');
        Route::post('temporadas', [AdminAnimeSyncController::class, 'importSeasons'])->name('.temporadas');
        Route::post('temporadas/vincular', [AdminAnimeSyncController::class, 'linkSeason'])->name('.temporadas.vincular');
        Route::post('temporadas/{animeSeason}/episodios', [AdminAnimeSyncController::class, 'importEpisodes'])->name('.episodios');

        Route::get('reparto', [AdminAnimeCastSyncController::class, 'show'])->name('.reparto');
        Route::post('reparto/actualizar', [AdminAnimeCastSyncController::class, 'refresh'])->name('.reparto.actualizar');
        Route::post('reparto/personajes', [AdminAnimeCastSyncController::class, 'importCharacters'])->name('.reparto.personajes');
        Route::post('reparto/equipo', [AdminAnimeCastSyncController::class, 'importStaff'])->name('.reparto.equipo');
    });

    Route::post('animes/{anime}/mover-a-temporada', [AdminAnimeController::class, 'moverATemporada'])->name('animes.mover');
    Route::post('animes/{anime}/traducir', [AdminAnimeTranslateController::class, 'store'])->name('animes.traducir');

    // Reparto del anime (staff)
    Route::post('animes/{anime}/reparto', [AdminAnimeController::class, 'attachStaff'])->name('animes.reparto.adjuntar');
    Route::delete('animes/{anime}/reparto/{person}', [AdminAnimeController::class, 'detachStaff'])->name('animes.reparto.quitar');

    // Personajes del anime
    Route::post('animes/{anime}/personajes', [AdminAnimeController::class, 'attachCharacter'])->name('animes.personajes.adjuntar');
    Route::delete('animes/{anime}/personajes/{character}', [AdminAnimeController::class, 'detachCharacter'])->name('animes.personajes.quitar');

    // Actor de voz de un personaje en un anime
    Route::post('animes/{anime}/personajes/{character}/actor-de-voz', [AdminAnimeController::class, 'attachVoiceActor'])->name('animes.actor_voz.adjuntar');
    Route::delete('animes/{anime}/personajes/{character}/actor-de-voz/{person}', [AdminAnimeController::class, 'detachVoiceActor'])->name('animes.actor_voz.quitar');

    // Temporadas de una serie (AnimeSeason)
    Route::resource('temporadas', AdminAnimeSeasonController::class)
        ->parameters(['temporadas' => 'anime_season']);

    // Estrenos (temporada del año): comparar con AniList
    Route::get('estrenos/{season}/anilist', [AdminAniListController::class, 'show'])->name('estrenos.anilist');
    Route::post('estrenos/{season}/anilist/actualizar', [AdminAniListController::class, 'refresh'])->name('estrenos.anilist.actualizar');
    Route::post('estrenos/{season}/anilist/vincular', [AdminAniListController::class, 'link'])->name('estrenos.anilist.vincular');
    Route::post('estrenos/{season}/anilist/importar', [AdminAniListController::class, 'import'])->name('estrenos.anilist.importar');

    Route::resource('estrenos', AdminSeasonController::class)
        ->parameters(['estrenos' => 'season']);

    // Sliders del inicio
    Route::resource('sliders', AdminSlideController::class)
        ->parameters(['sliders' => 'slide'])
        ->except('show');

    // Géneros
    Route::resource('generos', AdminGenreController::class)
        ->parameters(['generos' => 'genre']);

    // Comentarios
    Route::resource('comentarios', AdminCommentController::class)
        ->parameters(['comentarios' => 'comment'])
        ->only(['index', 'destroy']);

    // Usuarios
    Route::resource('usuarios', AdminUserController::class)
        ->parameters(['usuarios' => 'user'])
        ->only(['index', 'edit', 'update', 'destroy']);

    // Noticias
    Route::post('noticias/expandir', [AdminNewsAiController::class, 'expand'])->name('noticias.expandir');
    Route::resource('noticias', AdminNewsController::class)
        ->parameters(['noticias' => 'news']);

    // Episodios
    Route::resource('episodios', AdminEpisodeController::class)
        ->parameters(['episodios' => 'episode']);

    // Personas
    Route::resource('personas', AdminPersonController::class)
        ->parameters(['personas' => 'person']);

    // Personajes
    Route::resource('personajes', AdminCharacterController::class)
        ->parameters(['personajes' => 'character']);

});
