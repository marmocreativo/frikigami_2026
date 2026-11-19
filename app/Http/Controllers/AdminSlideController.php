<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Slide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminSlideController extends Controller
{
    public function index(): View
    {
        $slides = Slide::with('anime')->ordered()->get();

        return view('admin.slides.index', compact('slides'));
    }

    public function create(): View
    {
        return view('admin.slides.create', ['animes' => $this->animes()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, imageRequired: true);

        $data['image'] = $request->file('image')->store('sliders', 'public');
        $data['position'] ??= (Slide::max('position') ?? 0) + 1;
        $data['is_active'] = $request->boolean('is_active');

        Slide::create($data);

        return redirect()->route('admin.sliders.index')->with('status', 'Slider creado correctamente.');
    }

    public function edit(Slide $slide): View
    {
        return view('admin.slides.edit', ['slide' => $slide, 'animes' => $this->animes()]);
    }

    public function update(Request $request, Slide $slide): RedirectResponse
    {
        $data = $this->validated($request, imageRequired: false);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($slide->image);
            $data['image'] = $request->file('image')->store('sliders', 'public');
        } else {
            unset($data['image']);
        }

        $data['position'] ??= $slide->position;
        $data['is_active'] = $request->boolean('is_active');

        $slide->update($data);

        return redirect()->route('admin.sliders.index')->with('status', 'Slider actualizado correctamente.');
    }

    public function destroy(Slide $slide): RedirectResponse
    {
        Storage::disk('public')->delete($slide->image);
        $slide->delete();

        return redirect()->route('admin.sliders.index')->with('status', 'Slider eliminado.');
    }

    private function validated(Request $request, bool $imageRequired): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'image' => [$imageRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'anime_id' => ['nullable', 'exists:animes,id'],
            // Dirección completa (https://...) o ruta interna (/noticias/...)
            'link' => ['nullable', 'string', 'max:2048', 'regex:#^(https?://|/)#i'],
            'position' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ], [
            'image.required' => 'Sube la imagen del slider.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe ser JPG, PNG o WebP.',
            'image.max' => 'La imagen no puede pesar más de 4 MB.',
            'link.regex' => 'El link debe empezar con http://, https:// o con / (ruta del sitio).',
        ]);
    }

    private function animes()
    {
        return Anime::orderBy('title')->get(['id', 'title']);
    }
}
