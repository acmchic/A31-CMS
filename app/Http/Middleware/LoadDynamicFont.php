<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\FontService;

class LoadDynamicFont
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $fontCssPath = FontService::getFontCssPath();
            
            // Add dynamic font CSS to the response
            $response = $next($request);
            
            if ($response->headers->get('content-type') && str_contains($response->headers->get('content-type'), 'text/html')) {
                $content = $response->getContent();
                
                // Add font CSS link before closing head tag
                $fontCssLink = '<link rel="stylesheet" href="' . asset($fontCssPath) . '">';
                $content = str_replace('</head>', $fontCssLink . '</head>', $content);
                
                $response->setContent($content);
            }
            
            return $response;
        } catch (\Exception $e) {
            // If there's an error, just continue without dynamic font
            return $next($request);
        }
    }
}
