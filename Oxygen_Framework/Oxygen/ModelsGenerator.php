<?php
require __DIR__ . '/Model_Generators/Abstract.php';

class Generator_Exception extends Exception {};

class Oxygen_ModelsGenerator
{
    /**
     * Generate Models from tables schemas
     *
     * @param array $options
     *      Generator options as key/value, here is the full explanation:
     *       'classType' => 'Model', The class type to generate (must be registered in autoloader)
     *       'subclass' => '', Subclass to append to main class type, e.g. 'Foo' => 'Model_Foo'
     *       'adapterName' => null, The PDO adapter name to use to get table structures. Uses the default if empty
     *       'specialTables' => [], An array to specify special table(s) to convert (see below)
     *       'dbms' => 'MySQL', The DBMS to use (at the moment, only 'MySQL' or 'SQLite' is supported)
     *       'generatedSubclass' => 'Generated', The subclass to apend to generated class,
     *              e.g. with default value, the table named 'foo' becomes to'Model_Generated_Foo'
     *       'indentation' => '    ' The indent level chars to use. Feel free to use your own coding rules :)
     *
     *       About the special tables field
     *          each key/value pair is a table
     *          If key is a table name, the model name will be the value (with an ucfirst)
     *          If no key is specified (or key is numeric) the table name will be keep as-is
     *          otherwise any ending 's' will be removed from table, Here's sample array :
     *          array(
     *              'foobars'         // Keep Table name 'foobars' as class name (otherwise it becomes 'foobar')
     *              'foobar' => 'baz' // Set class name to be 'baz' for the table 'foobar'
     *
     * @throws Generator_Exception
     */
    public static function generateModels($options = [])
    {
        // TODO: Add PHPDoc comments in each function headers

        if (!Oxygen_Utils::isDev())
            throw new Generator_Exception("Models generator is not intended for production use !");

        if (!is_array($options))
            throw new Generator_Exception("Models generator options must be an array !");

        $defaultOptions = [
            'classType' => 'Model',
            'subclass' => '',
            'adapterName' => null,
            'specialTables' => [],
            'dbms' => 'MySQL',
            'generatedSubclass' => 'Generated',
            'indentation' => '    '
        ];

        // Options init
        $options = array_merge($defaultOptions, $options);
        $dbms = $options['dbms'];
        $namespace = $options['classType'];
        $subclass = !empty($options['subclass']) ? '_'.$options['subclass'] : '';
        $generatedSubclass = $options['generatedSubclass'];

        $indentation = $options['indentation'];
        $specialTables = $options['specialTables'];

        $autoloader = Project::getInstance()->getAutoloader();

        $generatedClassNamePrefix = $namespace.$subclass.'_'.$generatedSubclass;

        $classPath = $autoloader->getClassFileFromClassName($namespace.$subclass, true);
        $classPath  = end($classPath).DIRECTORY_SEPARATOR;

        $generatedClassPath = $autoloader->getClassFileFromClassName($generatedClassNamePrefix, true);
        $generatedClassPath  = end($generatedClassPath).DIRECTORY_SEPARATOR;

        $adapterName = $options['adapterName'];
        $db = $adapterName !== null ? Oxygen_Db::getAdapter($adapterName) : Oxygen_Db::getDefaultAdapter();
        $adapterString = $adapterName !== null ? 'Oxygen_Db::getAdapter(\''.$adapterName.'\')' : 'Oxygen_Db::getDefaultAdapter()';

        $result = [];

        if (!$db)
            throw new Generator_Exception("Unable to connect to the database");

        // If output folder not exists, create it
        if (!is_dir($generatedClassPath))
            mkdir($generatedClassPath, 0777, true);

        // If output folder still not exists, throw an exception
        if (!is_dir($generatedClassPath) || !is_writable($generatedClassPath))
            throw new Generator_Exception("Directory '". $generatedClassPath . "' must exist and be writtable");

        $generatorFilename = __DIR__ . '/Model_Generators/'.$dbms.'.php';

        $generatorClass = 'Oxygen_Model_Generator_'.$dbms;

        if (!class_exists($generatorClass))
        {
            if (!file_exists($generatorFilename))
                throw new Generator_Exception("Model generator not found for the '".$dbms."' database type");

            require $generatorFilename;
        }

        $generator = new $generatorClass($db);

        if (!is_subclass_of($generator, 'Oxygen_Model_Generator_Abstract'))
            throw new Generator_Exception("Database handler for $dbms not found");

        $tables = $generator->getTables();

        foreach($tables as $tableName)
        {
            $class = '<?php'."\n";

            // strip last 's' from table name (ie. table 'users' become class User)
            if (in_array($tableName, array_keys($specialTables)) || substr($tableName, -1) != 's')
                $classNameRaw = !empty($specialTables[$tableName]) ? $specialTables[$tableName] : Oxygen_Utils::convertSeparatorToUcLetters($tableName);
            else
                $classNameRaw = substr(Oxygen_Utils::convertSeparatorToUcLetters($tableName), 0, -1);

            $name = $tableName;
            $classNameRaw = ucfirst($classNameRaw);

            $className = $namespace . $subclass .'_'. $classNameRaw;
            $generatedClassName = $generatedClassNamePrefix .'_'. $classNameRaw;

            $fields = $generator->getFieldsList($tableName);

            end($fields);
            $last_field = key($fields);

            $class .= "class " . $generatedClassName . "\n{\n";

            foreach ($fields as $field => $fieldType)
            {
                $class .= str_repeat($indentation, 1).'protected $'.Oxygen_Utils::convertSeparatorToUcLetters($field)." = null;\n";
            }

            // Constructor
            $class .= "\n";
            $class .= str_repeat($indentation, 1).'public function __construct($fieldsOrId = 0)'."\n";
            $class .= str_repeat($indentation, 1)."{\n";
            $class .= str_repeat($indentation, 2)."if (!empty(\$fieldsOrId) && is_numeric(\$fieldsOrId))\n";
            $class .= str_repeat($indentation, 3)."\$this->load(\$fieldsOrId);\n";
            $class .= str_repeat($indentation, 2)."else if (!empty(\$fieldsOrId) && is_array(\$fieldsOrId))\n";
            $class .= str_repeat($indentation, 2)."{\n";
            $class .= str_repeat($indentation, 3)."\$fields = \$fieldsOrId;\n\n";

            foreach ($fields as $field => $fieldType)
            {
                $class .= str_repeat($indentation, 3).'$this->'.Oxygen_Utils::convertSeparatorToUcLetters($field).
                    ' = isset($fields[\''.$field.'\']) ? $fields[\''.$field.'\'] : '.self::getDefaultValueByType($fieldType).';'."\n";
            }
            $class .= str_repeat($indentation, 2)."}\n";
            $class .= str_repeat($indentation, 2)."else \n";
            $class .= str_repeat($indentation, 2)."{\n";

            foreach ($fields as $field => $fieldType)
            {
                $fieldValue = self::getDefaultValueByType($fieldType);
                $class .= str_repeat($indentation, 3).'$this->'.Oxygen_Utils::convertSeparatorToUcLetters($field)." = {$fieldValue};\n";
            }
            $class .= str_repeat($indentation, 2)."}\n";

            $class .= str_repeat($indentation, 1)."}\n\n";

            // Database adapter function
            $class .= str_repeat($indentation, 1).'protected static function _getDbAdapter()'."\n";
            $class .= str_repeat($indentation, 1).'{'."\n";
            $class .= str_repeat($indentation, 2).'return '.$adapterString.';'."\n";
            $class .= str_repeat($indentation, 1).'}'."\n\n";

            // Table name function
            $class .= str_repeat($indentation, 1).'protected static function _getTableName()'."\n";
            $class .= str_repeat($indentation, 1).'{'."\n";
            $class .= str_repeat($indentation, 2).'return \''.$tableName.'\';'."\n";
            $class .= str_repeat($indentation, 1).'}'."\n\n";

            // Save function
            $class .= str_repeat($indentation, 1)."public function save()\n".$indentation."{\n";
            $class .= str_repeat($indentation, 2).'$db = static::_getDbAdapter()'.";\n\n";
            $class .= str_repeat($indentation, 2).'$res = $db->prepare(\''."\n";
            $class .= str_repeat($indentation, 3).'INSERT INTO `\'.static::_getTableName().\'` (';
            foreach ($fields as $field => $fieldType)
            {
                $class .= '`'.$field.'`';
                if ($field != $last_field)
                    $class .= ", ";
            }

            $class .= ')'."\n";
            $class .= str_repeat($indentation, 3).'VALUES (';
            foreach ($fields as $field => $fieldType)
            {
                $class .= ':'.$field;
                if ($field != $last_field)
                    $class .= ", ";
            }
            $class .= ') ON DUPLICATE KEY UPDATE'."\n";
            foreach ($fields as $field => $fieldType)
            {
                $class .= str_repeat($indentation, 3).'`'.$field.'` = VALUES('.$field.')';
                if ($field != $last_field)
                    $class .= ",";
                $class .= "\n";
            }
            $class .= str_repeat($indentation, 2).'\');'."\n\n";

            foreach ($fields as $field => $fieldType)
            {
                $camelCaseField = Oxygen_Utils::convertSeparatorToUcLetters($field);
                $fieldType = !empty($fieldType) ? ', PDO::PARAM_'.strtoupper($fieldType) : '';

                $class .= str_repeat($indentation, 2).'$res->bindValue(\':'.$field.'\', $this->'.$camelCaseField.$fieldType.'); '."\n";
            }

            $class .= "\n";
            $class .= str_repeat($indentation, 2).'$res->execute();'."\n\n";
            $class .= str_repeat($indentation, 2).'if (empty($this->id))'."\n";
            $class .= str_repeat($indentation, 3).'$this->id = $db->lastInsertId();'."\n";

            $class .= str_repeat($indentation, 1).'}'."\n\n";

            // Load function
            $class .= str_repeat($indentation, 1).'public function load($id = 0)'."\n".$indentation."{\n";
            $class .= str_repeat($indentation, 2).'$db = static::_getDbAdapter();'."\n\n";
            $class .= str_repeat($indentation, 2).'$res = $db->prepare(\''."\n";
            $class .= str_repeat($indentation, 3).'SELECT ';
            foreach ($fields as $field => $fieldType)
            {
                $class .= '`'.$field.'`';
                if ($field != $last_field)
                    $class .= ", ";
            }
            $class .= "\n";
            $class .= str_repeat($indentation, 3).'FROM `\'.static::_getTableName().\'`'."\n";
            $class .= str_repeat($indentation, 3).'WHERE id = :id'."\n";
            $class .= str_repeat($indentation, 2).'\');'."\n\n";

            $class .= str_repeat($indentation, 2).'$res->bindValue(\':id\', !empty($id) ? $id : $this->id);'."\n\n";
            $class .= str_repeat($indentation, 2).'$res->execute();'."\n\n";

            $class .= str_repeat($indentation, 2).'if ($row = $res->fetch())'."\n";
            $class .= str_repeat($indentation, 2).'{'."\n";
            foreach ($fields as $field => $fieldType)
            {
                $camelCaseField = Oxygen_Utils::convertSeparatorToUcLetters($field);
                $class .= str_repeat($indentation, 3).'$this->'.$camelCaseField.' = $row[\''.$field.'\'];'."\n";
            }
            $class .= str_repeat($indentation, 2).'}'."\n";

            $class .= str_repeat($indentation, 1).'}'."\n\n";

            // Delete function
            $class .= str_repeat($indentation, 1)."public function delete(\$id = 0)\n".$indentation."{\n";
            $class .= str_repeat($indentation, 2).'$db = static::_getDbAdapter();'."\n\n";
            $class .= str_repeat($indentation, 2).'$res = $db->prepare(\''."\n";
            $class .= str_repeat($indentation, 3).'DELETE FROM `\'.static::_getTableName().\'`'."\n";
            $class .= str_repeat($indentation, 3).'WHERE id = :id'."\n";
            $class .= str_repeat($indentation, 2).'\');'."\n\n";

            $class .= str_repeat($indentation, 2).'$res->bindValue(\':id\', !empty($id) ? $id : $this->id);'."\n\n";
            $class .= str_repeat($indentation, 2).'$res->execute();'."\n";

            $class .= str_repeat($indentation, 1).'}'."\n\n";

            // Find function
            $findPrototype = <<<'EOM'
__INDENTATION__/**
__INDENTATION__ * Find rows in database according to criterion
__INDENTATION__ *
__INDENTATION__ * $criterion array : find criterion as array(
__INDENTATION__ *      'select' => array('field1', 'field2')
__INDENTATION__ *      'where' => array('cond1', 'cond2')
__INDENTATION__ *      'other' => array('ORDER BY something')
__INDENTATION__ * )
__INDENTATION__ * $returnObjects bool : if true, rows will be returned as '__CLASSNAME__' instances
__INDENTATION__ * */
__INDENTATION__public static function find($criterion = array(), $returnObjects = false)
__INDENTATION__{
__INDENTATION____INDENTATION__return Oxygen_Db::find(
__INDENTATION____INDENTATION____INDENTATION__static::_getDbAdapter(),
__INDENTATION____INDENTATION____INDENTATION__static::_getTableName(),
__INDENTATION____INDENTATION____INDENTATION__'__CLASSNAME__',
__INDENTATION____INDENTATION____INDENTATION__$criterion,
__INDENTATION____INDENTATION____INDENTATION__$returnObjects
__INDENTATION____INDENTATION__);
__INDENTATION__}
EOM;
            $class .= str_replace(
                array('__CLASSNAME__', '__INDENTATION__'),
                array($className, $indentation),
                $findPrototype
            )."\n\n";

            // Setters
            $class .= str_repeat($indentation, 1).'// Setters'."\n";
            foreach ($fields as $field => $fieldType)
            {
                $camelCaseField = Oxygen_Utils::convertSeparatorToUcLetters($field);

                $class .= str_repeat($indentation, 1).'public function set'.ucfirst($camelCaseField).'($'.$camelCaseField.')'."\n";
                $class .= str_repeat($indentation, 1).'{'."\n";
                $class .= str_repeat($indentation, 2).'$this->'.$camelCaseField.' = $'.$camelCaseField.';'."\n";
                $class .= str_repeat($indentation, 2).'return $this;'."\n";
                $class .= str_repeat($indentation, 1).'}'."\n";
                if ($field != $last_field)
                    $class .= "\n";
            }

            $class .= "\n";

            // Getters
            $class .= str_repeat($indentation, 1).'// Getters'."\n";
            foreach ($fields as $field => $fieldType)
            {
                $class .= str_repeat($indentation, 1).'public function get'.ucfirst(Oxygen_Utils::convertSeparatorToUcLetters($field)).'()'."\n";
                $class .= str_repeat($indentation, 1).'{'."\n";
                $class .= str_repeat($indentation, 2).'return $this->'.Oxygen_Utils::convertSeparatorToUcLetters($field).';'."\n";
                $class .= str_repeat($indentation, 1).'}'."\n";
                if ($field != $last_field)
                   $class .= "\n";
            }

            // End Class bracket
            $class .= '}'."\n";

            // Writing base model file
            file_put_contents($generatedClassPath . $classNameRaw.'.php', $class);

            $result[$className] = $generatedClassPath . $classNameRaw.'.php';

            // Create Model in model folder if not exists
            if (!file_exists($classPath . $classNameRaw.'.php'))
            {
                $model = "<?php\n";
                $model .= 'class '. $className.' extends ' . $generatedClassName . "\n";
                $model .= "{\n\n";
                $model .= "}\n";

                file_put_contents($classPath . $classNameRaw.'.php', $model);
                $result[$generatedClassName] = $classPath . $classNameRaw.'.php';
            }
        }

        return $result;
    }

    protected static function getDefaultValueByType($type)
    {
        $result = 'null';

        switch ($type)
        {
            case 'int':
                $result = '0';
            break;

            case 'str':
                $result = "''";
            break;

            case 'bool':
                $result = 'false';
            break;

            default:
                $result = 'null';
            break;
        }

        return $result;
    }
}