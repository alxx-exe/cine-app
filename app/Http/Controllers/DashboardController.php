<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Reservation;
use App\Models\Screening;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $screenings = Screening::with(['movie', 'screen.cinema', 'tariff'])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->take(6)
            ->get();

        return view('dashboard', [
            'screenings' => $screenings,
            'movieCount' => Movie::where('is_active', true)->count(),
            'reservationCount' => auth()->user()->hasRole('cliente')
                ? auth()->user()->reservations()->count()
                : Reservation::count(),
            'userCount' => auth()->user()->hasRole('administrador') ? User::count() : null,
        ]);
    }
}
