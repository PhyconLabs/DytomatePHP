<?php
namespace SDS\Dytomate\Helpers;

class Image
{
    protected $name;
    protected $blob;

    public function __construct($name, $blob)
    {
        $this->name = $name;
        $this->blob = $blob;
    }

    public function save($path)
    {
        $filename = sprintf(
            "%s.%s",
            uniqid(),
            pathinfo($this->name, PATHINFO_EXTENSION)
        );

        $finalPath = sprintf(
            "%s/%s",
            rtrim($path, "/\\"),
            $filename
        );

        file_put_contents($finalPath, base64_decode($this->blob));

        return $filename;
    }
}
