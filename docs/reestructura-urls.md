# Reestructura de URLs y nombres (todo en español)

Decisiones tomadas:
- Mercado hispanohablante: URLs, nombres de rutas y (en fase 2) modelos en español.
- Lo que es **de** un anime cuelga de `/animes/{slug}`; las entidades compartidas (personajes, personas, noticias) tienen URL canónica propia.
- Episodios con temporada en la URL (no requiere migrar números de episodio).
- "season" se separa en **Estreno** (invierno 2026) y **Temporada** (de una serie).

## 1. Público

```
/                                                    home
/animes                                              catálogo
/animes/genero/{slug}                                categoría
/animes/estreno/{invierno-2026}                      categoría (antes /temporada/)
/animes/{slug}                                       ficha
    /temporada-{n}/episodios                         lista de episodios de la temporada
    /temporada-{n}/episodios/{numero}                episodio
    /personajes                                      lista → enlaza a /personajes/{slug}
    /reparto                                         staff → enlaza a /personas/{slug}   (antes /cast)
    /noticias                                        lista → enlaza a /noticias/{slug}
/personajes            /personajes/{slug}            canónica
/personas              /personas/{slug}              canónica
/noticias              /noticias/{slug}              canónica
/encuestas  /tienda                                  placeholders
```

Notas:
- `/animes/{slug}/episodios` (sin temporada) redirige 301 a la primera temporada.
- Slugs reservados para animes: `genero`, `estreno`. Validar en `StoreAnimeRequest`.
- Redirecciones 301 desde las URLs viejas (`/animes/temporada/*`, `/animes/{slug}/cast`, `/animes/{slug}/episodios`).
- `/dashboard` del usuario: decidir si se conserva (no hay perfil todavía).

## 2. Admin (`/admin`)

```
/admin/animes                                        CRUD
/admin/animes/{anime}/temporadas                     CRUD (antes /admin/anime-seasons)
/admin/animes/{anime}/temporadas/{temporada}/episodios   CRUD (antes /admin/episodes)
/admin/animes/{anime}/reparto                        adjuntar / quitar staff (antes staff)
/admin/animes/{anime}/personajes                     adjuntar / quitar personajes
/admin/animes/{anime}/personajes/{personaje}/actor-de-voz
/admin/animes/{anime}/traducir                       POST
/admin/animes/{anime}/anilist                        sincronización
    /actualizar  /vincular
    /temporadas  /temporadas/vincular  /temporadas/{temporada}/episodios
    /reparto  /reparto/actualizar  /reparto/personajes  /reparto/equipo
/admin/estrenos                                      CRUD (antes /admin/seasons)
    /{estreno}/anilist  (+ importar, vincular, actualizar)
/admin/generos   /admin/personajes   /admin/personas
/admin/noticias  (+ /admin/noticias/expandir)
/admin/comentarios   /admin/usuarios
```

Los nombres de ruta siguen la URL: `admin.animes.temporadas.episodios.index`, `admin.noticias.create`, etc.

## 3. Fases

**Fase 1 — rutas (sin tocar base de datos)**
1. Reescribir `routes/web.php` con el esquema nuevo (URLs y nombres en español).
2. Actualizar los ~112 `route('...')` en ~85 archivos (controladores, vistas) con un renombre guiado y verificar con `php artisan route:list`.
3. Anidar episodios y temporadas bajo el anime (ajustar controladores y vistas de formulario).
4. Redirecciones 301 de las URLs públicas viejas.
5. Tests de humo (Pest) para cada URL pública.

**Fase 2 — modelos y tablas**
1. `Season` → `Estreno` (tabla `estrenos`), `AnimeSeason` → `Temporada` (tabla `temporadas`), `Episode` → `Episodio`, etc.
2. Migraciones de renombre de tablas y columnas (`season_id` → `estreno_id`, `anime_season_id` → `temporada_id`).
3. Opcional: renombrar el resto de modelos (`Anime`, `Genre`→`Genero`, `Character`→`Personaje`, `Person`→`Persona`, `News`→`Noticia`, `Comment`→`Comentario`).

Recomendación: hacer la fase 1 completa y funcionando antes de empezar la fase 2, para poder revisar cada una por separado.

## 4. Riesgos
- Renombrar tablas con datos reales: respaldar la base antes de la fase 2.
- Los servicios de AniList/Kitsu y los traductores usan nombres de modelo y columnas; hay que revisarlos en la fase 2.
