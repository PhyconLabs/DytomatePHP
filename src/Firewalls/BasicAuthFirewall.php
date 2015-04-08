<?php
namespace SDS\Dytomate\Firewalls;

use SDS\Dytomate\Firewall;

class BasicAuthFirewall implements Firewall
{
    protected $username;
    protected $password;

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function isAccessAllowed()
    {
        $username = isset($_SERVER["PHP_AUTH_USER"]) ? $_SERVER["PHP_AUTH_USER"] : "";
        $password = isset($_SERVER["PHP_AUTH_PW"]) ? $_SERVER["PHP_AUTH_PW"] : "";

        return ($username === $this->username && $password === $this->password);
    }
}
