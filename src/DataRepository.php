<?php
namespace SDS\Dytomate;

interface DataRepository
{
    public function getOneByKey($key);

    public function getByKeys(array $keys);

    public function save(Data $data);
}
