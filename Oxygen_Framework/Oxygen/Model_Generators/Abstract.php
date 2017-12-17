<?php

abstract class Oxygen_Model_Generator_Abstract
{
    /**
     * Initialize the Generator with the specified database
     *
     * @param PDO $db
     */
    abstract public function __construct($db);

    /**
     * Get all tables
     * @return array
     *      tables list as ['foo', 'bar', 'baz']
     */
    abstract public function getTables();

    /**
     * Fetch all fields from the table name provided
     *
     * @param string $tableName
     * @return array
     *      keys are field names, values are their type (must be one of int, bool or str)
     *      [
     *          'id' => int,
     *          'ga' => int,
     *          'bu' => str,
     *          'zo' => str,
     *          'meu' => bool
     *      ]
     */
    abstract public function getFieldsList($tableName);
}
?>