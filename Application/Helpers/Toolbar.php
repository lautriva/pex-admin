<?php
class Helper_Toolbar extends Helper
{
    function toolbar($barArray, $current)
    {
        $result = '';

        foreach($barArray as $key => $value)
        {
            if (isset($value['display_condition']) && empty($value['display_condition']))
                continue;

            $selected = ($current == $key);

            $result .= '
            <li'.( $selected?' class="active"':'').'>
                <a href="'. $value['link'].'">
                    '.$value['title'].'
                </a>
            </li>';
        }

        return $result;
    }
}