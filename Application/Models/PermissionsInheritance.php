<?php
class Model_PermissionsInheritance extends Model_Generated_PermissionsInheritance
{
    protected static function _getTableName()
    {
        return Model_Permission::getTableName('permissions_inheritance');
    }

    public static function getGroupsParents()
    {
        $tableName = self::_getTableName();

        $result = [];

        $res = Model_PermissionsEntity::find([
            'select' => ['a.child', 'GROUP_CONCAT(DISTINCT a.parent) parents'],
            'from' => $tableName.' a',
            'join' => [
                ['type' => 'LEFT', 'table' => $tableName.' b', 'on' => 'a.parent = b.child']
            ],
            'other' => 'GROUP BY a.child'
        ]);

        foreach ($res as $row)
        {
            $result[$row['child']] = explode(',', $row['parents']);
        }

        return $result;
    }
}
