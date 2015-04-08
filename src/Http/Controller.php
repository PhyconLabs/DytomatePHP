<?php
namespace SDS\Dytomate\Http;

use SDS\Dytomate\Entities\Data;
use SDS\Dytomate\Helpers\Image;
use SDS\Dytomate\Repositories\DataRepository;

class Controller
{
    protected $dataRepository;
    protected $uploadPath;
    protected $uploadUrl;

    public function __construct(DataRepository $dataRepository, $uploadPath, $uploadUrl)
    {
        $this->dataRepository = $dataRepository;
        $this->uploadPath = $uploadPath;
        $this->uploadUrl = rtrim($uploadUrl, "/");
    }

    public function save($key, $value, array $attributes = [])
    {
        $data = $this->dataRepository->getOneByKey($key);

        if (!isset($data)) {
            $data = new Data($key, $value, $attributes);
        }

        $data
            ->setValue($value)
            ->setAttributes($attributes);

        $this->dataRepository->save($data);

        // header("Content-Type: application/json");

        echo json_encode([
            "success" => true,
            "value" => $data->getValue(),
            "attributes" => $data->getAttributes()
        ]);
    }

    public function upload($key, $imageName, $imageBlob, array $attributes = [])
    {
        $this->save(
            $key,
            $this->uploadUrl . "/" . (new Image($imageName, $imageBlob))->save($this->uploadPath),
            $attributes
        );
    }
}
