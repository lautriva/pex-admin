<?php

class Db_Exception extends Exception {}

class Oxygen_Db
{
    private static $registeredAdapters = array();
    private static $adaptersList = array();

    /**
     * Get the DB adapter named 'default'
     *
     * @return boolean|mixed
     */
    public static function getDefaultAdapter()
    {
        return self::getAdapter('default');
    }

    /**
     * Get a PDO db adapter by name
     *
     * @param unknown $adapterName
     * @throws Db_Exception
     * @return boolean|mixed
     */
    public static function getAdapter($adapterName)
    {
        $adaptersList = self::$adaptersList;
        $result = false;

        if (!empty(self::$registeredAdapters[$adapterName]))
            $result = self::$registeredAdapters[$adapterName];
        else if (isset($adaptersList[$adapterName]))
        {
            try {
                $dsn = isset($adaptersList[$adapterName]['dsn']) ? $adaptersList[$adapterName]['dsn'] : '';
                $login = isset($adaptersList[$adapterName]['user']) ? $adaptersList[$adapterName]['user'] : '';
                $password = isset($adaptersList[$adapterName]['password']) ? $adaptersList[$adapterName]['password'] : '';

                self::$registeredAdapters[$adapterName] = new PDO($dsn, $login, $password);
                self::$registeredAdapters[$adapterName]->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$registeredAdapters[$adapterName]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $result = self::$registeredAdapters[$adapterName];
            }
            catch (Exception $e)
            {
                throw new Db_Exception('Cannot open connection to DSN '.$adaptersList[$adapterName]['dsn'] .
                    ' : ' . $e->getMessage());
            }
        }
        else
            throw new Db_Exception('Database adapter "'.$adapterName.'" not found');

        if (!$result || !is_a($result, 'PDO'))
            throw new Db_Exception('Cannot open connection with database adapter "'.$adapterName.'"');

        return $result;
    }

    /**
     * Register DB adapters
     *
     * @param array $adaptersList
     */
    public static function loadAdapters($adaptersList)
    {
        self::$adaptersList = $adaptersList;
    }

    /**
     * Find rows in database according to criterion
     *
     * $db PDO : Database adapter
     * $table string : table name
     * $class string : if $returnObjects, rows will be returned as $class instances
     * $criterion array : find criterion as array(
     *      'select' => array('field1', 'field2')
     *      'where' => array('cond1', 'cond2')
     *      'other' => array('ORDER BY something')
     * )
     *
     * @throws Db_Exception
     * @return mixed
     * */
    public static function find($db, $table, $class, $criterion = array(), $returnObjects = false)
    {
        $result = array();

        if (is_array($criterion))
        {
            $select = '';
            $from = '';
            $join = '';
            $where = '';
            $other = '';

            if (empty($criterion['from']))
                $criterion['from'] = $table;

            if (empty($criterion['select']))
                $criterion['select'] = '*';

            $select = is_array($criterion['select']) ? implode(',', $criterion['select']) : $criterion['select'];
            $from = is_array($criterion['from']) ? implode(',', $criterion['from']) : $criterion['from'];

            if (!empty($criterion['join']))
            {
                $joinQuery = $criterion['join'];

                if (is_string($joinQuery))
                    $join = $joinQuery;
                elseif (is_array($joinQuery))
                {
                    foreach ($joinQuery as $joinData)
                    {
                        // Do inner join if no type specified
                        $joinType = isset($joinData['type']) ? $joinData['type'] : 'INNER';

                        // We do a full cross join if no condition specified
                        $joinOn = isset($joinData['on']) ? $joinData['on'] : '1=1';

                        if (!isset($joinData['table']))
                            throw new Db_Exception('Cannot join without secondary table');
                        else
                            $joinTable = $joinData['table'];

                        $join .= $joinType.' JOIN '.$joinTable.' ON '.$joinOn.' ';
                    }
                }
            }

            if (!empty($criterion['where']))
            {
                // if it's associative array, keys are fields and values are requested values
                if (Oxygen_Utils::isAssociativeArray($criterion['where']) && empty($criterion['bind']))
                {
                    $fieldsWhere = [];

                    foreach ($criterion['where'] as $field => $value)
                    {
                        $fieldsWhere[] = '`'.$field.'` = :'.$field;
                        $criterion['bind'][$field] = $value;
                    }

                    $criterion['where'] = $fieldsWhere;
                }

                $where = 'WHERE ('. (is_array($criterion['where']) ? implode(') AND (', $criterion['where']) : $criterion['where']). ')';
            }

            if (!empty($criterion['other']))
                $other = is_array($criterion['other']) ? implode("\n", $criterion['other']) : $criterion['other'];


            $query = 'SELECT '.$select.'
                      FROM '.$from.'
                      '.$join.'
                      '.$where.'
                      '.$other;

            try
            {
                $res = $db->prepare($query);

                if (!empty($criterion['bind']))
                {
                    foreach ($criterion['bind'] as $param => $value)
                    {
                        $res->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
                    }
                }

                $res->execute();
            }
            catch (Exception $e)
            {
                // Append query to the exception
                $queryInfo = "\n" . (!empty($criterion['bind']) ? "Prepared query" : 'Query').' was: ';
                $queryInfo .= $query;

                throw new Db_Exception($e->getMessage().$queryInfo);
            }

            while ($row = $res->fetch())
            {
                if ($returnObjects)
                    $result[] = new $class($row['id']);
                else
                    $result[] = $row;
            }
        }

        return $result;
    }
}