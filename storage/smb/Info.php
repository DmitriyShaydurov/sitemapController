<?php
namespace shaydurov\opencart;

trait Info
{
    protected $db;
    protected $tableName = DB_PREFIX . '_shd_info';

    protected function connect()
    {
        $this->db = new Db();
        $isExist = $this->db->fetchAll("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '" . $this->tableName . "'");
        if (!$isExist) {
            $this->db->query("CREATE TABLE " . $this->tableName . "(key_id VARCHAR(255), value TEXT, PRIMARY KEY(key_id));",[]);
        }
    }

    protected function keyExists($key)
    {
        return $this->db->fetchSingle("SELECT value FROM " . $this->tableName . " WHERE key_id = :key_id", ['key_id' => $key]);
    }

    protected function set($key,$value)
    {
        $this->connect();
        $values = ['key_id' => $key, 'value' => json_encode($value)];

        if ($this->keyExists($key)) {
            $this->delete($key);
        }
            $this->db->query("INSERT INTO " . $this->tableName . "(key_id, value) VALUES(:key_id, :value)", $values);
    }

    protected function get($key)
    {
        $this->connect();
        return json_decode($this->keyExists($key)['value'], true);
    }

    protected function delete($key)
    {
        $this->connect();
        $this->db->query("DELETE  FROM " . $this->tableName . " WHERE key_id = :key_id", ['key_id' => $key]);
    }

}
