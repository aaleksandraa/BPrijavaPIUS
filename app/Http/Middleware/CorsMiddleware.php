<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', $this->getAllowedOrigin($request))
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type, Origin, X-Requested-With')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        $response = $next($request);

        // Add CORS headers to actual request
        return $response
            ->header('Access-Control-Allow-Origin', $this->getAllowedOrigin($request))
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type, Origin, X-Requested-With')
            ->header('Access-Control-Allow-Credentials', 'true');
    }

    /**
     * Get the allowed origin based on the request origin.
     */
    private function getAllowedOrigin(Request $request): string
    {
        $origin = $request->header('Origin');

        $allowedOrigins = [
            'https://prijava.pius-academy.com',
            'http://prijava.pius-academy.com',
            'http://localhost:5173',
            'http://localhost:3000',
        ];

        if (in_array($origin, $allowedOrigins)) {
            return $origin;
        }

        // Default to frontend URL from env
        return config('app.frontend_url', 'https://prijava.pius-academy.com');
    }
}
