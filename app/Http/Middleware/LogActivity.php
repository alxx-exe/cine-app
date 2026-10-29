<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            && $response->getStatusCode() < 400) {
            $subject = collect($request->route()?->parameters() ?? [])
                ->first(fn (mixed $parameter) => is_object($parameter) && method_exists($parameter, 'getTable'));
            $routeArea = explode('.', (string) $request->route()?->getName())[0];
            $affectedTable = $subject?->getTable() ?? match ($routeArea) {
                'users' => 'users',
                'movies' => 'movies',
                'screenings' => 'screenings',
                'reservations' => 'reservations',
                default => null,
            };

            DB::table('activity_logs')->insert([
                'user_id' => $request->user()->id,
                'action' => strtolower($request->method()),
                'affected_table' => $affectedTable,
                'route_name' => $request->route()?->getName(),
                'occurred_at' => now(),
            ]);
        }

        return $response;
    }
}
