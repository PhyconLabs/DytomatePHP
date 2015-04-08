<?php
namespace SDS\Dytomate;

use Closure;
use PDO;
use SDS\Dytomate\DefaultDataServices\ArrayDefaultDataService;
use SDS\Dytomate\DummyDataServices\LoremPixelDummyDataService;
use SDS\Dytomate\DummyDataServices\LoripsumDummyDataService;
use SDS\Dytomate\Firewalls\BasicAuthFirewall;
use SDS\Dytomate\Firewalls\ClosureFirewall;
use SDS\Dytomate\Helpers\HtmlTagBuilder;
use SDS\Dytomate\Http\Controller;
use SDS\Dytomate\Http\Router;
use SDS\Dytomate\Repositories\DataRepository;
use SDS\Dytomate\Repositories\MySql\MysqlDataRepository;

class DytomateFactory
{
    protected static $defaultConfiguration = [
        "enableBatching" => true,

        "enableRouting" => true,

        "placeholderTemplate" => "!{dyto{%s}mate}!",

        "attributePlaceholderTemplate" => "!{dyto{%s}...{%s}mate}!",

        "dummyDataServices" => [
            "text" => LoripsumDummyDataService::class,
            "image" => LoremPixelDummyDataService::class
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
            DefaultDataService::class => ArrayDefaultDataService::class,
            Firewall::class => ClosureFirewall::class
        ],

        "http" => [
            "scheme" => Router::WILDCARD,
            "host" => Router::WILDCARD,
            "basePath" => "/",
            "savePath" => "/api/dytomate/save",
            "uploadPath" => "/api/dytomate/upload"
        ],

        "basicAuth" => [
            "enabled" => false,
            "username" => "admin",
            "password" => "secret",
            "path" => "/dytomate/login"
        ],

        "isAccessAllowedCallback" => null,

        "uploadPath" => __DIR__ . "/../../../../public/uploads",

        "uploadUrl" => "/uploads",

        "preSaveCallback" => null,

        "postSaveCallback" => null,

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
            $this->dispatchDefaultDataService(),
            $this->dispatchFirewall()
        );

        $this->configureDytomate($dytomate);

        return $dytomate;
    }

    protected function configureDytomate(Dytomate $dytomate)
    {
        $dytomate->setPlaceholderTemplate($this->configuration["placeholderTemplate"]);
        $dytomate->setAttributePlaceholderTemplate($this->configuration["attributePlaceholderTemplate"]);

        if ($this->configuration["enableRouting"]) {
            $dytomate->setRouter(
                $this->dispatchRouter(
                    $dytomate->getDataRepository(),
                    $dytomate->getFirewall()
                )
            );
        }

        if ($this->configuration["enableBatching"]) {
            $dytomate->startBatching();
        }
    }

    protected function dispatchDytomate(
        HtmlTagBuilder $htmlTagBuilder,
        DummyDataManager $dummyDataManager,
        DataRepository $dataRepository,
        DefaultDataService $defaultDataService,
        Firewall $firewall
    ) {
        return new Dytomate(
            $htmlTagBuilder,
            $dummyDataManager,
            $dataRepository,
            $defaultDataService,
            $firewall
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

    protected function dispatchDefaultDataService()
    {
        $defaultDataService = $this->dispatchClassBinding(
            $this->configuration["classBindings"][DefaultDataService::class]
        );

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

            $defaultDataService->set($key, $defaults["value"], $defaults["attributes"]);
        }

        return $defaultDataService;
    }

    protected function dispatchFirewall()
    {
        if ($this->configuration["basicAuth"]["enabled"]) {
            $this->configuration["classBindings"][Firewall::class] = BasicAuthFirewall::class;
        }

        $binding = $this->configuration["classBindings"][Firewall::class];

        if ($binding === BasicAuthFirewall::class) {
            return new BasicAuthFirewall(
                $this->configuration["basicAuth"]["username"],
                $this->configuration["basicAuth"]["password"]
            );
        } elseif ($binding === ClosureFirewall::class) {
            if (!isset($this->configuration["isAccessAllowedCallback"])) {
                $this->configuration["isAccessAllowedCallback"] = function() {
                    return false;
                };
            }

            return new ClosureFirewall($this->configuration["isAccessAllowedCallback"]);
        }

        return $this->dispatchClassBinding($binding);
    }

    protected function dispatchRouter(DataRepository $dataRepository, Firewall $firewall)
    {
        $httpOptions = $this->configuration["http"];

        if ($firewall instanceof BasicAuthFirewall) {
            $httpOptions = array_merge($httpOptions, [
                "basicAuthPath" => $this->configuration["basicAuth"]["path"]
            ]);
        }

        return new Router(
            $this->dispatchController($dataRepository, $firewall),
            $httpOptions
        );
    }

    protected function dispatchController(DataRepository $dataRepository, Firewall $firewall)
    {
        return new Controller(
            $dataRepository,
            $firewall,
            $this->configuration["uploadPath"],
            $this->configuration["uploadUrl"],
            $this->configuration["preSaveCallback"],
            $this->configuration["postSaveCallback"]
        );
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
