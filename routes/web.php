<?php

use App\Http\Controllers\CredentialController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Ruta para mostrar la página de la credencial del usuario
    // Cuando el usuario visite /mi-credencial, se ejecutará el método 'show' de CredentialController
    Route::get('/mi-credencial', [CredentialController::class, 'show'])->name('credential.show');

    // Ruta para generar y mostrar la imagen del código QR del usuario
    // Cuando la etiqueta <img> en la vista de credencial pida esta URL,
    // se ejecutará el método 'show' de QrCodeController
    Route::get('/credential/qr-code', [QrCodeController::class, 'show'])->name('credential.qr.code');

});

require __DIR__.'/auth.php';


