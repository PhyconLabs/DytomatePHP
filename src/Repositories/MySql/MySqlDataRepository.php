<?php
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
        $data = $this->getByKeys([ $key ]);

        return isset($data[0]) ? $data[0] : null;
    }

    public function getByKeys(array $keys)
    {
        $keys = implode(", ", array_map(Closure::bind(function($key) {
            return $this->pdo->quote($key);
        }, $this), $keys));

        // TODO: handle failure by throwing Exception
        $result = $this->pdo->query(
            "SELECT *
            FROM `__dytomate_storage__`
            WHERE `key` IN ( {$keys} )",
            PDO::FETCH_ASSOC
        );

        $result = $result->fetchAll();

        return array_map(function($row) {
            return Data::createFromJsonEncodedAttributes(
                $row["key"],
                $row["value"],
                $row["jsonAttributes"]
            );
        }, $result);
    }

    public function save(Data $data)
    {
        $statement = $this->pdo->prepare(
            "INSERT INTO `__dytomate_storage__` ( `key`, `value`, `jsonAttributes` )
            VALUES (:key, :value, :jsonAttributes)
            ON DUPLICATE KEY UPDATE
                `key` = VALUES(`key`),
                `value` = VALUES(`value`),
                `jsonAttributes` = VALUES(`jsonAttributes`)"
        );

        $statement->execute([
            ":key" => $data->getKey(),
            ":value" => $data->getValue(),
            ":jsonAttributes" => $data->getAttributesAsJson()
        ]);

        // TODO: handle failure by throwing Exception
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
            $result = $this->pdo->query("SELECT 1 FROM `__dytomate_storage__` LIMIT 1");

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
        $sql = "
            CREATE TABLE `__dytomate_storage__`
            (
                `key` varchar(250) NOT NULL,
                `value` text NOT NULL,
                `jsonAttributes` text,
                PRIMARY KEY (`key`)
            )
            ENGINE=MyISAM DEFAULT CHARSET=utf8
        ";

        // TODO: handle failure by throwing Exception
        $this->pdo->query($sql);

        return $this;
    }
}
