<?php
namespace SDS\Dytomate;

use Closure;
use PDO;
use SDS\Dytomate\DummyDataServices\LoripsumDataService;
use SDS\Dytomate\DummyDataServices\PlacekittenDataService;
use SDS\Dytomate\Repositories\DataRepository;
use SDS\Dytomate\Repositories\MySql\MysqlDataRepository;

class DytomateFactory
{
    protected static $defaultConfiguration = [
        "enableBatching" => true,
        "placeholderTemplate" => "!{dyto{%s}mate}!",
        "attributePlaceholderTemplate" => "!{dyto{%s}...{%s}mate}!",
        "dummyDataServices" => [
            "text" => LoripsumDataService::class,
            "image" => PlacekittenDataService::class
        ],
        "dummyDataTagMap" => [
            "text" => DummyDataManager::WILDCARD_TAG,
            "image" => "img"
        ],
        "pdo" => [
            "dsn" => "mysql:host=localhost;port=3306;dbname=dytomate",
            "user" => "root",
            "password" => "",
            "driverOptions" => [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ]
        ],
        "classBindings" => [
            DataRepository::class => MysqlDataRepository::class,
            DefaultData::class => ArrayDefaultData::class
        ],
        "defaultData" => []
    ];

    protected $configuration;

    public static function setDefaultConfiguration(array $configuration)
    {
        static::$defaultConfiguration = array_replace_recursive(static::$defaultConfiguration, $configuration);
    }

    public static function makeDefault()
    {
        return (new static())->make();
    }

    public function __construct(array $configuration = [])
    {
        $this->configuration = array_replace_recursive(static::$defaultConfiguration, $configuration);
    }

    public function make()
    {
        $dytomate = $this->dispatchDytomate(
            $this->dispatchHtmlTagBuilder(),
            $this->dispatchDummyDataManager(),
            $this->dispatchDataRepository(),
            $this->dispatchDefaultData()
        );

        $this->configureDytomate($dytomate);

        return $dytomate;
    }

    protected function configureDytomate(Dytomate $dytomate)
    {
        $dytomate->setPlaceholderTemplate($this->configuration["placeholderTemplate"]);
        $dytomate->setAttributePlaceholderTemplate($this->configuration["attributePlaceholderTemplate"]);

        if ($this->configuration["enableBatching"]) {
            $dytomate->startBatching();
        }
    }

    protected function dispatchDytomate(
        HtmlTagBuilder $htmlTagBuilder,
        DummyDataManager $dummyDataManager,
        DataRepository $dataRepository,
        DefaultData $defaultData
    ) {
        return new Dytomate(
            $htmlTagBuilder,
            $dummyDataManager,
            $dataRepository,
            $defaultData
        );
    }

    protected function dispatchHtmlTagBuilder()
    {
        return new HtmlTagBuilder();
    }

    protected function dispatchDummyDataManager()
    {
        $manager = new DummyDataManager();

        foreach ($this->configuration["dummyDataServices"] as $type => $binding) {
            $manager->registerService($type, $this->dispatchClassBinding($binding));
        }

        foreach ($this->configuration["dummyDataTagMap"] as $type => $tag) {
            $manager->bindTypeWithTag($type, $tag);
        }

        return $manager;
    }

    protected function dispatchDataRepository()
    {
        $binding = $this->configuration["classBindings"][DataRepository::class];

        if ($binding === MysqlDataRepository::class) {
            $binding = new MysqlDataRepository($this->dispatchPdo());
        }

        return $this->dispatchClassBinding($binding);
    }

    protected function dispatchDefaultData()
    {
        $defaultData = $this->dispatchClassBinding($this->configuration["classBindings"][DefaultData::class]);

        foreach ($this->configuration["defaultData"] as $key => $defaults) {
            if (!is_array($defaults)) {
                $defaults = [ "value" => $defaults, "attributes" => [] ];
            }

            if (!isset($defaults["value"])) {
                $defaults = [ "value" => null, "attributes" => $defaults ];
            }

            if (isset($defaults["value"]) && !isset($defaults["attributes"]) && count($defaults) > 1) {
                $defaults["attributes"] = $defaults;

                unset($defaults["attributes"]["value"]);
            }

            $defaultData->set($key, $defaults["value"], $defaults["attributes"]);
        }

        return $defaultData;
    }

    protected function dispatchPdo()
    {
        return new PDO(
            $this->configuration["pdo"]["dsn"],
            $this->configuration["pdo"]["user"],
            $this->configuration["pdo"]["password"],
            $this->configuration["pdo"]["driverOptions"]
        );
    }

    protected function dispatchClassBinding($binding)
    {
        if (is_object($binding) && !$binding instanceof Closure) {
            return $binding;
        }

        if (is_object($binding) && $binding instanceof Closure) {
            return $binding();
        }

        return new $binding;
    }
}
