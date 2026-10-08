<?php

use Illuminate\Support\Facades\Route;

// ─── Rutas públicas ───────────────────────────────────────────────────────────

Route::get('/', App\Http\Controllers\HomeController::class)->name('home');

// Animes
Route::controller(App\Http\Controllers\AnimeController::class)
    ->prefix('animes')
    ->name('animes.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('temporada/{temporada}', 'season')->name('season');
        Route::get('genero/{genre:slug}', 'genre')->name('genre');

        Route::get('{anime:slug}', 'show')->name('show');
        Route::get('{anime:slug}/cast', 'cast')->name('cast');
        Route::get('{anime:slug}/personajes', 'characters')->name('characters');
        Route::get('{anime:slug}/episodios', 'episodes')->name('episodes');
        Route::get('{anime:slug}/noticias', 'news')->name('news');
    });

// Noticias
Route::get('/noticias', [App\Http\Controllers\NewsController::class, 'index'])->name('news.index');
Route::get('/noticias/{news:slug}', [App\Http\Controllers\NewsController::class, 'show'])->name('news.show');

// Personas
Route::get('/personas', [App\Http\Controllers\PersonController::class, 'index'])->name('people.index');
Route::get('/personas/{person:slug}', [App\Http\Controllers\PersonController::class, 'show'])->name('people.show');

// Personajes
Route::get('/personajes', [App\Http\Controllers\CharacterController::class, 'index'])->name('characters.index');
Route::get('/personajes/{character:slug}', [App\Http\Controllers\CharacterController::class, 'show'])->name('characters.show');

// Encuestas y Tienda (placeholders)
Route::get('/encuestas', function () { return view('polls.index'); })->name('polls.index');
Route::get('/tienda', function () { return view('shop.index'); })->name('shop.index');

// ─── Rutas autenticadas (usuarios registrados) ────────────────────────────────

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Comentarios
    Route::post('/comentarios', [App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comentarios/{comment}', [App\Http\Controllers\CommentController::class, 'destroy'])->name('comments.destroy');

});

// ─── Rutas de admin ───────────────────────────────────────────────────────────

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Animes
    Route::resource('animes', App\Http\Controllers\AdminAnimeController::class);

    // Cast del anime (staff)
    Route::post('animes/{anime}/staff', [App\Http\Controllers\AdminAnimeController::class, 'attachStaff'])->name('animes.staff.attach');
    Route::delete('animes/{anime}/staff/{person}', [App\Http\Controllers\AdminAnimeController::class, 'detachStaff'])->name('animes.staff.detach');

    // Personajes del anime
    Route::post('animes/{anime}/characters', [App\Http\Controllers\AdminAnimeController::class, 'attachCharacter'])->name('animes.characters.attach');
    Route::delete('animes/{anime}/characters/{character}', [App\Http\Controllers\AdminAnimeController::class, 'detachCharacter'])->name('animes.characters.detach');

    // Seiyuu de un personaje en un anime
    Route::post('animes/{anime}/characters/{character}/voice-actor', [App\Http\Controllers\AdminAnimeController::class, 'attachVoiceActor'])->name('animes.voiceactor.attach');
    Route::delete('animes/{anime}/characters/{character}/voice-actor/{person}', [App\Http\Controllers\AdminAnimeController::class, 'detachVoiceActor'])->name('animes.voiceactor.detach');
    
    // Animes Seasons
    Route::resource('anime-seasons', App\Http\Controllers\AdminAnimeSeasonController::class);

    Route::resource('seasons', App\Http\Controllers\AdminSeasonController::class);

    // Generos
    Route::resource('genres', App\Http\Controllers\AdminGenreController::class);

    // Coments
    Route::resource('comments', App\Http\Controllers\AdminCommentController::class)->only(['index', 'destroy']);

    // Users
    Route::resource('users', App\Http\Controllers\AdminUserController::class)->only(['index', 'edit', 'update', 'destroy']);

    // Noticias
    Route::resource('news', App\Http\Controllers\AdminNewsController::class);

    // Episodios
    Route::resource('episodes', App\Http\Controllers\AdminEpisodeController::class);

    // Personas
    Route::resource('people', App\Http\Controllers\AdminPersonController::class);

    // Personajes
    Route::resource('characters', App\Http\Controllers\AdminCharacterController::class);

});