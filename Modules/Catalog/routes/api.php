<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Http\Controllers\CategoryController;


Route::apiResource('catalogs', CategoryController::class)->names('catalog');
