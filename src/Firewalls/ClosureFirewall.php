<?php
namespace SDS\Dytomate\Firewalls;

use Closure;
use SDS\Dytomate\Firewall;

class ClosureFirewall implements Firewall
{
    protected $callback;

    protected $cachedCallbackReturn;

    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    public function isAccessAllowed()
    {
        if (!isset($this->cachedCallbackReturn)) {
            $c = $this->callback;

            $this->cachedCallbackReturn = (bool) $c();
        }

        return $this->cachedCallbackReturn;
    }
}
