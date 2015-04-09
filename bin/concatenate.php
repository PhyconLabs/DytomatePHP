<?php
namespace SDS\Dytomate;

define("COMPILED_FILE_PATH", realpath(__DIR__ . "/../dist") . "/dytomate.php");
define("CLASS_PRELOADER_PATH", __DIR__ . "/classpreloader.php");

function classToPath($class)
{
    $path = str_replace("\\", "/", substr($class, strlen(__NAMESPACE__) + 1));

    return realpath(__DIR__ . "/../src/{$path}.php");
}

$files = [
    // Interfaces
    classToPath(DefaultDataService::class),
    classToPath(DummyDataService::class),
    classToPath(Firewall::class),
    classToPath(Repositories\DataRepository::class),

    // Entities
    classToPath(Entities\Data::class),

    // DefaultDataServices
    classToPath(DefaultDataServices\ArrayDefaultDataService::class),

    // DummyDataServices
    classToPath(DummyDataServices\LoremPixelDummyDataService::class),
    classToPath(DummyDataServices\LoripsumDummyDataService::class),
    classToPath(DummyDataServices\PlacekittenDummyDataService::class),

    // Firewalls
    classToPath(Firewalls\BasicAuthFirewall::class),
    classToPath(Firewalls\ClosureFirewall::class),

    // Repositories
    classToPath(Repositories\MySql\MySqlDataRepository::class),

    // Helpers
    classToPath(Helpers\HtmlTagBuilder::class),
    classToPath(Helpers\Image::class),

    // Http
    classToPath(Http\Controller::class),
    classToPath(Http\Router::class),

    // DefaultDataManager
    classToPath(DummyDataManager::class),

    // Dytomate
    classToPath(Dytomate::class),

    // DytomateFactory
    classToPath(DytomateFactory::class),
];

$filesFilePath = tempnam(sys_get_temp_dir(), "dytomate");

file_put_contents($filesFilePath, "<?php\nreturn " . var_export($files, true) . ";");

passthru(sprintf(
    "php %s compile --config=\"%s\" --output=\"%s\" --fix_dir=\"0\" --strip_comments=\"1\"",
    CLASS_PRELOADER_PATH,
    $filesFilePath,
    COMPILED_FILE_PATH
));

unlink($filesFilePath);
