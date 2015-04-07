<?php
namespace SDS\Dytomate\Entities;

class Data
{
    protected $key;
    protected $value;
    protected $attributes;

    public static function createFromJsonEncodedAttributes($key, $value, $attributes)
    {
        $attributes = json_decode($attributes, true);

        if (!is_array($attributes)) {
            $attributes = [];
        }

        return new static($key, $value, $attributes);
    }

    public function __construct($key, $value, array $attributes)
    {
        $this
            ->setKey($key)
            ->setValue($value)
            ->setAttributes($attributes);
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttributesAsJson()
    {
        return json_encode($this->getAttributes());
    }

    public function getAttribute($attribute)
    {
        return $this->hasAttribute($attribute) ? $this->attributes[$attribute] : null;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function hasAttribute($attribute)
    {
        return isset($this->attributes[$attribute]);
    }
}
