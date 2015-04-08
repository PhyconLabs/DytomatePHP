<?php
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

    public function __construct(
        DataRepository $dataRepository,
        Firewall $firewall,
        $uploadPath,
        $uploadUrl,
        Closure $preSaveCallback = null,
        Closure $postSaveCallback = null
    ) {
        $this->dataRepository = $dataRepository;
        $this->firewall = $firewall;
        $this->uploadPath = $uploadPath;
        $this->uploadUrl = rtrim($uploadUrl, "/");
        $this->preSaveCallback = $preSaveCallback;
        $this->postSaveCallback = $postSaveCallback;
    }

    public function save($key, $value, array $attributes = [])
    {
        if (!$this->firewall->isAccessAllowed()) {
            return $this->denyAccess();
        }

        $data = $this->dataRepository->getOneByKey($key);

        if (!isset($data)) {
            $data = new Data($key, $value, $attributes);
        }

        $data
            ->setValue($value)
            ->setAttributes($attributes);

        if ($this->preSaveCallback) {
            $c = $this->preSaveCallback;
            $c($data);
        }

        $this->dataRepository->save($data);

        if ($this->postSaveCallback) {
            $c = $this->postSaveCallback;
            $c($data);
        }

        // header("Content-Type: application/json");

        echo json_encode([
            "success" => true,
            "value" => $data->getValue(),
            "attributes" => $data->getAttributes()
        ]);
    }

    public function upload($key, $imageName, $imageBlob, array $attributes = [])
    {
        if (!$this->firewall->isAccessAllowed()) {
            return $this->denyAccess();
        }

        $this->save(
            $key,
            $this->uploadUrl . "/" . (new Image($imageName, $imageBlob))->save($this->uploadPath),
            $attributes
        );
    }

    public function basicAuth()
    {
        if ($this->firewall->isAccessAllowed()) {
            echo "Access granted.";
        } else {
            header("WWW-Authenticate: Basic realm=\"Dytomate Basic Auth\"");
            header("HTTP/1.0 401 Unauthorized");

            echo "Invalid authentication details.";
        }
    }

    protected function denyAccess()
    {
        http_response_code(403);

        echo json_encode([
            "success" => false,
            "error" => "Access denied."
        ]);
    }
}
