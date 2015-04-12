<?php
namespace SDS\Dytomate;

interface DefaultDataService
{
    public function get($key);
    public function getAttribute($key, $attribute);
    public function set($key, $value, array $attributes = array());
    public function has($key);
    public function hasAttribute($key, $attribute);
}
namespace SDS\Dytomate;

interface DummyDataService
{
    public function generate(array $options = array());
}
namespace SDS\Dytomate;

interface Firewall
{
    public function isAccessAllowed();
}
namespace SDS\Dytomate\Repositories;

use SDS\Dytomate\Entities\Data;
interface DataRepository
{
    public function getOneByKey($key);
    public function getByKeys(array $keys);
    public function save(Data $data);
    public function ensureExists();
}
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
            $attributes = array();
        }
        return new static($key, $value, $attributes);
    }
    public function __construct($key, $value, array $attributes)
    {
        $this->setKey($key)->setValue($value)->setAttributes($attributes);
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
namespace SDS\Dytomate\DefaultDataServices;

use SDS\Dytomate\DefaultDataService;
class ArrayDefaultDataService implements DefaultDataService
{
    protected $data;
    public function __construct()
    {
        $this->data = array();
    }
    public function get($key)
    {
        return $this->has($key) ? $this->data[$key]['value'] : null;
    }
    public function getAttribute($key, $attribute)
    {
        return $this->hasAttribute($key, $attribute) ? $this->data[$key]['attributes'][$attribute] : null;
    }
    public function set($key, $value, array $attributes = array())
    {
        if (!isset($this->data[$key])) {
            $this->data[$key] = array('value' => null, 'attributes' => array());
        }
        $this->data[$key]['value'] = $value;
        $this->data[$key]['attributes'] = $attributes;
        return $this;
    }
    public function has($key)
    {
        return isset($this->data[$key], $this->data[$key]['value']);
    }
    public function hasAttribute($key, $attribute)
    {
        return isset($this->data[$key], $this->data[$key]['attributes'][$attribute]);
    }
}
namespace SDS\Dytomate\DummyDataServices;

use SDS\Dytomate\DummyDataService;
class LoremPixelDummyDataService implements DummyDataService
{
    public function __construct()
    {
        $this->defaultOptions = array('size' => '500', 'grayscale' => false, 'category' => false, 'number' => false, 'text' => false);
    }
    public function generate(array $options = array())
    {
        if (isset($options['_simpleOptions'])) {
            $options['size'] = $options['_simpleOptions'];
            unset($options['_simpleOptions']);
        }
        $options = array_merge($this->defaultOptions, $options);
        $width = isset($options['width']) ? $options['width'] : $options['size'];
        $height = isset($options['height']) ? $options['height'] : $options['size'];
        $grayscale = $options['grayscale'] ? '/g' : '';
        $category = $options['category'] ? "/{$options['category']}" : '';
        $number = $options['number'] ? "/{$options['number']}" : '';
        $text = $options['text'] ? "/{$options['text']}" : '';
        return "http://lorempixel.com{$grayscale}/{$width}/{$height}{$category}{$number}{$text}";
    }
    public function mergeDefaultOptions(array $options)
    {
        $this->defaultOptions = array_merge($this->defaultOptions, $options);
        return $this;
    }
}
namespace SDS\Dytomate\DummyDataServices;

use SDS\Dytomate\DummyDataService;
class LoripsumDummyDataService implements DummyDataService
{
    public function __construct()
    {
        $this->defaultOptions = array('paragraphs' => 3, 'length' => 'medium', 'decorate' => false, 'link' => false, 'ul' => false, 'ol' => false, 'dl' => false, 'bq' => false, 'code' => false, 'headers' => false, 'allcaps' => false, 'prude' => false, 'plaintext' => false);
    }
    public function generate(array $options = array())
    {
        if (isset($options['_simpleOptions'])) {
            $options['paragraphs'] = $options['_simpleOptions'];
            unset($options['_simpleOptions']);
        }
        $options = array_merge($this->defaultOptions, $options);
        $path = array($options['paragraphs'], $options['length']);
        unset($options['paragraphs'], $options['length']);
        foreach ($options as $option => $value) {
            if ($value) {
                $path[] = $option;
            }
        }
        $url = 'http://loripsum.net/api/' . implode('/', $path);
        return file_get_contents($url);
    }
    public function mergeDefaultOptions(array $options)
    {
        $this->defaultOptions = array_merge($this->defaultOptions, $options);
        return $this;
    }
}
namespace SDS\Dytomate\DummyDataServices;

use SDS\Dytomate\DummyDataService;
class PlacekittenDummyDataService implements DummyDataService
{
    public function __construct()
    {
        $this->defaultOptions = array('size' => '500', 'grayscale' => false);
    }
    public function generate(array $options = array())
    {
        if (isset($options['_simpleOptions'])) {
            $options['size'] = $options['_simpleOptions'];
            unset($options['_simpleOptions']);
        }
        $options = array_merge($this->defaultOptions, $options);
        $width = isset($options['width']) ? $options['width'] : $options['size'];
        $height = isset($options['height']) ? $options['height'] : $options['size'];
        $grayscale = $options['grayscale'] ? '/g' : '';
        return "http://placekitten.com{$grayscale}/{$width}/{$height}";
    }
    public function mergeDefaultOptions(array $options)
    {
        $this->defaultOptions = array_merge($this->defaultOptions, $options);
        return $this;
    }
}
namespace SDS\Dytomate\Firewalls;

use SDS\Dytomate\Firewall;
class BasicAuthFirewall implements Firewall
{
    protected $username;
    protected $password;
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }
    public function isAccessAllowed()
    {
        $username = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
        $password = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
        return $username === $this->username && $password === $this->password;
    }
}
namespace SDS\Dytomate\Firewalls;

use Closure;
use SDS\Dytomate\Firewall;
class ClosureFirewall implements Firewall
{
    protected $callback;
    protected $cachedCallbackReturn;
    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }
    public function isAccessAllowed()
    {
        if (!isset($this->cachedCallbackReturn)) {
            $c = $this->callback;
            $this->cachedCallbackReturn = (bool) $c();
        }
        return $this->cachedCallbackReturn;
    }
}
namespace SDS\Dytomate\Repositories\MySql;

use Closure;
use Exception;
use PDO;
use SDS\Dytomate\Entities\Data;
use SDS\Dytomate\Repositories\DataRepository;
class MySqlDataRepository implements DataRepository
{
    protected $pdo;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ensureExists();
    }
    public function getOneByKey($key)
    {
        $data = $this->getByKeys(array($key));
        return isset($data[0]) ? $data[0] : null;
    }
    public function getByKeys(array $keys)
    {
        $keys = implode(', ', array_map(Closure::bind(function ($key) {
            return $this->pdo->quote($key);
        }, $this), $keys));
        $result = $this->pdo->query("SELECT *\n            FROM `__dytomate_storage__`\n            WHERE `key` IN ( {$keys} )", PDO::FETCH_ASSOC);
        $result = $result->fetchAll();
        return array_map(function ($row) {
            return Data::createFromJsonEncodedAttributes($row['key'], $row['value'], $row['jsonAttributes']);
        }, $result);
    }
    public function save(Data $data)
    {
        $statement = $this->pdo->prepare('INSERT INTO `__dytomate_storage__` ( `key`, `value`, `jsonAttributes` )
            VALUES (:key, :value, :jsonAttributes)
            ON DUPLICATE KEY UPDATE
                `key` = VALUES(`key`),
                `value` = VALUES(`value`),
                `jsonAttributes` = VALUES(`jsonAttributes`)');
        $statement->execute(array(':key' => $data->getKey(), ':value' => $data->getValue(), ':jsonAttributes' => $data->getAttributesAsJson()));
        $statement->execute();
        return $this;
    }
    public function ensureExists()
    {
        if (!$this->tableExists()) {
            $this->createTable();
        }
        return $this;
    }
    protected function tableExists()
    {
        try {
            $result = $this->pdo->query('SELECT 1 FROM `__dytomate_storage__` LIMIT 1');
            if ($result === false) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
    protected function createTable()
    {
        $sql = '
            CREATE TABLE `__dytomate_storage__`
            (
                `key` varchar(250) NOT NULL,
                `value` text NOT NULL,
                `jsonAttributes` text,
                PRIMARY KEY (`key`)
            )
            ENGINE=MyISAM DEFAULT CHARSET=utf8
        ';
        $this->pdo->query($sql);
        return $this;
    }
}
namespace SDS\Dytomate\Helpers;

class HtmlTagBuilder
{
    public function build($tag, $content, array $attributes)
    {
        $html = "<{$tag} {$this->buildAttributeString($attributes)}";
        if ($this->isSelfClosing($tag)) {
            $html .= ' />';
        } else {
            $html .= ">{$content}</{$tag}>";
        }
        return $html;
    }
    protected function isSelfClosing($tag)
    {
        return in_array($tag, array('area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr'));
    }
    protected function buildAttributeString(array $attributes)
    {
        $html = array();
        foreach ($attributes as $name => $value) {
            $value = htmlspecialchars($value, ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
            $html[] = "{$name}=\"{$value}\"";
        }
        return implode(' ', $html);
    }
}
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
        $filename = sprintf('%s.%s', uniqid(), pathinfo($this->name, PATHINFO_EXTENSION));
        $finalPath = sprintf('%s/%s', rtrim($path, '/\\'), $filename);
        file_put_contents($finalPath, base64_decode($this->blob));
        return $filename;
    }
}
namespace SDS\Dytomate\Http;

use Closure;
use SDS\Dytomate\Entities\Data;
use SDS\Dytomate\Firewall;
use SDS\Dytomate\Helpers\Image;
use SDS\Dytomate\Repositories\DataRepository;
class Controller
{
    protected $dataRepository;
    protected $firewall;
    protected $uploadPath;
    protected $uploadUrl;
    protected $preSaveCallback;
    protected $postSaveCallback;
    public function __construct(DataRepository $dataRepository, Firewall $firewall, $uploadPath, $uploadUrl, Closure $preSaveCallback = null, Closure $postSaveCallback = null)
    {
        $this->dataRepository = $dataRepository;
        $this->firewall = $firewall;
        $this->uploadPath = $uploadPath;
        $this->uploadUrl = rtrim($uploadUrl, '/');
        $this->preSaveCallback = $preSaveCallback;
        $this->postSaveCallback = $postSaveCallback;
    }
    public function save($key, $value, array $attributes = array())
    {
        if (!$this->firewall->isAccessAllowed()) {
            return $this->denyAccess();
        }
        $data = $this->dataRepository->getOneByKey($key);
        if (!isset($data)) {
            $data = new Data($key, $value, $attributes);
        }
        $data->setValue($value)->setAttributes($attributes);
        if ($this->preSaveCallback) {
            $c = $this->preSaveCallback;
            $c($data);
        }
        $this->dataRepository->save($data);
        if ($this->postSaveCallback) {
            $c = $this->postSaveCallback;
            $c($data);
        }
        echo json_encode(array('success' => true, 'value' => $data->getValue(), 'attributes' => $data->getAttributes()));
    }
    public function upload($key, $imageName, $imageBlob, array $attributes = array())
    {
        if (!$this->firewall->isAccessAllowed()) {
            return $this->denyAccess();
        }
        $this->save($key, $this->uploadUrl . '/' . (new Image($imageName, $imageBlob))->save($this->uploadPath), $attributes);
    }
    public function basicAuth()
    {
        if ($this->firewall->isAccessAllowed()) {
            echo 'Access granted.';
        } else {
            header('WWW-Authenticate: Basic realm="Dytomate Basic Auth"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Invalid authentication details.';
        }
    }
    protected function denyAccess()
    {
        http_response_code(403);
        echo json_encode(array('success' => false, 'error' => 'Access denied.'));
    }
}
namespace SDS\Dytomate\Http;

class Router
{
    const WILDCARD = '*';
    protected $controller;
    protected $schemes;
    protected $hosts;
    protected $basePath;
    protected $savePath;
    protected $uploadPath;
    protected $basicAuthPath;
    public function __construct(Controller $controller, array $options = array())
    {
        $this->controller = $controller;
        $this->schemes = isset($options['scheme']) ? $options['scheme'] : static::WILDCARD;
        $this->hosts = isset($options['host']) ? $options['host'] : static::WILDCARD;
        $this->basePath = isset($options['basePath']) ? $options['basePath'] : '/';
        $this->savePath = isset($options['savePath']) ? $options['savePath'] : '/api/dytoamte/save';
        $this->uploadPath = isset($options['uploadPath']) ? $options['uploadPath'] : '/api/dytoamte/upload';
        $this->basicAuthPath = isset($options['basicAuthPath']) ? $options['basicAuthPath'] : null;
        if (!is_array($this->schemes)) {
            $this->schemes = array($this->schemes);
        }
        if (!is_array($this->hosts)) {
            $this->hosts = array($this->hosts);
        }
        $this->basePath = trim($this->basePath, '/');
    }
    public function route()
    {
        $scheme = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https';
        $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $path = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        $method = !empty($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
        if (!in_array(static::WILDCARD, $this->schemes) && !in_array($scheme, $this->schemes)) {
            return false;
        }
        if (!in_array(static::WILDCARD, $this->hosts) && !in_array($host, $this->hosts)) {
            return false;
        }
        $savePath = (empty($this->basePath) ? '' : "/{$this->basePath}") . $this->savePath;
        $uploadPath = (empty($this->basePath) ? '' : "/{$this->basePath}") . $this->uploadPath;
        $basicAuthPath = null;
        if (isset($this->basicAuthPath)) {
            $basicAuthPath = (empty($this->basePath) ? '' : "/{$this->basePath}") . $this->basicAuthPath;
        }
        if ($method === 'POST' && $path === $savePath && isset($_POST['key'], $_POST['value'])) {
            $attributes = isset($_POST['attributes']) ? $_POST['attributes'] : array();
            $this->controller->save($_POST['key'], $_POST['value'], $attributes);
            return true;
        }
        if ($method === 'POST' && $path === $uploadPath && isset($_POST['key'], $_POST['value']['name'], $_POST['value']['blob'])) {
            $attributes = isset($_POST['attributes']) ? $_POST['attributes'] : array();
            $this->controller->upload($_POST['key'], $_POST['value']['name'], $_POST['value']['blob'], $attributes);
            return true;
        }
        if (isset($basicAuthPath) && $method === 'GET' && $path === $basicAuthPath) {
            $this->controller->basicAuth();
            return true;
        }
        return false;
    }
}
namespace SDS\Dytomate;

use OutOfBoundsException;
class DummyDataManager
{
    const WILDCARD_TAG = '*';
    protected $services = array();
    protected $tagTypeMap = array();
    public function generate($options)
    {
        $type = is_array($options) && isset($options['type']) ? $options['type'] : null;
        $tag = is_array($options) && isset($options['_tag']) ? $options['_tag'] : null;
        unset($options['type']);
        if (!is_array($options)) {
            $options = array('_simpleOptions' => $options);
        }
        if (!isset($type)) {
            $type = $this->getTypeForTag($tag);
        }
        return $this->getService($type)->generate($options);
    }
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
    public function getService($type)
    {
        if (!$this->hasService($type)) {
            throw new OutOfBoundsException("No DummyDataService registered for `{$type}` \$type.");
        }
        return $this->services[$type];
    }
    public function getTypeForTag($tag)
    {
        if (!$this->hasTypeForTag($tag)) {
            throw new OutOfBoundsException("No \$type for `{$tag}` \$tag.");
        }
        return isset($this->tagTypeMap[$tag]) ? $this->tagTypeMap[$tag] : $this->tagTypeMap[static::WILDCARD_TAG];
    }
    public function hasService($type)
    {
        return isset($this->services[$type]);
    }
    public function hasTypeForTag($tag, $useDefault = true)
    {
        return isset($this->tagTypeMap[$tag]) || $useDefault && isset($this->tagTypeMap[static::WILDCARD_TAG]);
    }
}
namespace SDS\Dytomate;

use InvalidArgumentException;
use SDS\Dytomate\Helpers\HtmlTagBuilder;
use SDS\Dytomate\Http\Router;
use SDS\Dytomate\Repositories\DataRepository;
use SDS\Dytomate\Repositories\MySql\MysqlDataRepository;
class Dytomate
{
    protected $htmlTagBuilder;
    protected $dummyDataManager;
    protected $dataRepository;
    protected $defaultDataService;
    protected $firewall;
    protected $router;
    protected $isBatching = false;
    protected $currentBatch = array();
    protected $placeholderTemplate = '!{dyto{%s}mate}!';
    protected $attributePlaceholderTemplate = '!{dyto{%s}...{%s}mate}!';
    public function __construct(HtmlTagBuilder $htmlTagBuilder, DummyDataManager $dummyDataManager, DataRepository $dataRepository, DefaultDataService $defaultDataService, Firewall $firewall, Router $router = null)
    {
        $this->setHtmlTagBuilder($htmlTagBuilder)->setDummyDataManager($dummyDataManager)->setDataRepository($dataRepository)->setDefaultDataService($defaultDataService)->setFirewall($firewall)->setRouter($router);
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
        $this->currentBatch = array();
        return $this;
    }
    public function replaceCurrentBatch($content, $clearCurrentBatch = true)
    {
        $this->getDataRepository()->getByKeys(array_keys($this->currentBatch));
        $placeholderTemplate = $this->getPlaceholderTemplate();
        $attributePlaceholderTemplate = $this->getAttributePlaceholderTemplate();
        foreach ($this->currentBatch as $key => $requested) {
            if (array_key_exists('content', $requested)) {
                $content = str_replace(sprintf($placeholderTemplate, $key), $this->getValue($key, $requested['content']), $content);
            }
            if (isset($requested['attributes'])) {
                foreach ($requested['attributes'] as $attribute => $dummyDataOptions) {
                    $content = str_replace(sprintf($attributePlaceholderTemplate, $key, $attribute), $this->getAttributeValue($key, $attribute, $dummyDataOptions), $content);
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
        $defaultDataService = $this->getDefaultDataService();
        if ($defaultDataService->has($key)) {
            return $defaultDataService->get($key);
        }
        if (isset($dummyDataOptions)) {
            if (is_string($dummyDataOptions)) {
                return $dummyDataOptions;
            }
            return $this->getDummyDataManager()->generate($dummyDataOptions);
        }
        return '';
    }
    public function getPlaceholder($key, $dummyDataOptions = null)
    {
        if (!isset($this->currentBatch[$key])) {
            $this->currentBatch[$key] = array();
        }
        $this->currentBatch[$key]['content'] = $dummyDataOptions;
        return sprintf($this->getPlaceholderTemplate(), $key);
    }
    public function getTag($key, $tag, array $attributes = array(), $dummyDataOptions = null)
    {
        if ($this->getFirewall()->isAccessAllowed()) {
            $attributes['data-dytomate'] = $key;
        }
        if (isset($dummyDataOptions) && !is_string($dummyDataOptions) && (!is_array($dummyDataOptions) || !isset($dummyDataOptions['type']))) {
            if (!is_array($dummyDataOptions)) {
                $dummyDataOptions = array('_simpleOptions' => $dummyDataOptions);
            }
            $dummyDataOptions['_tag'] = $tag;
        }
        if ($tag === 'img') {
            $content = '';
            $attributes['src'] = $this->get($key, $dummyDataOptions);
        } elseif ($tag === 'a') {
            $content = $this->get($key, $dummyDataOptions);
            $attributes['href'] = $this->getAttribute($key, 'href');
            $attributes['title'] = $this->getAttribute($key, 'title');
        } else {
            $content = $this->get($key, $dummyDataOptions);
        }
        return $this->getHtmlTagBuilder()->build($tag, $content, $attributes);
    }
    public function getReadOnlyTag($key, $tag, array $attributes = array(), $dummyDataOptions = null)
    {
        if ($this->getFirewall()->isAccessAllowed()) {
            $attributes['data-dytomate-ro'] = 'true';
        }
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
        $defaultDataService = $this->getDefaultDataService();
        if ($defaultDataService->hasAttribute($key, $attribute)) {
            return $defaultDataService->getAttribute($key, $attribute);
        }
        if (isset($dummyDataOptions)) {
            return $this->getDummyDataManager()->generate($dummyDataOptions);
        }
        return '';
    }
    public function getAttributePlaceholder($key, $attribute, $dummyDataOptions = null)
    {
        if (!isset($this->currentBatch[$key])) {
            $this->currentBatch[$key] = array();
        }
        if (!isset($this->currentBatch[$key]['attributes'])) {
            $this->currentBatch[$key]['attributes'] = array();
        }
        $this->currentBatch[$key]['attributes'][$attribute] = $dummyDataOptions;
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
    public function getDefaultDataService()
    {
        return $this->defaultData;
    }
    public function setDefaultDataService(DefaultDataService $defaultDataService)
    {
        $this->defaultData = $defaultDataService;
        return $this;
    }
    public function getFirewall()
    {
        return $this->firewall;
    }
    public function setFirewall(Firewall $firewall)
    {
        $this->firewall = $firewall;
        return $this;
    }
    public function getRouter()
    {
        return $this->router;
    }
    public function setRouter(Router $router = null)
    {
        $this->router = $router;
        return $this;
    }
    public function getPlaceholderTemplate()
    {
        return $this->placeholderTemplate;
    }
    public function setPlaceholderTemplate($placeholderTemplate)
    {
        if (!is_string($placeholderTemplate)) {
            throw new InvalidArgumentException('$placeholderTemplate must be a string.');
        }
        if (substr_count($placeholderTemplate, '%s') !== 1) {
            throw new InvalidArgumentException('$placeholderTemplate must contain one %s placeholder.');
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
        if (!is_string($attributePlaceholderTemplate)) {
            throw new InvalidArgumentException('$attributePlaceholderTemplate must be a string.');
        }
        if (substr_count($attributePlaceholderTemplate, '%s') !== 2) {
            throw new InvalidArgumentException('$attributePlaceholderTemplate must contain two %s placeholders.');
        }
        $this->attributePlaceholderTemplate = $attributePlaceholderTemplate;
        return $this;
    }
    public function isBatching()
    {
        return $this->isBatching;
    }
}
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
    protected static $defaultConfiguration = array('enableBatching' => false, 'enableRouting' => true, 'placeholderTemplate' => '!{dyto{%s}mate}!', 'attributePlaceholderTemplate' => '!{dyto{%s}...{%s}mate}!', 'dummyDataServices' => array('text' => LoripsumDummyDataService::class, 'image' => LoremPixelDummyDataService::class), 'dummyDataTagMap' => array('text' => DummyDataManager::WILDCARD_TAG, 'image' => 'img'), 'pdo' => array('dsn' => 'mysql:host=localhost;port=3306;dbname=dytomate', 'user' => 'root', 'password' => '', 'driverOptions' => array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8')), 'classBindings' => array(DataRepository::class => MysqlDataRepository::class, DefaultDataService::class => ArrayDefaultDataService::class, Firewall::class => ClosureFirewall::class), 'http' => array('scheme' => Router::WILDCARD, 'host' => Router::WILDCARD, 'basePath' => '/', 'savePath' => '/api/dytomate/save', 'uploadPath' => '/api/dytomate/upload'), 'basicAuth' => array('enabled' => false, 'username' => 'admin', 'password' => 'secret', 'path' => '/dytomate/login'), 'isAccessAllowedCallback' => null, 'uploadPath' => __DIR__ . '/../../../../public/uploads', 'uploadUrl' => '/uploads', 'preSaveCallback' => null, 'postSaveCallback' => null, 'defaultData' => array());
    protected $configuration;
    public static function setDefaultConfiguration(array $configuration)
    {
        static::$defaultConfiguration = array_replace_recursive(static::$defaultConfiguration, $configuration);
    }
    public static function makeDefault()
    {
        return (new static())->make();
    }
    public function __construct(array $configuration = array())
    {
        $this->configuration = array_replace_recursive(static::$defaultConfiguration, $configuration);
    }
    public function make()
    {
        $dytomate = $this->dispatchDytomate($this->dispatchHtmlTagBuilder(), $this->dispatchDummyDataManager(), $this->dispatchDataRepository(), $this->dispatchDefaultDataService(), $this->dispatchFirewall());
        $this->configureDytomate($dytomate);
        return $dytomate;
    }
    protected function configureDytomate(Dytomate $dytomate)
    {
        $dytomate->setPlaceholderTemplate($this->configuration['placeholderTemplate']);
        $dytomate->setAttributePlaceholderTemplate($this->configuration['attributePlaceholderTemplate']);
        if ($this->configuration['enableRouting']) {
            $dytomate->setRouter($this->dispatchRouter($dytomate->getDataRepository(), $dytomate->getFirewall()));
        }
        if ($this->configuration['enableBatching']) {
            $dytomate->startBatching();
        }
    }
    protected function dispatchDytomate(HtmlTagBuilder $htmlTagBuilder, DummyDataManager $dummyDataManager, DataRepository $dataRepository, DefaultDataService $defaultDataService, Firewall $firewall)
    {
        return new Dytomate($htmlTagBuilder, $dummyDataManager, $dataRepository, $defaultDataService, $firewall);
    }
    protected function dispatchHtmlTagBuilder()
    {
        return new HtmlTagBuilder();
    }
    protected function dispatchDummyDataManager()
    {
        $manager = new DummyDataManager();
        foreach ($this->configuration['dummyDataServices'] as $type => $binding) {
            $manager->registerService($type, $this->dispatchClassBinding($binding));
        }
        foreach ($this->configuration['dummyDataTagMap'] as $type => $tag) {
            $manager->bindTypeWithTag($type, $tag);
        }
        return $manager;
    }
    protected function dispatchDataRepository()
    {
        $binding = $this->configuration['classBindings'][DataRepository::class];
        if ($binding === MysqlDataRepository::class) {
            $binding = new MysqlDataRepository($this->dispatchPdo());
        }
        return $this->dispatchClassBinding($binding);
    }
    protected function dispatchDefaultDataService()
    {
        $defaultDataService = $this->dispatchClassBinding($this->configuration['classBindings'][DefaultDataService::class]);
        foreach ($this->configuration['defaultData'] as $key => $defaults) {
            if (!is_array($defaults)) {
                $defaults = array('value' => $defaults, 'attributes' => array());
            }
            if (!isset($defaults['value'])) {
                $defaults = array('value' => null, 'attributes' => $defaults);
            }
            if (isset($defaults['value']) && !isset($defaults['attributes']) && count($defaults) > 1) {
                $defaults['attributes'] = $defaults;
                unset($defaults['attributes']['value']);
            }
            $defaultDataService->set($key, $defaults['value'], $defaults['attributes']);
        }
        return $defaultDataService;
    }
    protected function dispatchFirewall()
    {
        if ($this->configuration['basicAuth']['enabled']) {
            $this->configuration['classBindings'][Firewall::class] = BasicAuthFirewall::class;
        }
        $binding = $this->configuration['classBindings'][Firewall::class];
        if ($binding === BasicAuthFirewall::class) {
            return new BasicAuthFirewall($this->configuration['basicAuth']['username'], $this->configuration['basicAuth']['password']);
        } elseif ($binding === ClosureFirewall::class) {
            if (!isset($this->configuration['isAccessAllowedCallback'])) {
                $this->configuration['isAccessAllowedCallback'] = function () {
                    return false;
                };
            }
            return new ClosureFirewall($this->configuration['isAccessAllowedCallback']);
        }
        return $this->dispatchClassBinding($binding);
    }
    protected function dispatchRouter(DataRepository $dataRepository, Firewall $firewall)
    {
        $httpOptions = $this->configuration['http'];
        if ($firewall instanceof BasicAuthFirewall) {
            $httpOptions = array_merge($httpOptions, array('basicAuthPath' => $this->configuration['basicAuth']['path']));
        }
        return new Router($this->dispatchController($dataRepository, $firewall), $httpOptions);
    }
    protected function dispatchController(DataRepository $dataRepository, Firewall $firewall)
    {
        return new Controller($dataRepository, $firewall, $this->configuration['uploadPath'], $this->configuration['uploadUrl'], $this->configuration['preSaveCallback'], $this->configuration['postSaveCallback']);
    }
    protected function dispatchPdo()
    {
        return new PDO($this->configuration['pdo']['dsn'], $this->configuration['pdo']['user'], $this->configuration['pdo']['password'], $this->configuration['pdo']['driverOptions']);
    }
    protected function dispatchClassBinding($binding)
    {
        if (is_object($binding) && !$binding instanceof Closure) {
            return $binding;
        }
        if (is_object($binding) && $binding instanceof Closure) {
            return $binding();
        }
        return new $binding();
    }
}
