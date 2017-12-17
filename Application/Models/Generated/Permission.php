<?php
class Model_Generated_Permission
{
    protected $id = null;
    protected $name = null;
    protected $type = null;
    protected $permission = null;
    protected $world = null;
    protected $value = null;

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
            $this->permission = isset($fields['permission']) ? $fields['permission'] : '';
            $this->world = isset($fields['world']) ? $fields['world'] : null;
            $this->value = isset($fields['value']) ? $fields['value'] : '';
        }
        else 
        {
            $this->id = 0;
            $this->name = null;
            $this->type = 0;
            $this->permission = '';
            $this->world = null;
            $this->value = '';
        }
    }

    protected static function _getDbAdapter()
    {
        return Oxygen_Db::getDefaultAdapter();
    }

    protected static function _getTableName()
    {
        return 'permissions';
    }

    public function save()
    {
        $db = static::_getDbAdapter();

        $res = $db->prepare('
            INSERT INTO `'.static::_getTableName().'` (`id`, `name`, `type`, `permission`, `world`, `value`)
            VALUES (:id, :name, :type, :permission, :world, :value) ON DUPLICATE KEY UPDATE
            `id` = VALUES(id),
            `name` = VALUES(name),
            `type` = VALUES(type),
            `permission` = VALUES(permission),
            `world` = VALUES(world),
            `value` = VALUES(value)
        ');

        $res->bindValue(':id', $this->id, PDO::PARAM_INT); 
        $res->bindValue(':name', $this->name); 
        $res->bindValue(':type', $this->type, PDO::PARAM_INT); 
        $res->bindValue(':permission', $this->permission, PDO::PARAM_STR); 
        $res->bindValue(':world', $this->world); 
        $res->bindValue(':value', $this->value, PDO::PARAM_STR); 

        $res->execute();

        if (empty($this->id))
            $this->id = $db->lastInsertId();
    }

    public function load($id = 0)
    {
        $db = static::_getDbAdapter();

        $res = $db->prepare('
            SELECT `id`, `name`, `type`, `permission`, `world`, `value`
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
            $this->permission = $row['permission'];
            $this->world = $row['world'];
            $this->value = $row['value'];
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
     * $returnObjects bool : if true, rows will be returned as 'Model_Permission' instances
     * */
    public static function find($criterion = array(), $returnObjects = false)
    {
        return Oxygen_Db::find(
            static::_getDbAdapter(),
            static::_getTableName(),
            'Model_Permission',
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

    public function setPermission($permission)
    {
        $this->permission = $permission;
        return $this;
    }

    public function setWorld($world)
    {
        $this->world = $world;
        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;
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

    public function getPermission()
    {
        return $this->permission;
    }

    public function getWorld()
    {
        return $this->world;
    }

    public function getValue()
    {
        return $this->value;
    }
}
