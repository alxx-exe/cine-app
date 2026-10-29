<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\PriceRule;
use App\Models\Reservation;
use App\Models\ReservationSeat;
use App\Models\Role;
use App\Models\Screen;
use App\Models\Screening;
use App\Models\Seat;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SeatReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'administrador']);
        Role::firstOrCreate(['name' => 'empleado']);
        Role::firstOrCreate(['name' => 'cliente']);
    }

    public function test_a_seat_can_only_be_reserved_once_for_a_screening(): void
    {
        $cinema = Cinema::create(['name' => 'Sala de prueba', 'address' => 'Calle 1', 'city' => 'Bogota']);
        $screen = Screen::create(['cinema_id' => $cinema->id, 'name' => 'Sala 1', 'capacity' => 1]);
        $seat = Seat::create(['screen_id' => $screen->id, 'row_label' => 'A', 'number' => 1]);
        $classification = \App\Models\Classification::create(['code' => 'T', 'description' => 'Todo publico', 'minimum_age' => 0]);
        $movie = Movie::create([
            'classification_id' => $classification->id,
            'title' => 'Prueba',
            'synopsis' => 'Película de prueba.',
            'duration_minutes' => 90,
            'is_active' => true,
        ]);
        $tariff = Tariff::create(['name' => 'Prueba', 'is_active' => true]);
        PriceRule::create([
            'cinema_id' => $cinema->id,
            'tariff_id' => $tariff->id,
            'day_of_week' => now()->addDay()->dayOfWeek,
            'amount' => 10000,
        ]);
        $screening = Screening::create([
            'movie_id' => $movie->id,
            'screen_id' => $screen->id,
            'tariff_id' => $tariff->id,
            'starts_at' => now()->addDay(),
            'format' => '2D',
            'language' => 'Español',
        ]);
        $this->assertSame('10000.00', $screening->ticketPrice());
        $customer = User::create([
            'role_id' => Role::where('name', 'cliente')->value('id'),
            'name' => 'Cliente',
            'email' => 'cliente@example.test',
            'password' => Str::random(16).'aA1',
        ]);
        $firstReservation = Reservation::create([
            'user_id' => $customer->id,
            'status' => 'confirmada',
            'confirmation_code' => 'PRUEBA0001',
        ]);
        ReservationSeat::create([
            'reservation_id' => $firstReservation->id,
            'screening_id' => $screening->id,
            'seat_id' => $seat->id,
            'unit_price' => 10000,
        ]);

        $secondReservation = Reservation::create([
            'user_id' => $customer->id,
            'status' => 'confirmada',
            'confirmation_code' => 'PRUEBA0002',
        ]);

        $this->expectException(QueryException::class);
        ReservationSeat::create([
            'reservation_id' => $secondReservation->id,
            'screening_id' => $screening->id,
            'seat_id' => $seat->id,
            'unit_price' => 10000,
        ]);
    }

    public function test_a_seat_from_another_screen_cannot_be_attached_to_the_showing(): void
    {
        $cinema = Cinema::create(['name' => 'Cine', 'address' => 'Calle 1', 'city' => 'Bogota']);
        $firstScreen = Screen::create(['cinema_id' => $cinema->id, 'name' => 'Sala 1', 'capacity' => 1]);
        $secondScreen = Screen::create(['cinema_id' => $cinema->id, 'name' => 'Sala 2', 'capacity' => 1]);
        $seat = Seat::create(['screen_id' => $secondScreen->id, 'row_label' => 'A', 'number' => 1]);
        $classification = \App\Models\Classification::create(['code' => 'T', 'description' => 'Todo publico', 'minimum_age' => 0]);
        $movie = Movie::create(['classification_id' => $classification->id, 'title' => 'Prueba', 'synopsis' => 'Prueba.', 'duration_minutes' => 90, 'is_active' => true]);
        $tariff = Tariff::create(['name' => 'General', 'is_active' => true]);
        $screening = Screening::create([
            'movie_id' => $movie->id,
            'screen_id' => $firstScreen->id,
            'tariff_id' => $tariff->id,
            'starts_at' => now()->addDay(),
            'format' => '2D',
            'language' => 'Español',
        ]);
        $customer = User::create(['role_id' => Role::where('name', 'cliente')->value('id'), 'name' => 'Cliente', 'email' => 'otra@example.test', 'password' => Str::random(16).'aA1']);
        $reservation = Reservation::create(['user_id' => $customer->id, 'status' => 'confirmada', 'confirmation_code' => 'PRUEBA0003']);

        $this->expectException(QueryException::class);
        ReservationSeat::create([
            'reservation_id' => $reservation->id,
            'screening_id' => $screening->id,
            'seat_id' => $seat->id,
            'unit_price' => 10000,
        ]);
    }
}
