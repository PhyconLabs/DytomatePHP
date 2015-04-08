<?php
namespace SDS\Dytomate\Http;

class Router
{
    const WILDCARD = "*";

    protected $controller;

    protected $schemes;

    protected $hosts;

    protected $basePath;

    protected $savePath;

    protected $uploadPath;

    protected $basicAuthPath;

    public function __construct(Controller $controller, array $options = [])
    {
        $this->controller = $controller;
        $this->schemes = isset($options["scheme"]) ? $options["scheme"] : static::WILDCARD;
        $this->hosts = isset($options["host"]) ? $options["host"] : static::WILDCARD;
        $this->basePath = isset($options["basePath"]) ? $options["basePath"] : "/";
        $this->savePath = isset($options["savePath"]) ? $options["savePath"] : "/api/dytoamte/save";
        $this->uploadPath = isset($options["uploadPath"]) ? $options["uploadPath"] : "/api/dytoamte/upload";
        $this->basicAuthPath = isset($options["basicAuthPath"]) ? $options["basicAuthPath"] : null;


        if (!is_array($this->schemes)) {
            $this->schemes = [ $this->schemes ];
        }

        if (!is_array($this->hosts)) {
            $this->hosts = [ $this->hosts ];
        }

        $this->basePath = trim($this->basePath, "/");
    }

    public function route()
    {
        $scheme = (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] === "off") ? "http" : "https";
        $host = !empty($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "";
        $path = !empty($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "/";
        $method = !empty($_SERVER["REQUEST_METHOD"]) ? strtoupper($_SERVER["REQUEST_METHOD"]) : "GET";

        if (!in_array(static::WILDCARD, $this->schemes) && !in_array($scheme, $this->schemes)) {
            return false;
        }

        if (!in_array(static::WILDCARD, $this->hosts) && !in_array($host, $this->hosts)) {
            return false;
        }

        $savePath = (empty($this->basePath) ? "" : "/{$this->basePath}") . $this->savePath;
        $uploadPath = (empty($this->basePath) ? "" : "/{$this->basePath}") . $this->uploadPath;
        $basicAuthPath = null;

        if (isset($this->basicAuthPath)) {
            $basicAuthPath = (empty($this->basePath) ? "" : "/{$this->basePath}") . $this->basicAuthPath;
        }

        if ($method === "POST" && $path === $savePath && isset($_POST["key"], $_POST["value"])) {
            $attributes = isset($_POST["attributes"]) ? $_POST["attributes"] : [];

            $this->controller->save($_POST["key"], $_POST["value"], $attributes);

            return true;
        }

        if ($method === "POST" && $path === $uploadPath && isset($_POST["key"], $_POST["value"]["name"], $_POST["value"]["blob"])) {
            $attributes = isset($_POST["attributes"]) ? $_POST["attributes"] : [];

            $this->controller->upload(
                $_POST["key"],
                $_POST["value"]["name"],
                $_POST["value"]["blob"],
                $attributes
            );

            return true;
        }

        if (isset($basicAuthPath) && $method === "GET" && $path === $basicAuthPath) {
            $this->controller->basicAuth();

            return true;
        }

        return false;
    }
}
