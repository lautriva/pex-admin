<?php

/**
 * Model generator for a MySQL database type
 */
class Oxygen_Model_Generator_MySQL extends Oxygen_Model_Generator_Abstract
{
    protected $db = NULL;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getTables()
    {
        $result = array();

        $res = $this->db->prepare("show table status");

        $res->execute();

        while (($row = $res->fetch()))
        {
            $result[] = $row['Name'];
        }

        return $result;
    }

    public function getFieldsList($tableName)
    {
        $result = array();

        $res = $this->db->prepare("show columns from ".$tableName);
        $res->execute();

        while (($row = $res->fetch()))
        {
            $fieldName = $row['Field'];
            $fieldType = '';

            if (strpos($row['Type'], 'int') !== false)
                $fieldType = 'int';

            if (strpos($row['Type'], 'text') !== false)
                $fieldType = 'str';

            $result[$fieldName] = $fieldType;
        }

        return $result;
    }
}
?>