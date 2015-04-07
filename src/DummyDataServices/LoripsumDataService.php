<?php
namespace SDS\Dytomate\DummyDataServices;

use SDS\Dytomate\DummyDataService;

class LoripsumDataService implements DummyDataService
{
    public function __construct()
    {
        $this->defaultOptions = [
            "paragraphs" => 3,
            "length" => "medium",
            "decorate" => false,
            "link" => false,
            "ul" => false,
            "ol" => false,
            "dl" => false,
            "bq" => false,
            "code" => false,
            "headers" => false,
            "allcaps" => false,
            "prude" => false,
            "plaintext" => false
        ];
    }

    public function generate(array $options = [])
    {
        if (isset($options["_simpleOptions"])) {
            $options["paragraphs"] = $options["_simpleOptions"];

            unset($options["_simpleOptions"]);
        }

        $options = array_merge($this->defaultOptions, $options);
        $path = [ $options["paragraphs"], $options["length"] ];

        unset($options["paragraphs"], $options["length"]);

        foreach ($options as $option => $value) {
            if ($value) {
                $path[] = $option;
            }
        }

        $url = "http://loripsum.net/api/" . implode("/", $path);

        return file_get_contents($url);
    }

    public function mergeDefaultOptions(array $options)
    {
        $this->defaultOptions = array_merge($this->defaultOptions, $options);

        return $this;
    }
}
