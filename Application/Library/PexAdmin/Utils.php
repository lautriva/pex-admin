<?php
class PexAdmin_Utils
{
    /**
     * Check if application is configured properly and all data is available
     * @param string $message
     * @return boolean
     *      true: if all is ok
     *      false: an error occured, more info is put in $message var
     */
    public static function checkState(&$message = null)
    {
        $result = true;
        $message = 'No error';

        try
        {
            // Run fake requests
            Model_Permission::getPermissions('test', Model_Permission::TYPE_GROUP);
            Model_PermissionsEntity::getEntitiesByType(Model_Permission::TYPE_GROUP);
            Model_PermissionsInheritance::getGroupsParents();
        }
        catch (Exception $e)
        {
            $result = false;
            $message = $e->getMessage();
        }

        return $result;
    }

    // Adapted from http://www.minecraftforum.net/forums/mapping-and-modding-java-edition/minecraft-tools/1264944
    public static function convertMinetextToWeb($minetext)
    {
        preg_match_all ("/[^§&]*[^§&]|[§&][0-9a-z][^§&]*/", $minetext, $brokenupstrings);
        $returnstring = "";
        foreach ($brokenupstrings as $results)
        {
            $ending = '';
            foreach ($results as $individual)
            {
                $code = preg_split ("/[&§][0-9a-z]/", $individual);
                preg_match ("/[&§][0-9a-z]/", $individual, $prefix);
                if (isset ($prefix [0]))
                {
                    $actualcode = substr ($prefix [0], 1);
                    switch ($actualcode)
                    {
                        case "1" :
                            $returnstring = $returnstring . '<span style="color:#0000AA">';
                            $ending = $ending . "</span>";
                            break;
                        case "2" :
                            $returnstring = $returnstring . '<span style="color:#00AA00">';
                            $ending = $ending . "</span>";
                            break;
                        case "3" :
                            $returnstring = $returnstring . '<span style="color:#00AAAA">';
                            $ending = $ending . "</span>";
                            break;
                        case "4" :
                            $returnstring = $returnstring . '<span style="color:#AA0000">';
                            $ending = $ending . "</span>";
                            break;
                        case "5" :
                            $returnstring = $returnstring . '<span style="color:#AA00AA">';
                            $ending = $ending . "</span>";
                            break;
                        case "6" :
                            $returnstring = $returnstring . '<span style="color:#FFAA00">';
                            $ending = $ending . "</span>";
                            break;
                        case "7" :
                            $returnstring = $returnstring . '<span style="color:#AAAAAA">';
                            $ending = $ending . "</span>";
                            break;
                        case "8" :
                            $returnstring = $returnstring . '<span style="color:#555555">';
                            $ending = $ending . "</span>";
                            break;
                        case "9" :
                            $returnstring = $returnstring . '<span style="color:#5555FF">';
                            $ending = $ending . "</span>";
                            break;
                        case "a" :
                            $returnstring = $returnstring . '<span style="color:#55FF55">';
                            $ending = $ending . "</span>";
                            break;
                        case "b" :
                            $returnstring = $returnstring . '<span style="color:#55FFFF">';
                            $ending = $ending . "</span>";
                            break;
                        case "c" :
                            $returnstring = $returnstring . '<span style="color:#FF5555">';
                            $ending = $ending . "</span>";
                            break;
                        case "d" :
                            $returnstring = $returnstring . '<span style="color:#FF55FF">';
                            $ending = $ending . "</span>";
                            break;
                        case "e" :
                            $returnstring = $returnstring . '<span style="color:#FFFF55">';
                            $ending = $ending . "</span>";
                            break;
                        case "f" :
                            $returnstring = $returnstring . '<span style="color:#FFFFFF">';
                            $ending = $ending . "</span>";
                            break;
                        case "l" :
                            if (strlen ($individual) > 2) {
                                $returnstring = $returnstring . '<span style="font-weight:bold;">';
                                $ending = "</span>" . $ending;
                                break;
                            }
                        case "m" :
                            if (strlen ($individual) > 2) {
                                $returnstring = $returnstring . '<strike>';
                                $ending = "</strike>" . $ending;
                                break;
                            }
                        case "n" :
                            if (strlen ($individual) > 2) {
                                $returnstring = $returnstring . '<span style="text-decoration: underline;">';
                                $ending = "</span>" . $ending;
                                break;
                            }
                        case "o" :
                            if (strlen ($individual) > 2) {
                                $returnstring = $returnstring . '<i>';
                                $ending = "</i>" . $ending;
                                break;
                            }
                        case "r" :
                            $returnstring = $returnstring . $ending;
                            $ending = '';
                            break;
                    }
                    if (isset ($code [1])) {
                        $returnstring = $returnstring . $code [1];
                        if (isset ($ending) && strlen ($individual) > 2) {
                            $returnstring = $returnstring . $ending;
                            $ending = '';
                        }
                    }
                } else {
                    $returnstring = $returnstring . $individual;
                }
            }
        }

        return $returnstring;
    }
}
