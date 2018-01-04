<?php

class IndexController extends Controller
{
    // Freely inspired by PEXI
    public function indexAction()
    {
        $this->view->title = 'PEX Permissions overview';

        $groups = Model_PermissionsEntity::find(['other' => 'order by type, name'], true);

        $pexData = [];

        // Random player names used to preview chat prefixes / suffixes
        $samplePlayernames = ['Notch', 'jeb_', 'Dinnerbone', 'Tobias', 'David', 'Tiffany', 'Clow'];
        $randomPlayerCount = count($samplePlayernames);
        $currentPlayer = 0;

        /** @var Model_PermissionsEntity $group */
        foreach ($groups as $group)
        {
            $row = [
                'name' => $group->getName(),
                'type' => $group->getType()
            ];

            $options = $group->getOptions();


            $hasChatRender = false;

            // Simulate chat render of player name
            $samplePlayername = $samplePlayernames[$currentPlayer];

            if (isset($options['']['prefix']))
            {
                $hasChatRender = true;
                $samplePlayername = $options['']['prefix'].$samplePlayername;
            }

            if (isset($options['']['suffix']))
            {
                $hasChatRender = true;
                $samplePlayername .= $options['']['suffix'];
            }

            if ($hasChatRender)
            {
                $row['render'] = PexAdmin_Utils::convertMinetextToWeb($samplePlayername);

                $currentPlayer++;
                $currentPlayer %= $randomPlayerCount;
            }

            $row['id'] = $row['name']; // To display edit page

            if ($group->getType() == Model_Permission::TYPE_GROUP)
            {
                if (isset($options['']['rank']))
                    $row['rank'] = $options['']['rank'];

                if (isset($options['']['rank-ladder']))
                    $row['rank-ladder'] = $options['']['rank-ladder'];
            }
            else
            {
                // Show player name with the uuid
                if (isset($options['']['name']))
                {
                    $row['uuid'] = $row['name'];
                    $row['name'] = $options['']['name'];
                }
            }

            $row['permissions'] = [];
            $permissions = Model_Permission::getPermissions($group->getName(), $group->getType());
            foreach ($permissions as $wordName => $worldData)
            {
                foreach ($worldData as $node)
                {
                    $row['permissions'][] = [
                        'world' => $wordName,
                        'node' => $node
                    ];
                }
            }

            $pexData[] = $row;
        }

        $this->view->pexData = $pexData;
    }
}
