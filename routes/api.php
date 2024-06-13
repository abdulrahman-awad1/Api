<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\LangController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\User\AuthUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
//  >>>> >>config >> auth   دا معناه انك هتدخل ع , ..
// auth >> authentication معناها ان لازم اكون عامل
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
// api >> دي الميدل وير الديفولت
Route::group(['middleware'=>'api','prefix'=>'post'],function (){
    Route::apiResource('post',PostController::class,);
// x عملنا ميدل وير جديد غير الديفولت
});
Route::group(['middleware'=>['api','ChekUser'],'prefix'=>'post'],function (){
    Route::apiResource('posts',PostController::class);
});
Route::group(['middleware'=>['api','ChekUser','lang'],'prefix'=>'lang'],function (){
    Route::apiResource('language',LangController::class);

});
Route::group(['middleware'=>'api','prefix'=>'admin'],function (){
    Route::post('register_admin',[AuthController::class,'register_admin']);
    Route::post('login_admin',[AuthController::class,'login_admin'])->name('login');
    Route::group(['middleware'=>'auth.guard:admin-api'],function (){
        Route::post('profile',function (){
            return Auth::user();
        });

    });

    Route::post('logout_admin',[AuthController::class,'logout_admin'])->middleware('auth.guard:admin-api');
});

Route::group(['middleware'=>'api','prefix'=>'user', 'namespace'=>'User'],function (){
    Route::post('login',[AuthUserController::class,'login'])->name('login');
    Route::post('logout',[AuthUserController::class,'logout'])->middleware('auth.guard:user-api');
    Route::post('register',[AuthUserController::class,'register']);
    Route::post('facebook_login', [AuthUserController::class,'logFacebook'])->name('logFacebook');
    Route::post('forgotPassword', [AuthUserController::class,'forgotPassword'])->name('forgotPassword');
    Route::post('resetPassword', [AuthUserController::class,'resetPassword'])->name('resetPassword');
    Route::post('changePassword', [AuthUserController::class,'changePassword'])->name('changePassword')->middleware('auth.guard:user-api');
    Route::post('email_verification', [EmailVerificationController::class,'email_verification']);


    Route::group(['middleware'=>'auth.guard:user-api'],function (){
        Route::post('profile',function (){
            return Auth::user();
        });

    });

});

/*Route::group([
    'middleware'=>['admin-api','CheckAdminToken:admin-api'], 'prefix'=>'auth'],function (){
    Route::post('login_admin',[AuthController::class,'login_admin'])->name('login');
    Route::post('logout_admin',[AuthController::class,'logout_admin']);
    Route::post('register_admin',[AuthController::class,'register_admin']);

});*/

Route::group([
    'middleware'=>['user-api',/*'CheckAdminToken:admin-api'*/],
    'prefix'=>'auth'],function (){
    Route::post('login',[AuthUserController::class,'login'])->name('login');
    Route::post('logout',[AuthUserController::class,'logout']);
    Route::post('register',[AuthUserController::class,'register']);
    Route::post('facebook_login', [AuthUserController::class,'logFacebook'])->name('logFacebook');
    Route::post('forgotPassword', [AuthUserController::class,'forgotPassword'])->name('forgotPassword');
    Route::post('resetPassword', [AuthUserController::class,'resetPassword'])->name('resetPassword');
    Route::post('changePassword', [AuthUserController::class,'changePassword'])->name('changePassword');


});
