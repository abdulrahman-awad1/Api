<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;
use \App\trait\apiResponse;

class AssignGuard

{
    use apiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request , Closure $next , $guard = null): Response
    {
        if ($guard != null) {
            auth()->shouldUse($guard); //shoud you user guard / table
            $token = $request->header('token');
            $request->headers->set('token', (string)$token, true);
            $request->headers->set('Authorization', 'Bearer ' . $token, true);
            try {
                //  $user = $this->auth->authenticate($request);  //check authenticted user
                $user = JWTAuth::parseToken()->authenticate();
            } catch (TokenExpiredException $e) {
                return $this->returnError('401', 'Unauthenticated user');
            } catch (JWTException $e) {

                return $this->returnError('', 'token_invalid' . $e->getMessage());
            }

        }
        return $next($request);
    }
}
