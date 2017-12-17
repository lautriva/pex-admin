<?php

class Oxygen_Model_Generator_test extends Oxygen_Model_Generator_Abstract
{
    public function __construct($db)
    {
        echo 'Test adapter loaded<br/>';
    }

    public function getTables()
    {
        return array(
            'test',
            'test2'
        );
    }

    public function getFieldsList($tableName)
    {
        // TODO use PDO_PARAM_xxx

        return array(
            'id' => 'int',
            'test_str' => 'str',
            'test3_bool' => 'bool',
            'plif_b' => 'bool',
            'plaf_dqf_dsqf' => '',
            'test2_int' => 'int'
        );
    }
}
?>