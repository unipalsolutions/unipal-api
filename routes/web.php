<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => ['name' => 'UniPal API', 'version' => 'v1']);
