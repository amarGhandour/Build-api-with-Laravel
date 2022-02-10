<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class EnsureCorrectAPIHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return BaseResponse
     */
    public function handle(Request $request, Closure $next)
    {

        if ($request->headers->get('Accept') !== 'application/vnd.api+json'){
            return $this->addCorrectContentType(new Response('', 406));
        }

        if ($request->has('Content-Type')||$request->isMethod('POST') || $request->isMethod('PATCH')){
            if ($request->headers->get('Content-Type') !== "application/vnd.api+json") {
                return $this->addCorrectContentType(new Response('', 415));
            }
        }

        return $this->addCorrectContentType($next($request));
    }

    private function addCorrectContentType(BaseResponse $response)
    {
        $response->headers->set('Content-Type', 'application/vnd.api+json');
        return $response;
    }
}
