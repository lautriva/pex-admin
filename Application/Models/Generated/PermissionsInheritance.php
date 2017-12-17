<?php
class Model_Generated_PermissionsInheritance
{
    protected $id = null;
    protected $child = null;
    protected $parent = null;
    protected $type = null;
    protected $world = null;

    public function __construct($fieldsOrId = 0)
    {
        if (!empty($fieldsOrId) && is_numeric($fieldsOrId))
            $this->load($fieldsOrId);
        else if (!empty($fieldsOrId) && is_array($fieldsOrId))
        {
            $fields = $fieldsOrId;

            $this->id = isset($fields['id']) ? $fields['id'] : 0;
            $this->child = isset($fields['child']) ? $fields['child'] : null;
            $this->parent = isset($fields['parent']) ? $fields['parent'] : null;
            $this->type = isset($fields['type']) ? $fields['type'] : 0;
            $this->world = isset($fields['world']) ? $fields['world'] : null;
        }
        else 
        {
            $this->id = 0;
            $this->child = null;
            $this->parent = null;
            $this->type = 0;
            $this->world = null;
        }
    }

    protected static function _getDbAdapter()
    {
        return Oxygen_Db::getDefaultAdapter();
    }

    protected static function _getTableName()
    {
        return 'permissions_inheritance';
    }

    public function save()
    {
        $db = static::_getDbAdapter();

        $res = $db->prepare('
            INSERT INTO `'.static::_getTableName().'` (`id`, `child`, `parent`, `type`, `world`)
            VALUES (:id, :child, :parent, :type, :world) ON DUPLICATE KEY UPDATE
            `id` = VALUES(id),
            `child` = VALUES(child),
            `parent` = VALUES(parent),
            `type` = VALUES(type),
            `world` = VALUES(world)
        ');

        $res->bindValue(':id', $this->id, PDO::PARAM_INT); 
        $res->bindValue(':child', $this->child); 
        $res->bindValue(':parent', $this->parent); 
        $res->bindValue(':type', $this->type, PDO::PARAM_INT); 
        $res->bindValue(':world', $this->world); 

        $res->execute();

        if (empty($this->id))
            $this->id = $db->lastInsertId();
    }

    public function load($id = 0)
    {
        $db = static::_getDbAdapter();

        $res = $db->prepare('
            SELECT `id`, `child`, `parent`, `type`, `world`
            FROM `'.static::_getTableName().'`
            WHERE id = :id
        ');

        $res->bindValue(':id', !empty($id) ? $id : $this->id);

        $res->execute();

        if ($row = $res->fetch())
        {
            $this->id = $row['id'];
            $this->child = $row['child'];
            $this->parent = $row['parent'];
            $this->type = $row['type'];
            $this->world = $row['world'];
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
     * $returnObjects bool : if true, rows will be returned as 'Model_PermissionsInheritance' instances
     * */
    public static function find($criterion = array(), $returnObjects = false)
    {
        return Oxygen_Db::find(
            static::_getDbAdapter(),
            static::_getTableName(),
            'Model_PermissionsInheritance',
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

    public function setChild($child)
    {
        $this->child = $child;
        return $this;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setWorld($world)
    {
        $this->world = $world;
        return $this;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getChild()
    {
        return $this->child;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getWorld()
    {
        return $this->world;
    }
}
