<?php
class Oxygen_Form
{
    public function getInput($name = null, $type = 'text', $params = array())
    {
        $defaultParams = array(
            'type' => $type,
            'id' => $name,
            'name' => $name,
        );

        // Merge provided params with defaults ones
        $params = array_merge($defaultParams, $params);

        $result = '<input';
        foreach ($params as $key => $value)
        {
            // Sanitize the value
            $value = $this->filterParam($value, $key);

            $result .= ' '.$key.'="'.$value.'"';
        }
        $result .= '/>';

        return $result;
    }

    public function getTextarea($name = null, $rows='4', $params = array())
    {
        $defaultParams = array(
            'id' => $name,
            'name' => $name,
            'rows' => $rows,
        );

        // Merge provided params with defaults ones
        $params = array_merge($defaultParams, $params);

        // In textarea, value must be between tags
        $defaultValue = isset($params['value']) ? $params['value'] : '';
        unset($params['value']);

        // Create the markup
        $result = '<textarea';
        foreach ($params as $key => $value)
        {
            // Sanitize the value
            $value = $this->filterParam($value, $key);

            $result .= ' '.$key.'="'.$value.'"';
        }
        $result .= '>';
        $result .= $defaultValue;
        $result .= '</textarea>';

        return $result;
    }

    public function getSelect($name = null, $options = array(), $params = array())
    {
        $defaultParams = array(
            'id' => $name,
            'name' => $name
        );

        // Merge provided params with defaults ones
        $params = array_merge($defaultParams, $params);

        $optionsData = '';
        foreach ($options as $option)
        {
            $optionsData .= '<option';

            // TODO: throw exception if $option is not like array('value' => XXX, 'caption' => YYY)

            foreach ($option as $key => $value)
            {
                if ($key == 'caption')
                    continue;

                // Sanitize the value
                $value = $this->filterParam($value, $key);

                $optionsData .= ' '.$key.'="'.$value.'"';
            }

            $optionsData .= '>';
            if (isset($option['caption']))
                $optionsData .= $option['caption'];
            $optionsData .= '</option>';
        }

        // Create the markup
        $result = '<select';
        foreach ($params as $key => $value)
        {
            // Sanitize the value
            $value = $this->filterParam($value, $key);

            $result .= ' '.$key.'="'.$value.'"';
        }
        $result .= '>';
        $result .= $optionsData;
        $result .= '</select>';

        return $result;
    }

    public function getButton($caption = '', $action=null, $params = array())
    {
        if (!empty($action))
        {
            $params = array_merge(array('onclick' => $action, 'type' => 'button'), $params);
        }

        // Create the markup
        $result = '<button';
        foreach ($params as $key => $value)
        {
            // Sanitize the value
            $value = $this->filterParam($value, $key);            

            $result .= ' '.$key.'="'.$value.'"';
        }
        $result .= '>';
        $result .= $caption;
        $result .= '</button>';

        return $result;
    }

    public function getSubmitButton($caption = '', $params = array())
    {
        $defaultParams = array(
            'type' => 'submit'
        );

        // Merge provided params with defaults ones
        $params = array_merge($defaultParams, $params);

        return $this->getButton($caption, null, $params);
    }

    protected function filterParam($value, $paramName)
    {
        return htmlEntities($value, ENT_QUOTES, 'UTF-8');
    }
}