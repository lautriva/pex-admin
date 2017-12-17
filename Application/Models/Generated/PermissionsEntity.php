<?php
class Model_Generated_PermissionsEntity
{
    protected $id = null;
    protected $name = null;
    protected $type = null;
    protected $default = null;

    public function __construct($fieldsOrId = 0)
    {
        if (!empty($fieldsOrId) && is_numeric($fieldsOrId))
            $this->load($fieldsOrId);
        else if (!empty($fieldsOrId) && is_array($fieldsOrId))
        {
            $fields = $fieldsOrId;

            $this->id = isset($fields['id']) ? $fields['id'] : 0;
            $this->name = isset($fields['name']) ? $fields['name'] : null;
            $this->type = isset($fields['type']) ? $fields['type'] : 0;
            $this->default = isset($fields['default']) ? $fields['default'] : 0;
        }
        else 
        {
            $this->id = 0;
            $this->name = null;
            $this->type = 0;
            $this->default = 0;
        }
    }

    protected static function _getDbAdapter()
    {
        return Oxygen_Db::getDefaultAdapter();
    }

    protected static function _getTableName()
    {
        return 'permissions_entity';
    }

    public function save()
    {
        $db = static::_getDbAdapter();

        $res = $db->prepare('
            INSERT INTO `'.static::_getTableName().'` (`id`, `name`, `type`, `default`)
            VALUES (:id, :name, :type, :default) ON DUPLICATE KEY UPDATE
            `id` = VALUES(id),
            `name` = VALUES(name),
            `type` = VALUES(type),
            `default` = VALUES(default)
        ');

        $res->bindValue(':id', $this->id, PDO::PARAM_INT); 
        $res->bindValue(':name', $this->name); 
        $res->bindValue(':type', $this->type, PDO::PARAM_INT); 
        $res->bindValue(':default', $this->default, PDO::PARAM_INT); 

        $res->execute();

        if (empty($this->id))
            $this->id = $db->lastInsertId();
    }

    public function load($id = 0)
    {
        $db = static::_getDbAdapter();

        $res = $db->prepare('
            SELECT `id`, `name`, `type`, `default`
            FROM `'.static::_getTableName().'`
            WHERE id = :id
        ');

        $res->bindValue(':id', !empty($id) ? $id : $this->id);

        $res->execute();

        if ($row = $res->fetch())
        {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->type = $row['type'];
            $this->default = $row['default'];
        }
    }

    public function delete($id = 0)
    {
        $db = static::_getDbAdapter();

        $res = $db->prepare('
            DELETE FROM `'.static::_getTableName().'`
            WHERE id = :id
        ');

        $res->bindValue(':id', !empty($id) ? $id : $this->id);

        $res->execute();
    }

    /**
     * Find rows in database according to criterion
     *
     * $criterion array : find criterion as array(
     *      'select' => array('field1', 'field2')
     *      'where' => array('cond1', 'cond2')
     *      'other' => array('ORDER BY something')
     * )
     * $returnObjects bool : if true, rows will be returned as 'Model_PermissionsEntity' instances
     * */
    public static function find($criterion = array(), $returnObjects = false)
    {
        return Oxygen_Db::find(
            static::_getDbAdapter(),
            static::_getTableName(),
            'Model_PermissionsEntity',
            $criterion,
            $returnObjects
        );
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDefault()
    {
        return $this->default;
    }
}
