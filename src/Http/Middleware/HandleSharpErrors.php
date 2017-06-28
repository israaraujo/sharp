<?php

namespace Code16\Sharp\Http\Middleware;

use Closure;
use Code16\Sharp\Exceptions\Auth\SharpAuthenticationException;
use Code16\Sharp\Exceptions\Auth\SharpAuthorizationException;
use Code16\Sharp\Exceptions\Form\SharpFormException;
use Code16\Sharp\Exceptions\SharpException;

class HandleSharpErrors
{

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $response = $next($request);

        if($response->exception instanceof SharpException) {

            if($request->wantsJson()) {
                return response()->json([
                    "message" => $response->exception->getMessage()
                ], $this->getHttpCodeFor($response->exception));
            }

            if($response->exception instanceof SharpAuthenticationException) {
                // Web redirect to login page
                return redirect()->route("code16.sharp.login");
            }
        }

        return $response;
    }

    private function getHttpCodeFor($exception)
    {
        if ($exception instanceof SharpFormException) {
            // This is an applicative exception, we return it as a 417
            return 417;
        }

        if ($exception instanceof SharpAuthenticationException) {
            return 401;
        }

        if ($exception instanceof SharpAuthorizationException) {
            return 403;
        }

        return 500;
    }
}