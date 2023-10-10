<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\Api;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/', [Api::class, 'index']);
Route::post('/auth/login', [Api::class, 'loginuser']);
Route::post('/auth/register', [Api::class, 'createUser']);
Route::middleware('auth:sanctum')->get('courier/list', [Api::class, 'getCourierList']);
