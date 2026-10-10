<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies;

/**
 * Laravel's TrustProxies, configured from tasyiir.trusted_proxies at request
 * time (bootstrap/app.php runs before .env is loaded, so env() cannot be read
 * there). Needed behind Cloudflare Tunnel: without it every link is http://
 * on an https page and every visitor shares the tunnel's 127.0.0.1 address.
 */
class TrustProxiesFromConfig extends TrustProxies
{
    protected function proxies()
    {
        $configured = config('tasyiir.trusted_proxies');

        if (is_string($configured) && trim($configured) !== '') {
            $configured = trim($configured);

            return $configured === '*' ? '*' : array_map('trim', explode(',', $configured));
        }

        return parent::proxies();
    }
}
