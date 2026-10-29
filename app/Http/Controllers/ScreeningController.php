<?php

namespace App\Http\Controllers;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\PriceRule;
use App\Models\Screen;
use App\Models\Screening;
use App\Models\Tariff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ScreeningController extends Controller
{
    public function index(): View
    {
        return view('screenings.index', [
            'screenings' => Screening::with(['movie', 'screen.cinema', 'tariff'])
                ->orderByDesc('starts_at')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('screenings.form', $this->formData(new Screening()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->ensureScreenBelongsToCinema($data);
        unset($data['cinema_id']);
        Screening::create($data);

        return redirect()->route('screenings.index')->with('success', 'Función programada.');
    }

    public function edit(Screening $screening): View
    {
        return view('screenings.form', $this->formData($screening));
    }

    public function update(Request $request, Screening $screening): RedirectResponse
    {
        $data = $this->validated($request);
        $this->ensureScreenBelongsToCinema($data);
        unset($data['cinema_id']);
        $newStart = \Illuminate\Support\Carbon::parse($data['starts_at'])->format('Y-m-d\TH:i');
        if ($screening->tickets()->exists() && ((int) $data['screen_id'] !== $screening->screen_id || $newStart !== $screening->starts_at->format('Y-m-d\TH:i'))) {
            return back()->withErrors(['screening' => 'No puedes cambiar sala u horario de una función con asientos reservados.'])->withInput();
        }
        $screening->update($data);

        return redirect()->route('screenings.index')->with('success', 'Función actualizada.');
    }

    public function destroy(Screening $screening): RedirectResponse
    {
        if ($screening->tickets()->exists()) {
            return back()->withErrors(['screening' => 'No se puede eliminar una función con entradas reservadas.']);
        }
        $screening->delete();

        return redirect()->route('screenings.index')->with('success', 'Función eliminada.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'movie_id' => ['required', 'exists:movies,id'],
            'cinema_id' => ['required', 'exists:cinemas,id'],
            'screen_id' => ['required', 'exists:screens,id'],
            'tariff_id' => ['required', 'exists:tariffs,id'],
            'starts_at' => ['required', 'date', 'after:now'],
            'format' => ['required', 'in:2D,3D,IMAX'],
            'language' => ['required', 'string', 'max:30'],
        ]);
    }

    private function ensureScreenBelongsToCinema(array $data): void
    {
        $screen = Screen::whereKey($data['screen_id'])->where('cinema_id', $data['cinema_id'])->first();
        if (! $screen) {
            throw ValidationException::withMessages(['screen_id' => 'La sala seleccionada no pertenece al cine elegido.']);
        }
        $day = \Illuminate\Support\Carbon::parse($data['starts_at'])->dayOfWeek;
        if (! PriceRule::where('cinema_id', $data['cinema_id'])
            ->where('tariff_id', $data['tariff_id'])->where('day_of_week', $day)->exists()) {
            throw ValidationException::withMessages(['tariff_id' => 'No existe un precio para esa tarifa, cine y día de la semana.']);
        }
    }

    private function formData(Screening $screening): array
    {
        return [
            'screening' => $screening,
            'movies' => Movie::where('is_active', true)->orderBy('title')->get(),
            'cinemas' => Cinema::with('screens')->orderBy('name')->get(),
            'tariffs' => Tariff::where('is_active', true)->orderBy('name')->get(),
        ];
    }
}
