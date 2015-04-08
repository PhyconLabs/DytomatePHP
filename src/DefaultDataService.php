<?php
namespace SDS\Dytomate;

interface DefaultDataService
{
    public function get($key);

    public function getAttribute($key, $attribute);

    public function set($key, $value, array $attributes = []);

    public function has($key);

    public function hasAttribute($key, $attribute);
}
