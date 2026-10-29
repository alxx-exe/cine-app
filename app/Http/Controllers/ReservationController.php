<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\ReservationSeat;
use App\Models\Screening;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(): View
    {
        $query = Reservation::with(['user', 'tickets.screening.movie', 'tickets.screening.screen.cinema', 'tickets.seat.screen'])
            ->latest();
        if (auth()->user()->hasRole('cliente')) {
            $query->where('user_id', auth()->id());
        }

        return view('reservations.index', ['reservations' => $query->paginate(15)]);
    }

    public function show(Screening $screening): View
    {
        abort_unless($screening->starts_at->isFuture(), 404);
        $screening->load(['movie.classification', 'screen.cinema', 'screen.seats', 'tariff']);
        abort_unless($screening->ticketPrice() !== null, 409, 'La función no tiene una tarifa configurada para este día.');
        $taken = $screening->tickets()->pluck('seat_id')->all();

        return view('reservations.seats', compact('screening', 'taken'));
    }

    public function store(Request $request, Screening $screening): RedirectResponse
    {
        $data = $request->validate([
            'seat_ids' => ['required', 'array', 'min:1', 'max:8'],
            'seat_ids.*' => ['required', 'integer', 'distinct', 'exists:seats,id'],
        ]);

        $reservation = DB::transaction(function () use ($screening, $data): Reservation {
            $lockedScreening = Screening::whereKey($screening->id)->lockForUpdate()->firstOrFail();
            if ($lockedScreening->starts_at->isPast()) {
                throw ValidationException::withMessages(['seat_ids' => 'Esta función ya comenzó y no admite reservas.']);
            }

            $seatIds = array_values(array_unique(array_map('intval', $data['seat_ids'])));
            $validSeats = $lockedScreening->screen->seats()->whereIn('id', $seatIds)->count();
            if ($validSeats !== count($seatIds)) {
                throw ValidationException::withMessages(['seat_ids' => 'Selecciona asientos válidos de esta sala.']);
            }

            $alreadyTaken = ReservationSeat::where('screening_id', $lockedScreening->id)
                ->whereIn('seat_id', $seatIds)->exists();
            if ($alreadyTaken) {
                throw ValidationException::withMessages(['seat_ids' => 'Uno o más asientos acaban de ser reservados. Elige otros.']);
            }

            $price = $lockedScreening->ticketPrice();
            if ($price === null) {
                throw ValidationException::withMessages(['seat_ids' => 'No hay precio configurado para esta función. Contacta al personal del cine.']);
            }
            $reservation = Reservation::create([
                'user_id' => auth()->id(),
                'status' => 'confirmada',
                'confirmation_code' => Str::upper(Str::random(10)),
            ]);

            foreach ($seatIds as $seatId) {
                ReservationSeat::create([
                    'reservation_id' => $reservation->id,
                    'screening_id' => $lockedScreening->id,
                    'seat_id' => $seatId,
                    'unit_price' => $price,
                ]);
            }

            return $reservation;
        }, 3);

        return redirect()->route('reservations.index')->with('success', 'Reserva confirmada. Código: '.$reservation->confirmation_code);
    }
}
