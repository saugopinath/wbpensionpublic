<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ValidateMobileController;
use App\Http\Controllers\PensionFormController;
use App\Http\Controllers\CaptchaController;
Route::get('/captcha/generate', [CaptchaController::class, 'generate']);
Route::post('/sample_encrypt', [ValidateMobileController::class, 'sample_encrypt']);
Route::post('/public/validate/mobile', [ValidateMobileController::class, 'mobilecheck']);
Route::post('/public/validate/checkotp', [ValidateMobileController::class, 'otpcheck']);
Route::post('/public/form/check-existing', [PensionFormController::class, 'checkExistingApplication']);
Route::post('/public/dashboard', [ValidateMobileController::class, 'guestdashboardcheck'])->middleware('GuestDashboardCheck');
Route::post('/public/form/entry/personal', [PensionFormController::class, 'personalEntry']);
Route::post('/public/form/entry/contact', [PensionFormController::class, 'contactEntry']);
Route::post('/public/form/entry/bank', [PensionFormController::class, 'bankEntry']);
Route::post('/public/form/entry/family', [PensionFormController::class, 'familyEntry']);
Route::post('/public/form/entry/declaration', [PensionFormController::class, 'declarationEntry']);
Route::post('/public/form/entry/encloser', [PensionFormController::class, 'encloserEntry']);
Route::post('/public/form/entry/final-submit', [PensionFormController::class, 'finalSubmit']);
