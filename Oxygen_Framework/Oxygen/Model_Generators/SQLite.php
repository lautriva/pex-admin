<?php

/**
 * Model generator for a SQLite database type
 */
class Oxygen_Model_Generator_SQLite extends Oxygen_Model_Generator_Abstract
{
    protected $db = NULL;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getTables()
    {
        $result = array();

        $res = $this->db->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence';");
        $res->execute();

        while (($row = $res->fetch()))
        {
            $result[] = $row['name'];
        }

        return $result;
    }

    public function getFieldsList($tableName)
    {
        $result = array();

        $res = $this->db->prepare("PRAGMA table_info(".$tableName.");");
        $res->execute();

        while (($row = $res->fetch()))
        {
            $fieldName = $row['name'];
            $fieldType = '';

            if (stripos($row['type'], 'int') !== false)
                $fieldType = 'int';

            if (stripos($row['type'], 'text') !== false)
                $fieldType = 'str';

            $result[$fieldName] = $fieldType;
        }

        return $result;
    }
}
?>