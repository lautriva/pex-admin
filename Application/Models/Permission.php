<?php
class Model_Permission extends Model_Generated_Permission
{
    CONST TYPE_GROUP = 0;
    CONST TYPE_PLAYER = 1;

    protected static function _getTableName()
    {
        return self::getTableName('permissions');
    }

    public static function getTableName($originalName)
    {
        $config = Config::getInstance();
        return $config->getOption('application/table_aliases/'.$originalName, $originalName);
    }

    public static function getPermissions($name, $type)
    {
        $result = [];

        $res = self::find([
            'select' => ['permission', 'world'],
            'where' => 'name = :name and type = :type and value = \'\'',
            'bind' => [
                'name' => $name,
                'type' => $type
            ]
        ]);

        foreach ($res as $row)
        {
            $result[$row['world']][] = $row['permission'];
        }

        return $result;
    }

    public static function setPermissions($name, $type, $permissions)
    {
        $db = Oxygen_Db::getDefaultAdapter();

        $permissionsTable = Model_Permission::getTableName('permissions');

        // Remove old permissions
        $res = $db->prepare('DELETE FROM '.$permissionsTable.' WHERE name = :name AND type = :type AND VALUE = \'\'');
        $res->bindValue(':name', $name);
        $res->bindValue(':type', $type);
        $res->execute();

        foreach ($permissions as $permission)
        {
            $res = $db->prepare('INSERT INTO '.$permissionsTable.' (`name`, `type`, `permission`, `world`, `value`)
                VALUES (:name, :type, :permission, :world, :value)');
            $res->bindValue(':name', $name);
            $res->bindValue(':type', $type);
            $res->bindValue(':permission', $permission['node']);
            $res->bindValue(':value', '');
            $res->bindValue(':world', $permission['world']);
            $res->execute();
        }
    }
}
