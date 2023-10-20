<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class VerifyRequest
{
    private const TOKEN_HEADER = 'Authorization';

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure(Request): (Response|RedirectResponse) $next
     * @return JsonResponse|RedirectResponse|Response
     */
    public function handle(Request $request, Closure $next): JsonResponse|RedirectResponse|Response
    {
        $vKey = config('mt.verification_key');

        if (
            $request->hasHeader(self::TOKEN_HEADER)
            && $request->header(self::TOKEN_HEADER) === $vKey
        ) {
            return $next($request);
        } else {
            return new JsonResponse(['error' => 'Invalid authorization token'], 403);
        }
    }
}
