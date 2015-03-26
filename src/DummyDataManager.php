<?php
namespace SDS\Dytomate;

use OutOfBoundsException;

class DummyDataManager
{
    const WILDCARD_TAG = "*";

    protected $services = [];

    protected $tagTypeMap = [];

    public function registerService($type, DummyDataService $service)
    {
        $this->services[$type] = $service;

        return $this;
    }

    public function unregisterService($type)
    {
        unset($this->services[$type]);

        return $this;
    }

    public function bindDefaultTagType($type)
    {
        return $this->bindTypeWithTag($type, static::WILDCARD_TAG);
    }

    public function bindTypeWithTag($type, $tag)
    {
        $this->tagTypeMap[$tag] = $type;

        return $this;
    }

    public function bindTypeWithTags($type, array $tags)
    {
        foreach ($tags as $tag) {
            $this->bindTypeWithTag($type, $tag);
        }

        return $this;
    }

    public function getTypeForTag($tag)
    {
        if (!$this->hasTypeForTag($tag)) {
            throw new OutOfBoundsException(
                "No \$type for `{$tag}` \$tag."
            );
        }

        return isset($this->tagTypeMap[$tag]) ? $this->tagTypeMap[$tag] : $this->tagTypeMap[static::WILDCARD_TAG];
    }

    public function hasService($type)
    {
        return isset($this->services[$type]);
    }

    public function hasTypeForTag($tag, $useDefault = true)
    {
        return (isset($this->tagTypeMap[$tag]) || ($useDefault && isset($this->tagTypeMap[static::WILDCARD_TAG])));
    }
}
