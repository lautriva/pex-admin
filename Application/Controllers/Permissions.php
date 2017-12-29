<?php

class PermissionsController extends Controller
{
    public function init()
    {
        if (!PexAdmin_Acl::isAllowed('PERMISSION', 'READ'))
            Oxygen_Utils::redirect(Oxygen_Utils::url('home'));

        $this->view->toolbarCurrent = 'permissions';
    }

    public function indexAction()
    {
        $this->view->title = 'Permissions by entity';
    }

    public function editAction()
    {
        $entityName = $this->getRequest()->getParam('name');
        $entityType = $this->getRequest()->getParam('type', 0);

        $canEdit = PexAdmin_Acl::isAllowed('PERMISSION', 'UPDATE');

        $oEntity = false;
        if (!empty($entityName))
            $oEntity = Model_PermissionsEntity::getEntityByName($entityName, $entityType);

        if (!$oEntity)
            Oxygen_Utils::redirect(Oxygen_Utils::url('permission-index'));

        $permissions = !empty($_POST['permissions'])
            ? $_POST['permissions']
            : []
        ;

        if ($canEdit && $this->getRequest()->isPost())
        {
            Model_Permission::setPermissions($entityName, $entityType, $permissions);
        }

        $this->view->oEntity = $oEntity;
        $this->view->parents = Model_PermissionsInheritance::getGroupsParents();
        $this->view->permissions = Model_Permission::getPermissions($entityName, $entityType);
    }

    public function listAction()
    {
        $this->view->disableRender();
        header('Content-type: application/json');

        $allParents = Model_PermissionsInheritance::getGroupsParents();

        $permissionsTableName = Model_Permission::getTableName('permissions');
        $entityTableName = Model_Permission::getTableName('permissions_entity');

        $nameEntityPermissionField = $permissionsTableName.'.name';
        $nameEntityField = $entityTableName.'.name';

        $typeEntityField = $entityTableName.'.type';

        $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $length = isset($_GET['length']) ? intval($_GET['length']) : 10;

        $columnOrderTarget = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
        $columnOrderDirection = (isset($_GET['order'][0]['dir']) && $_GET['order'][0]['dir'] == 'asc' ) ? 'asc' : 'desc';

        $columnOrder = [
            $nameEntityField,
            $typeEntityField,
            'nbPerms'
        ];

        $result = array(
            'draw' => intval($_GET['draw']),
            'data' => array()
        );

        $result['recordsTotal'] = $result['recordsFiltered'] = Model_PermissionsEntity::find(array(
            'from' => $entityTableName,
            'select' => 'count(id) count',
        ))[0]['count'];

        $queryData = array(
            'from' => $entityTableName,
            'other' => 'GROUP BY '.$nameEntityField.','.$typeEntityField,
            'join' => [['type' => 'left', 'table' => $permissionsTableName, 'on' => $nameEntityField.' = '.$nameEntityPermissionField]]
        );

        if (!empty($_GET['search']['value']))
        {
            $search = $_GET['search']['value'];

            $queryData['where'] = $entityTableName.'.name LIKE CONCAT(\'%\',:name,\'%\')';
            $queryData['bind']['name'] = $_GET['search']['value'];

            $queryData['select'] = 'count(*) count';

            $res = Model_Permission::find(array_merge($queryData, ['from' => $entityTableName]));

            $result['recordsFiltered'] = !empty($res) ? $res[0]['count'] : 0;
            unset($queryData['select']);
        }

        $queryData['other'] .= ' ORDER BY '.$columnOrder[$columnOrderTarget].' '.$columnOrderDirection.', '.
                $nameEntityField.' LIMIT '.$start.', '.$length;

        $queryData['select'] = $nameEntityField.', '.$entityTableName.'.type, SUM(IF(value = \'\', 1, 0)) nbPerms';

        $res = Model_Permission::find($queryData);

        /** @var Model_PermissionsEntity $row */
        foreach ($res as $row)
        {
            $finalRow = array();

            $entityName = $row['name'];
            $entityType = $row['type'];
            $permissions = $row['nbPerms'];

            $isPlayer = $entityType == Model_Permission::TYPE_PLAYER;

            $icon = $isPlayer
                ? '<span class="fa fa-fw fa-user"></span>'
                : '<span class="fa fa-fw fa-users"></span>'
            ;

            $permissionEditUrl = Oxygen_Utils::url('permission-edit', [
                'name' => urlencode($entityName),
                'type' => $entityType
            ]);

            $finalRow[] = '<a href="'.$permissionEditUrl.'">'.$icon.$entityName.'</a>';
            $finalRow[] = $entityType == 1 ? 'Player' : 'Group';
            $finalRow[] = $permissions;

            $result['data'][] = $finalRow;
        }

        echo json_encode($result);

    }
}
