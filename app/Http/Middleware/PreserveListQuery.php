<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class PreserveListQuery
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethodSafe() || ! $response instanceof RedirectResponse) {
            return $response;
        }

        $referer = $request->headers->get('referer');
        if (! $referer) {
            return $response;
        }

        $source = Request::create($referer);
        $targetUrl = $response->getTargetUrl();
        $target = Request::create(url($targetUrl));

        // Only restore filters when returning to the same local page.
        if ($source->getSchemeAndHttpHost() !== $request->getSchemeAndHttpHost()
            || $target->getSchemeAndHttpHost() !== $request->getSchemeAndHttpHost()
            || $source->getPathInfo() !== $target->getPathInfo()
            || ! $source->query->count()) {
            return $response;
        }

        $query = http_build_query(array_replace($source->query->all(), $target->query->all()), '', '&', PHP_QUERY_RFC3986);
        $fragment = parse_url($targetUrl, PHP_URL_FRAGMENT);
        $base = preg_split('/[?#]/', $targetUrl, 2)[0];
        $response->setTargetUrl($base.'?'.$query.($fragment !== null ? '#'.$fragment : ''));

        return $response;
    }
}
