<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('cine:info', function () {
    $this->info('CineApp esta listo para administrar funciones y reservas.');
})->purpose('Mostrar informacion de CineApp');
