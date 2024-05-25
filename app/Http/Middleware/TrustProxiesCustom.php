<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
//use Fideloper\Proxy\TrustProxies as Middleware;
use App\Http\Middleware\TrustProxies as Middleware
class TrustProxiesCustom extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;

    /**
     * Create a new trusted proxies middleware instance.
     *
     * @param  array|string|null  $proxies
     * @return void
     */
    public function __construct($proxies = null)
    {
        parent::__construct($proxies, $this->headers);
    }
}
