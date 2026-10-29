<?php

namespace Database\Seeders;

use App\Models\Actor;
use App\Models\Cinema;
use App\Models\Classification;
use App\Models\Director;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\PriceRule;
use App\Models\Role;
use App\Models\Screen;
use App\Models\Screening;
use App\Models\Seat;
use App\Models\Tariff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach (['administrador', 'empleado', 'cliente'] as $name) {
                Role::firstOrCreate(['name' => $name]);
            }

            $cinema = Cinema::firstOrCreate(
                ['name' => 'Cine Aurora'],
                ['address' => 'Carrera 7 # 45-20', 'city' => 'Bogota', 'phone' => '601 555 0100']
            );
            $screen = Screen::firstOrCreate(
                ['cinema_id' => $cinema->id, 'name' => 'Sala 1'],
                ['capacity' => 60]
            );

            for ($row = 0; $row < 6; $row++) {
                foreach (range(1, 10) as $number) {
                    Seat::firstOrCreate([
                        'screen_id' => $screen->id,
                        'row_label' => chr(65 + $row),
                        'number' => $number,
                    ], ['seat_type' => 'general']);
                }
            }

            $classification = Classification::firstOrCreate(
                ['code' => 'PG-13'],
                ['description' => 'Acompanamiento sugerido para menores de 13 anos', 'minimum_age' => 13]
            );
            $genres = collect(['Aventura', 'Ciencia ficcion'])->map(fn (string $name) => Genre::firstOrCreate(['name' => $name]));
            $director = Director::firstOrCreate(['name' => 'Sofia Reyes']);
            $actors = collect(['Lucia Torres', 'Mateo Silva'])->map(fn (string $name) => Actor::firstOrCreate(['name' => $name]));
            $movie = Movie::firstOrCreate(
                ['title' => 'Horizonte de cristal'],
                [
                    'classification_id' => $classification->id,
                    'synopsis' => 'Una expedicion descubre una ciudad oculta bajo un mar de hielo y debe decidir que secretos compartir.',
                    'duration_minutes' => 118,
                    'release_date' => today()->subMonths(2),
                    'poster_url' => null,
                    'is_active' => true,
                ]
            );
            $movie->genres()->syncWithoutDetaching($genres->pluck('id')->all());
            $movie->directors()->syncWithoutDetaching([$director->id]);
            foreach ($actors->values() as $order => $actor) {
                $movie->actors()->syncWithoutDetaching([$actor->id => ['billing_order' => $order + 1]]);
            }

            $tariff = Tariff::firstOrCreate(
                ['name' => 'General'],
                ['description' => 'Entrada general', 'is_active' => true]
            );
            foreach (range(0, 6) as $dayOfWeek) {
                PriceRule::firstOrCreate([
                    'cinema_id' => $cinema->id,
                    'tariff_id' => $tariff->id,
                    'day_of_week' => $dayOfWeek,
                ], ['amount' => $dayOfWeek >= 1 && $dayOfWeek <= 4 ? 16000 : 18000]);
            }
            Screening::firstOrCreate([
                'movie_id' => $movie->id,
                'screen_id' => $screen->id,
                'starts_at' => today()->addDay()->setTime(19, 0),
            ], [
                'tariff_id' => $tariff->id,
                'format' => '2D',
                'language' => 'Español',
            ]);
        });
    }
}
