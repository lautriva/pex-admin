<?php

class GroupsController extends Controller
{
    public function init()
    {
        if (!PexAdmin_Acl::isAllowed('GROUP', 'READ'))
            Oxygen_Utils::redirect(Oxygen_Utils::url('home'));

        $this->view->toolbarCurrent = 'groups';
    }

    public function indexAction()
    {
        $this->view->title = 'PEX Groups list';
    }

    public function editAction()
    {
        $groupName = $this->getRequest()->getParam('name');
        $groupName = rawurldecode($groupName);

        $oGroup = false;

        $canCreate = PexAdmin_Acl::isAllowed('GROUP', 'CREATE');
        $canEdit = PexAdmin_Acl::isAllowed('GROUP', 'UPDATE');

        if (!empty($groupName))
            $oGroup = Model_PermissionsEntity::getEntityByName($groupName, Model_Permission::TYPE_GROUP);

        if (!$oGroup && !$canCreate)
            Oxygen_Utils::redirect(Oxygen_Utils::url('group-index'));
        else if(!$oGroup)
            $oGroup = new Model_PermissionsEntity();

        $isCreate = empty($oGroup->getId());

        if (($isCreate && $canCreate || $canEdit) && $this->getRequest()->isPost())
        {
            $oGroup = Model_PermissionsEntity::setGroupData($_POST, $isCreate);

            Oxygen_Utils::redirect(Oxygen_Utils::url('group-edit', [
                'name' => rawurlencode($oGroup->getName())
            ]));
        }

        $this->view->oGroup = $oGroup;
        $this->view->parents = Model_PermissionsInheritance::getGroupsParents();
    }

    // Used to feed select's ists
    public function listAjaxAction()
    {
        $this->view->disableRender();

        $type = $this->getRequest()->getParam('type', Model_Permission::TYPE_GROUP);

        header('Content-type: application/json');

        $query = [
            'select' => ['name as id', 'name as text'],
            'where' => 'type = '.$type
        ];

        if (!empty($_GET['q']))
        {
            $query['where'] .= ' AND name LIKE CONCAT(\'%\',:name,\'%\')';
            $query['bind']['name'] = $_GET['q'];
        }

        echo json_encode(Model_PermissionsEntity::find($query));
    }

    public function listAction()
    {
        $this->view->disableRender();
        header('Content-type: application/json');

        $allParents = Model_PermissionsInheritance::getGroupsParents();

        $entitiesTableName = Model_Permission::getTableName('entities');

        $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $length = isset($_GET['length']) ? intval($_GET['length']) : 10;

        $columnOrderDirection = (isset($_GET['order'][0]['dir']) && $_GET['order'][0]['dir'] == 'asc' ) ? 'asc' : 'desc';

        $result = array(
            'draw' => intval($_GET['draw']),
            'data' => array()
        );

        $result['recordsTotal'] = $result['recordsFiltered'] = Model_PermissionsEntity::find(array(
            'select' => 'count(id) count',
            'where' => ['type' => Model_Permission::TYPE_GROUP],
        ))[0]['count'];

        $queryData = array(
            'where' => 'type='. Model_Permission::TYPE_GROUP,
            'other' => 'ORDER BY name '.$columnOrderDirection.' LIMIT '.$start.', '.$length,
        );

        if (!empty($_GET['search']['value']))
        {
            $search = $_GET['search']['value'];

            $queryData['where'] .= ' AND name LIKE CONCAT(\'%\',:name,\'%\')';
            $queryData['bind']['name'] = $_GET['search']['value'];

            $queryData['select'] = 'count(id) count';

            $res = Model_PermissionsEntity::find($queryData);

            $result['recordsFiltered'] = !empty($res) ? $res[0]['count'] : 0;
            unset($queryData['select']);
        }

        $res = Model_PermissionsEntity::find($queryData, true);

        /** @var Model_PermissionsEntity $row */
        foreach ($res as $row)
        {
            $finalRow = array();

            $parents = '<i>No parents</i>';

            $groupName = $row->getName();

            if (isset($allParents[$groupName]))
            {
                $parents = [];

                foreach ($allParents[$groupName] as $parent)
                    $parents[] = '<a href="'.Oxygen_Utils::url('group-edit', [
                        'name' => rawurlencode($parent)
                    ]).'">'.$parent.'</a>';

                $parents = implode(', ', $parents);
            }

            $options = $row->getOptions();

            $options = isset($options[''])
                ? $options['']
                : []
            ;

            array_walk($options, function (&$v, $k) {
                $v = '<u>'.$k.'</u>: '.$v;
            });

            $groupEditUrl = Oxygen_Utils::url('group-edit', [
                'name' => rawurlencode($groupName)
            ]);

            $finalRow[] = '<a href="'.$groupEditUrl.'">'.$groupName.'</a>';
            $finalRow[] = !empty($options) ? implode('<br/>', $options) : '<i>No options</i>';
            $finalRow[] = $parents;

            $result['data'][] = $finalRow;
        }

        echo json_encode($result);
    }

    public function cloneAction()
    {
        $this->view->disableRender();

        if (PexAdmin_Acl::isAllowed('GROUP', 'CREATE') && $this->_request->isPost() && !empty($_POST['name']) && !empty($_POST['newName']))
        {
            $oldName = $_POST['name'];
            $newName = $_POST['newName'];

            if (Model_PermissionsEntity::getEntityByName($newName, Model_Permission::TYPE_GROUP))
                echo 'There is already an existing group with the specified name !';
            else
            {
                $group = Model_PermissionsEntity::getEntityByName($oldName, Model_Permission::TYPE_GROUP);

                if ($group)
                    echo $group->copy($newName);
                else
                    echo 'Unknown group !';
            }
        }
        else
            echo 'You are not allowed to do this !';
    }

    public function deleteAction()
    {
        $this->view->disableRender();

        if (PexAdmin_Acl::isAllowed('GROUP', 'DELETE') && $this->_request->isPost() && !empty($_POST['name']))
        {
            $name = $_POST['name'];

            $group = Model_PermissionsEntity::getEntityByName($name, Model_Permission::TYPE_GROUP);

            if ($group)
                echo $group->remove();
            else
                echo 'Unknown group !';
        }
        else
            echo 'You are not allowed to do this !';
    }
}
