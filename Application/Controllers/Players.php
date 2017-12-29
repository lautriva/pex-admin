<?php

class PlayersController extends Controller
{
    public function init()
    {
        if (!PexAdmin_Acl::isAllowed('PLAYER', 'READ'))
            Oxygen_Utils::redirect(Oxygen_Utils::url('home'));

        $this->view->toolbarCurrent = 'players';
    }

    public function indexAction()
    {
        $this->view->title = 'PEX Players list';
    }

    public function editAction()
    {
        $playerName = $this->getRequest()->getParam('name');
        $playerName = urldecode($playerName);

        $oPlayer = false;

        $canCreate = PexAdmin_Acl::isAllowed('PLAYER', 'CREATE');
        $canEdit = PexAdmin_Acl::isAllowed('PLAYER', 'UPDATE');

        if (!empty($playerName))
            $oPlayer = Model_PermissionsEntity::getEntityByName($playerName, Model_Permission::TYPE_PLAYER);

        if (!$oPlayer && !$canCreate)
            Oxygen_Utils::redirect(Oxygen_Utils::url('player-index'));
        else if (!$oPlayer)
            $oPlayer = new Model_PermissionsEntity();

        $isCreate = empty($oPlayer->getId());

        if (($isCreate && $canCreate || $canEdit) && $this->getRequest()->isPost())
        {
            $oPlayer = Model_PermissionsEntity::setGroupData($_POST, $isCreate, Model_Permission::TYPE_PLAYER);

            Oxygen_Utils::redirect(Oxygen_Utils::url('player-edit', [
                'name' => rawurlencode($oPlayer->getName())
            ]));
        }

        $this->view->oPlayer = $oPlayer;
        $this->view->parents = Model_PermissionsInheritance::getGroupsParents();
    }

    public function removeAction()
    {
        $playerName = $this->getRequest()->getParam(0);
        Model_PermissionsEntity::remove($playerName);
    }

    public function listAjaxAction()
    {
        $this->view->disableRender();

        $type = $this->getRequest()->getParam('type', Model_Permission::TYPE_PLAYER);

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

        $entitiesTableName = Model_Permission::getTableName('permissions_entity');

        $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $length = isset($_GET['length']) ? intval($_GET['length']) : 10;

        $columnOrderDirection = (isset($_GET['order'][0]['dir']) && $_GET['order'][0]['dir'] == 'asc' ) ? 'asc' : 'desc';

        $result = array(
            'draw' => intval($_GET['draw']),
            'data' => array()
        );

        $result['recordsTotal'] = $result['recordsFiltered'] = Model_PermissionsEntity::find(array(
            'select' => 'count(id) count',
            'where' => ['type' => Model_Permission::TYPE_PLAYER],
        ))[0]['count'];

        $queryData = array(
            'where' => 'type=' . Model_Permission::TYPE_PLAYER,
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

            $playerUuid = $row->getName();

            if (isset($allParents[$playerUuid]))
            {
                $parents = [];

                foreach ($allParents[$playerUuid] as $parent)
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

            $playerName = isset($options['name'])
                ? $options['name']
                : 'N/A'
            ;

            array_walk($options, function (&$v, $k) {
                $v = '<u>'.$k.'</u>: '.$v;
            });

              $playerEditUrl = Oxygen_Utils::url('player-edit', [
                'name' => rawurlencode($playerUuid)
            ]);

            $tooltip = 'title="Last known playername: '.$playerName.'"';

            $finalRow[] = '<a href="'.$playerEditUrl.'" '.$tooltip.'>'.$playerUuid.'</a>';
            $finalRow[] = !empty($options) ? implode('<br/>', $options) : '<i>No options</i>';
            $finalRow[] = $parents;

            $result['data'][] = $finalRow;
        }

        echo json_encode($result);

    }

    public function cloneAction()
    {
        $this->view->disableRender();

        if (PexAdmin_Acl::isAllowed('PLAYER', 'CREATE') && $this->_request->isPost() && !empty($_POST['name']) && !empty($_POST['newName']))
        {
            $oldName = $_POST['name'];
            $newName = $_POST['newName'];

            if (Model_PermissionsEntity::getEntityByName($newName, Model_Permission::TYPE_PLAYER))
                echo 'There is already an existing player with the specified name !';
            else
            {
                $group = Model_PermissionsEntity::getEntityByName($oldName, Model_Permission::TYPE_PLAYER);

                if ($group)
                    echo $group->copy($newName);
                else
                    echo 'Unknown player !';
            }
        }
        else
            echo 'You are not allowed to do this !';
    }

    public function deleteAction()
    {
        $this->view->disableRender();

        if (PexAdmin_Acl::isAllowed('PLAYER', 'DELETE') && $this->_request->isPost() && !empty($_POST['name']))
        {
            $name = $_POST['name'];

            $player = Model_PermissionsEntity::getEntityByName($name, Model_Permission::TYPE_PLAYER);

            if ($player)
                echo $player->remove();
            else
                echo 'Unknown player !';
        }
        else
            echo 'You are not allowed to do this !';
    }
}
