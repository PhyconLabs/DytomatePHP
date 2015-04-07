<?php
namespace SDS\Dytomate;

use InvalidArgumentException;
use SDS\Dytomate\Repositories\DataRepository;
use SDS\Dytomate\Repositories\MySql\MysqlDataRepository;

class Dytomate
{
    protected $htmlTagBuilder;

    protected $dummyDataManager;

    protected $dataRepository;

    protected $defaultData;

    protected $pdo;

    protected $isBatching = false;

    protected $currentBatch = [];

    protected $placeholderTemplate = "!{dyto{%s}mate}!";

    protected $attributePlaceholderTemplate = "!{dyto{%s}...{%s}mate}!";

    public function __construct(
        HtmlTagBuilder $htmlTagBuilder,
        DummyDataManager $dummyDataManager,
        DataRepository $dataRepository,
        DefaultData $defaultData
    ) {
        $this
            ->setHtmlTagBuilder($htmlTagBuilder)
            ->setDummyDataManager($dummyDataManager)
            ->setDataRepository($dataRepository)
            ->setDefaultData($defaultData);
    }

    public function startBatching()
    {
        $this->isBatching = true;

        return $this;
    }

    public function stopBatching()
    {
        $this->isBatching = false;

        return $this;
    }

    public function clearCurrentBatch()
    {
        $this->currentBatch = [];

        return $this;
    }

    public function replaceCurrentBatch($content, $clearCurrentBatch = true)
    {
        $this->getDataRepository()->getByKeys(array_keys($this->currentBatch));

        $placeholderTemplate = $this->getPlaceholderTemplate();
        $attributePlaceholderTemplate = $this->getAttributePlaceholderTemplate();

        foreach ($this->currentBatch as $key => $requested) {
            if (array_key_exists("content", $requested)) {
                $content = str_replace(
                    sprintf($placeholderTemplate, $key),
                    $this->getValue($key, $requested["content"]),
                    $content
                );
            }

            if (isset($requested["attributes"])) {
                foreach ($requested["attributes"] as $attribute => $dummyDataOptions) {
                    $content = str_replace(
                        sprintf($attributePlaceholderTemplate, $key, $attribute),
                        $this->getAttributeValue($key, $attribute, $dummyDataOptions),
                        $content
                    );
                }
            }
        }

        if ($clearCurrentBatch) {
            $this->clearCurrentBatch();
        }

        return $content;
    }

    public function get($key, $dummyDataOptions = null)
    {
        if ($this->isBatching()) {
            return $this->getPlaceholder($key, $dummyDataOptions);
        } else {
            return $this->getValue($key, $dummyDataOptions);
        }
    }

    public function getValue($key, $dummyDataOptions = null)
    {
        $data = $this->getDataRepository()->getOneByKey($key);

        if (isset($data)) {
            return $data->getValue();
        }

        $defaultData = $this->getDefaultData();

        if ($defaultData->has($key)) {
            return $defaultData->get($key);
        }

        if (isset($dummyDataOptions)) {
            return $this->getDummyDataManager()->generate($dummyDataOptions);
        }

        return "";
    }

    public function getPlaceholder($key, $dummyDataOptions = null)
    {
        if (!isset($this->currentBatch[$key])) {
            $this->currentBatch[$key] = [];
        }

        $this->currentBatch[$key]["content"] = $dummyDataOptions;

        return sprintf($this->getPlaceholderTemplate(), $key);
    }

    public function getTag($key, $tag, array $attributes = [], $dummyDataOptions = null)
    {
        $attributes["data-dytomate"] = $key;

        if (isset($dummyDataOptions) && (!is_array($dummyDataOptions) || !isset($dummyDataOptions["type"]))) {
            if (!is_array($dummyDataOptions)) {
                $dummyDataOptions = [ "_simpleOptions" => $dummyDataOptions ];
            }

            $dummyDataOptions["_tag"] = $tag;
        }

        if ($tag === "img") {
            $content = "";
            $attributes["src"] = $this->get($key, $dummyDataOptions);
        } elseif ($tag === "a") {
            $content = $this->get($key, $dummyDataOptions);
            $attributes["href"] = $this->getAttribute($key, "href");
            $attributes["title"] = $this->getAttribute($key, "title");
        } else {
            $content = $this->get($key, $dummyDataOptions);
        }

        return $this->getHtmlTagBuilder()->build($tag, $content, $attributes);
    }

    public function getReadOnlyTag($key, $tag, array $attributes = [], $dummyDataOptions = null)
    {
        $attributes["data-dytomate-ro"] = "true";

        return $this->getTag($key, $tag, $attributes);
    }

    public function getAttribute($key, $attribute, $dummyDataOptions = null)
    {
        if ($this->isBatching()) {
            return $this->getAttributePlaceholder($key, $attribute, $dummyDataOptions);
        } else {
            return $this->getAttributeValue($key, $attribute, $dummyDataOptions);
        }
    }

    public function getAttributeValue($key, $attribute, $dummyDataOptions = null)
    {
        $data = $this->getDataRepository()->getOneByKey($key);

        if (isset($data) && $data->hasAttribute($attribute)) {
            return $data->getAttribute($attribute);
        }

        $defaultData = $this->getDefaultData();

        if ($defaultData->hasAttribute($key, $attribute)) {
            return $defaultData->getAttribute($key, $attribute);
        }

        if (isset($dummyDataOptions)) {
            return $this->getDummyDataManager()->generate($dummyDataOptions);
        }

        return "";
    }

    public function getAttributePlaceholder($key, $attribute, $dummyDataOptions = null)
    {
        if (!isset($this->currentBatch[$key])) {
            $this->currentBatch[$key] = [];
        }

        if (!isset($this->currentBatch[$key]["attributes"])) {
            $this->currentBatch[$key]["attributes"] = [];
        }

        $this->currentBatch[$key]["attributes"][$attribute] = $dummyDataOptions;

        return sprintf($this->getAttributePlaceholderTemplate(), $key, $attribute);
    }

    public function getHtmlTagBuilder()
    {
        return $this->htmlTagBuilder;
    }

    public function setHtmlTagBuilder(HtmlTagBuilder $htmlTagBuilder)
    {
        $this->htmlTagBuilder = $htmlTagBuilder;

        return $this;
    }

    public function getDummyDataManager()
    {
        return $this->dummyDataManager;
    }

    public function setDummyDataManager(DummyDataManager $dummyDataManager)
    {
        $this->dummyDataManager = $dummyDataManager;

        return $this;
    }

    public function getDataRepository()
    {
        return $this->dataRepository;
    }

    public function setDataRepository(DataRepository $dataRepository)
    {
        $this->dataRepository = $dataRepository;

        return $this;
    }

    public function getDefaultData()
    {
        return $this->defaultData;
    }

    public function setDefaultData(DefaultData $defaultData)
    {
        $this->defaultData = $defaultData;

        return $this;
    }

    public function getPlaceholderTemplate()
    {
        return $this->placeholderTemplate;
    }

    public function setPlaceholderTemplate($placeholderTemplate)
    {
        // TODO: handle object with __toString() method
        if (!is_string($placeholderTemplate)) {
            throw new InvalidArgumentException(
                "\$placeholderTemplate must be a string."
            );
        }

        if (substr_count($placeholderTemplate, "%s") !== 1) {
            throw new InvalidArgumentException(
                "\$placeholderTemplate must contain one %s placeholder."
            );
        }

        $this->placeholderTemplate = $placeholderTemplate;

        return $this;
    }

    public function getAttributePlaceholderTemplate()
    {
        return $this->attributePlaceholderTemplate;
    }

    public function setAttributePlaceholderTemplate($attributePlaceholderTemplate)
    {
        // TODO: handle object with __toString() method
        if (!is_string($attributePlaceholderTemplate)) {
            throw new InvalidArgumentException(
                "\$attributePlaceholderTemplate must be a string."
            );
        }

        if (substr_count($attributePlaceholderTemplate, "%s") !== 2) {
            throw new InvalidArgumentException(
                "\$attributePlaceholderTemplate must contain two %s placeholders."
            );
        }

        $this->attributePlaceholderTemplate = $attributePlaceholderTemplate;

        return $this;
    }

    public function isBatching()
    {
        return $this->isBatching;
    }
}
