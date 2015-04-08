<?php
namespace SDS\Dytomate\DefaultDataServices;

use SDS\Dytomate\DefaultDataService;

class ArrayDefaultDataService implements DefaultDataService
{
    protected $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function get($key)
    {
        return $this->has($key) ? $this->data[$key]["value"] : null;
    }

    public function getAttribute($key, $attribute)
    {
        return $this->hasAttribute($key, $attribute) ? $this->data[$key]["attributes"][$attribute] : null;
    }

    public function set($key, $value, array $attributes = [])
    {
        if (!isset($this->data[$key])) {
            $this->data[$key] = [ "value" => null, "attributes" => [] ];
        }

        $this->data[$key]["value"] = $value;
        $this->data[$key]["attributes"] = $attributes;

        return $this;
    }

    public function has($key)
    {
        return isset($this->data[$key], $this->data[$key]["value"]);
    }

    public function hasAttribute($key, $attribute)
    {
        return isset($this->data[$key], $this->data[$key]["attributes"][$attribute]);
    }
}
