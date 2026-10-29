<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use App\Models\Classification;
use App\Models\Director;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MovieController extends Controller
{
    public function index(): View
    {
        return view('movies.index', [
            'movies' => Movie::with(['classification', 'genres'])->orderBy('title')->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('movies.form', $this->formData(new Movie()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $movie = DB::transaction(function () use ($data): Movie {
            $movie = Movie::create($data);
            $movie->genres()->sync($data['genres'] ?? []);
            $movie->directors()->sync($data['directors'] ?? []);
            $movie->actors()->sync($this->orderedActors($data['actors'] ?? []));
            return $movie;
        });

        return redirect()->route('movies.index')->with('success', 'Película creada.');
    }

    public function edit(Movie $movie): View
    {
        $movie->load(['genres', 'directors', 'actors']);
        return view('movies.form', $this->formData($movie));
    }

    public function update(Request $request, Movie $movie): RedirectResponse
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($movie, $data): void {
            $movie->update($data);
            $movie->genres()->sync($data['genres'] ?? []);
            $movie->directors()->sync($data['directors'] ?? []);
            $movie->actors()->sync($this->orderedActors($data['actors'] ?? []));
        });

        return redirect()->route('movies.index')->with('success', 'Película actualizada.');
    }

    public function destroy(Movie $movie): RedirectResponse
    {
        if ($movie->screenings()->exists()) {
            return back()->withErrors(['movie' => 'No se puede eliminar una película con funciones programadas.']);
        }
        $movie->delete();

        return redirect()->route('movies.index')->with('success', 'Película eliminada.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'synopsis' => ['required', 'string', 'max:5000'],
            'duration_minutes' => ['required', 'integer', 'between:1,600'],
            'release_date' => ['nullable', 'date'],
            'poster_url' => ['nullable', 'url', 'max:2048'],
            'classification_id' => ['required', 'exists:classifications,id'],
            'is_active' => ['required', Rule::in(['0', '1', 0, 1])],
            'genres' => ['nullable', 'array'],
            'genres.*' => ['integer', 'distinct', 'exists:genres,id'],
            'directors' => ['nullable', 'array'],
            'directors.*' => ['integer', 'distinct', 'exists:directors,id'],
            'actors' => ['nullable', 'array'],
            'actors.*' => ['integer', 'distinct', 'exists:actors,id'],
        ]);
    }

    private function formData(Movie $movie): array
    {
        return [
            'movie' => $movie,
            'classifications' => Classification::orderBy('minimum_age')->get(),
            'genres' => Genre::orderBy('name')->get(),
            'directors' => Director::orderBy('name')->get(),
            'actors' => Actor::orderBy('name')->get(),
        ];
    }

    private function orderedActors(array $actorIds): array
    {
        $ordered = [];
        foreach (array_values($actorIds) as $index => $actorId) {
            $ordered[(int) $actorId] = ['billing_order' => $index + 1];
        }

        return $ordered;
    }
}
