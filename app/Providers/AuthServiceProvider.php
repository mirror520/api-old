<?php

namespace App\Providers;

use App\User;
use Lcobucci\JWT;
use Lcobucci\JWT\Signer\Hmac;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        Auth::viaRequest('api', function ($request) {
            // if ($request->input('api_token')) {
            //     return User::where('api_token', $request->input('api_token'))->first();
            // }

            try {
                $token = $request->bearerToken();
                if ($token) {
                    $singer = new Hmac\Sha256();    // HS256
                    $token = (new JWT\Parser())->parse($token);
    
                    if (($token->getClaim('iss') == 'api.secret.taichung.gov.tw') && 
                        ($token->verify($singer, env('TOKEN_SECRET')))) {
                        $iat = $token->getClaim('iat');
                        $exp = $token->getClaim('exp');
                        $now = time();
    
                        if ($exp - $now > 0) {
                            return true;
                        }
                    }
                }
            } catch (\Exception $e) {
            }
        });
    }
}
