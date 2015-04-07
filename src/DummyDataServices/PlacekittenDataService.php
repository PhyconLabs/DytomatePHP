<?php
namespace SDS\Dytomate\DummyDataServices;

use SDS\Dytomate\DummyDataService;

class PlacekittenDataService implements DummyDataService
{
    public function __construct()
    {
        $this->defaultOptions = [
            "size" => "500",
            "grayscale" => false
        ];
    }

    public function generate(array $options = [])
    {
        if (isset($options["_simpleOptions"])) {
            $options["size"] = $options["_simpleOptions"];

            unset($options["_simpleOptions"]);
        }

        $options = array_merge($this->defaultOptions, $options);
        $width = isset($options["width"]) ? $options["width"] : $options["size"];
        $height = isset($options["height"]) ? $options["height"] : $options["size"];
        $grayscale = $options["grayscale"] ? "/g" : "";

        return "http://placekitten.com{$grayscale}/{$width}/{$height}";
    }

    public function mergeDefaultOptions(array $options)
    {
        $this->defaultOptions = array_merge($this->defaultOptions, $options);

        return $this;
    }
}
