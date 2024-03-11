<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\WebsiteAnalysisController;

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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::controller(FileController::class)->group(function () {
        Route::prefix('files')->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::post('/', 'store');
        });
    });

    Route::controller(AnalysisController::class)->group(function () {
        Route::prefix('analysis')->group(function () {
            Route::get('/{fileId}', 'analyzeScreenshotByFileId');
            Route::delete('/{id}', 'destroy');
        });
    });

    Route::resource('web', WebsiteAnalysisController::class)->only([
        'index', 'show', 'store'
    ]);
});
