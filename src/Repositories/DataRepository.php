<?php
namespace SDS\Dytomate\Repositories;

use SDS\Dytomate\Entities\Data;

interface DataRepository
{
    public function getOneByKey($key);

    public function getByKeys(array $keys);

    public function save(Data $data);

    public function ensureExists();
}
