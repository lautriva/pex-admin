<?php
class Model_PermissionsEntity extends Model_Generated_PermissionsEntity
{
    protected static function _getTableName()
    {
        return Model_Permission::getTableName('permissions_entity');
    }

    public static function getEntitiesByType($type = Model_Permission::TYPE_GROUP)
    {
        return self::find([
            'where' => ['type' => $type]
        ], true);
    }

    public function getOptions()
    {
        $result = [];

        $res = self::find([
            'select' => ['permission', 'value', 'world'],
            'from' => Model_Permission::getTableName('permissions'),
            'where' => 'name = :name and type = :type and value != \'\'',
            'bind' => [
                'name' => $this->getName(),
                'type' => $this->getType()
            ]
        ]);

        foreach ($res as $row)
        {
            $result[$row['world']][$row['permission']] = $row['value'];
        }

        return $result;
    }

    /**
     * Get an entity by name
     *
     * @param string $name
     * @param int $type
     * @return Model_PermissionsEntity|false
     */
    public static function getEntityByName($name, $type = Model_Permission::TYPE_PLAYER)
    {
        $result = self::find([
            'where' => ['name' => $name, 'type' => $type]
        ], true);

        return count($result) > 0 ? $result[0] : false;
    }

    public static function setGroupData($data, $isCreation, $type = Model_Permission::TYPE_GROUP)
    {
        $result = false;

        $db = Oxygen_Db::getDefaultAdapter();

        $entitiesTable = Model_Permission::getTableName('permissions_entity');
        $permissionsTable = Model_Permission::getTableName('permissions');
        $inheritancesTable = Model_Permission::getTableName('permissions_inheritance');

        $name = $data['name'];

        $name = str_replace(['#', '?', '/', '\\'], '_', $name);

        if (!$isCreation)
        {
            // Remove old group data
            $res = $db->prepare('DELETE FROM `'.$entitiesTable.'` WHERE name = :name');
            $res->bindValue(':name', $name);
            $res->execute();

            // Remove inheritances
            $res = $db->prepare('DELETE FROM `'.$inheritancesTable.'` WHERE child = :name');
            $res->bindValue(':name', $name);
            $res->execute();

            // Remove options
            $res = $db->prepare('DELETE FROM `'.$permissionsTable.'` WHERE name = :name AND VALUE != \'\'');
            $res->bindValue(':name', $name);
            $res->execute();
        }

        $res = $db->prepare('INSERT INTO `'.$entitiesTable.'` (`name`, `type`) VALUES (:name, :type)');
        $res->bindValue(':name', $name);
        $res->bindValue(':type', $type);
        $res->execute();

        $result = new self($db->lastInsertId());

        if (isset($data['parents']))
        {
            $parents = array_unique($data['parents']);

            foreach ($parents as $parent)
            {
                if ($name == $parent)
                    continue;

                $res = $db->prepare('INSERT INTO `'.$inheritancesTable.'` (`child`, `parent`, `type`) VALUES (:child, :parent, :type)');
                $res->bindValue(':child', $name);
                $res->bindValue(':parent', $parent);
                $res->bindValue(':type', $type);
                $res->execute();
            }
        }

        if (isset($data['options']))
        {
            foreach ($data['options'] as $option)
            {
                $res = $db->prepare('INSERT INTO `'.$permissionsTable.'` (`name`, `type`, `permission`, `world`, `value`)
                    VALUES (:name, :type, :permission, :world, :value)');
                $res->bindValue(':name', $name);
                $res->bindValue(':type', $type);
                $res->bindValue(':permission', $option['name']);
                $res->bindValue(':value', $option['value']);
                $res->bindValue(':world', $option['world']);
                $res->execute();
            }
        }

        return $result;
    }

    public function copy($newName)
    {
        if ($newName == 'system')
            return 'Reserved name !';

        $db = Oxygen_Db::getDefaultAdapter();

        $entitiesTable = Model_Permission::getTableName('permissions_entity');
        $permissionsTable = Model_Permission::getTableName('permissions');
        $inheritancesTable = Model_Permission::getTableName('permissions_inheritance');

        // Clone entity
        $res = $db->prepare('INSERT INTO `'.$entitiesTable.'` (`name`, `type`, `default`)
            SELECT :newname, `type`, `default` FROM `'.$entitiesTable.'` e
            WHERE e.name = :name AND e.type = :type;');
        $res->bindValue(':newname', $newName);
        $res->bindValue(':name', $this->getName());
        $res->bindValue(':type', $this->getType());
        $res->execute();

        // Clone inheritances
        $res = $db->prepare('INSERT INTO `'.$inheritancesTable.'` (`child`, `parent`, `type`, `world`)
            SELECT :newname, `parent`, `type`, `world`
            FROM `'.$inheritancesTable.'` i WHERE i.child = :name AND i.type = :type;');
        $res->bindValue(':newname', $newName);
        $res->bindValue(':name', $this->getName());
        $res->bindValue(':type', $this->getType());
        $res->execute();

        // Clone permissions
        $res = $db->prepare('INSERT INTO `'.$permissionsTable.'` (`name`, `type`, `permission`, `world`, `value`)
            SELECT :newname, `type`, `permission`, `world`, `value`
            FROM `'.$permissionsTable.'` p WHERE p.name = :name AND p.type = :type;');
        $res->bindValue(':newname', $newName);
        $res->bindValue(':name', $this->getName());
        $res->bindValue(':type', $this->getType());
        $res->execute();

        return '';
    }

    public function remove()
    {
        $name = $this->getName();

        if ($name == 'system')
            return 'Reserved name !';

        $db = Oxygen_Db::getDefaultAdapter();

        $entitiesTable = Model_Permission::getTableName('permissions_entity');
        $permissionsTable = Model_Permission::getTableName('permissions');
        $inheritancesTable = Model_Permission::getTableName('permissions_inheritance');

        // Remove group data
        $res = $db->prepare('DELETE FROM `'.$entitiesTable.'` WHERE name = :name');
        $res->bindValue(':name', $name);
        $res->execute();

        // Remove inheritances
        $res = $db->prepare('DELETE FROM `'.$inheritancesTable.'` WHERE parent = :name OR child = :name');
        $res->bindValue(':name', $name);
        $res->execute();

        // Remove permissions
        $res = $db->prepare('DELETE FROM `'.$permissionsTable.'` WHERE name = :name');
        $res->bindValue(':name', $name);
        $res->execute();
    }
}
