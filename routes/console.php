<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('campo:status', function () {
    $this->info('Campo Aberto Tecnologia Rural - base Laravel operacional.');
})->purpose('Exibe o status básico da aplicação Campo Aberto.');
