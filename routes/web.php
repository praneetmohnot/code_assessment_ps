<?php

use App\Http\Livewire\GeoZones\CreateZone;
use App\Http\Livewire\GeoZones\EditZone;
use App\Http\Livewire\GeoZones\ListZones;
use App\Http\Livewire\GeoZones\ViewZone;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', ListZones::class)->name('geo-zones.index');
Route::redirect('/geo-zones', '/');
Route::get('/geo-zones/create', CreateZone::class)->name('geo-zones.create');
Route::get('/geo-zones/{zone}/edit', EditZone::class)
    ->whereNumber('zone')
    ->name('geo-zones.edit');

Route::get('/geo-zones/{zone}', ViewZone::class)
    ->whereNumber('zone')
    ->name('geo-zones.show');
