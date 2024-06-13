<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckLoginRequest;
use App\Http\Requests\User\changePasswordeRequest;
use App\Models\Admin;
use App\Models\User;
use App\Notifications\LoginNotification;
use App\Notifications\verificationNotification;
use App\trait\apiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use mysql_xdevapi\Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthUserController extends Controller
{
    use apiResponse;
   /* public function __construct()
    {
        $this->middleware('auth:user-api', ['except' => ['login', 'register']]);
    }*/
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));
        $user->notify(new verificationNotification());
        return $this->returnData('user', $user,'successfully registered');

    }

    public function login(CheckLoginRequest $request)
    {
        try {
            $rules = [
                "email" => "required",
                "password" => "required"

            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $code = $this->returnCodeAccordingToInput($validator);
                return $this->returnValidationError($code, $validator);
            }

            //login

            $credentials = $request->only(['email', 'password']);

            $token = Auth::guard('user-api')->attempt($credentials);  //generate token

            if (!$token)
                return $this->returnError('E001', 'بيانات الدخول غير صحيحة');

            $user = Auth::guard('user-api')->user();
            $user ->api_token = $token;
            $user->notify(new LoginNotification());
            //return token
            return $this->returnData('user', $user,'successfully');  //return json response

        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }
    public function logFacebook(Request $request){
        $token = $request->token;
        $providerUser = Socialite::driver('facebook')->userFromToken($token);
        $userProviderId = $providerUser->id;

        $user = User::where('provider_name','facebook')->where('provider_id',$userProviderId);
        if(!$user){
            $user = User::create([
                'name'=>$providerUser->name,
                'provider_name'=>'facebook',
                'provider_id'=>$userProviderId,
                'avatar'=>"http://graph.facebook.com/v3.3/$userProviderId/picture?type=large&access_token=$token",
            ]);

        }
        $accessToken = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'status'=>'success',
            'access_token'=>$accessToken
        ]);

    }

    public function logout(Request $request){
        $token = $request->header('token');
        if ($token){
            try {
                JWTAuth::setToken($token)->invalidate(); // destroy token
            }catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e){
                return $this->returnError('101','some thing went wrong');

            }

            return $this->successMessage('111','logout');

        }
        else
            return $this->returnError('101','some thing went wrong');
    }
/*auth()->guard('admin-api')->logout();
return response()->json(['message' => ' successfully signed out']);*/


    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Handle invalid email gracefully
            return response()->json(['message' => 'Invalid email'], 400);
        }

        $token = Str::random(60);
        $user->reset_password_token = $token;
        $user->save();

        $resetUrl = config('app.url') . '/api/reset-password/' . $token;

        // Use Laravel's Mail or a third-party service to send the email:
        // ...


        return response()->json(['message' => 'Password reset link sent successfully']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::broker()->resetPassword(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'reset_password_token' => null, // Clear token
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // Send mobile notification using your chosen service (optional)
            // ...

            return response()->json(['message' => 'Password reset successfully'], 200);
        }

        return response()->json(['message' => 'Invalid token or email'], 400);
    }

    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'current_password' => 'required',
            'new_password' => 'required',
        ]);


        try {

            $user = JWTAuth::user();

            // Validate current password
            if (!Hash::check($request->current_password, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['The provided password does not match your current password.'],
                ]);
            }

            // Update the user's password
            $user->password = Hash::make($request->new_password);
            $user->save();


            return response()->json(['message' => 'Password changed successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
