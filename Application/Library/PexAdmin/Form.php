<?php
class PexAdmin_Form extends Oxygen_Form
{
    public function getInput($name = '', $type = 'text', $params = array())
    {
        $paramsInput = $this->_addCommonAttributes($params);
        $paramsInput['class'] .= ' form-control';

        unset($paramsInput['caption']);

        return $this->_getFormComponent
        (
            $name,
            parent::getInput($name, $type, $paramsInput),
            $params
        );
    }

    public function getTextarea($name = '', $rows='4', $params = array())
    {
        $paramsInput = $this->_addCommonAttributes($params);
        $paramsInput['class'] .= ' form-control';

        unset($paramsInput['caption']);

        return $this->_getFormComponent
        (
            $name,
            parent::getTextarea($name, $rows, $paramsInput),
            $params
        );
    }

    public function getSelect($name = null, $options = array(), $params = array(), $select2Config = array())
    {
        $paramsInput = $this->_addCommonAttributes($params);
        $paramsInput['class'] .= ' form-control';

        $paramsInput['class'] .= ' select2';

        unset($paramsInput['caption']);

        $defaultSelect2Config = array(
            'width' => '100%',
            'theme' => 'bootstrap',
            'placeholder' => isset($params['placeholder']) ? $params['placeholder'] : ''
        );

        if (isset($params['value']))
        {
            foreach ($options as $i => $option)
            {
                if (isset($option['value']) && $option['value'] == $params['value'])
                    $options[$i]['selected'] = 'selected';
            }

            unset($params['value']);
        }

        $select2Config = array_merge($defaultSelect2Config, $select2Config);

        $initScript = '
        <script>
        $(document).ready(function()
        {
            $("#'.$name.'").select2('.json_encode($select2Config).');
        });
        </script>';

        return $this->_getFormComponent
        (
            $name,
            parent::getSelect($name, $options, $paramsInput),
            $params
        ).$initScript;
    }

    public function getSwitchery($name = '', $params = array())
    {
        $params = $this->_addCommonAttributes($params);

        $caption = isset($params['caption'])
            ? $params['caption']
            : '';
        unset($params['caption']);

        $checkbox = parent::getInput($name, 'checkbox', $params);

        $switchery = '<div class="switch">
          '.$checkbox.'
          <div class="slider"></div>
        </div>';

        return $this->_getFormComponent
        (
            $name,
            $switchery,
            array(
                'caption' => $caption
            )
        );
    }

    public function getButton($caption = '', $action='', $params = array())
    {
        $params = $this->_addCommonAttributes($params);

        // default button class
        if (empty($params['class']) && isset($params['type']) && $params['type'] == 'submit')
            $params['class'] = 'btn btn-primary';
        else if (empty($params['class']))
            $params['class'] = 'btn btn-default';
        else
            $params['class'] .= ' btn';

        return parent::getButton($caption, $action, $params);
    }

    // Todo: avoid code duplication with simple select
public function getAjaxSelect($name = null, $url, $options = array(), $params = array(), $select2Config = array())
    {
        $params = $this->_addCommonAttributes($params);
        $params['class'] .= ' form-control';

        $params['class'] .= ' select2';

        $defaultSelect2Config = array(
            'theme' => 'bootstrap',
            'placeholder' => isset($params['placeholder']) ? $params['placeholder'] : ''
        );

        $select2Config = array_merge($defaultSelect2Config, $select2Config);

        $initScript = '
        <script>
        $(document).ready(function()
        {
            var select2Config = '.json_encode($select2Config).';

            $("#'.$name.'").select2($.extend({}, select2Config,
                {
                    ajax :
                    {
                        "url" : "'.$url.'",
                        "dataType": "json",
                        "processResults": function (data, page) {
                            return {
                                results: data
                            };
                        },
                        formatResult: function (option) {
                            return option.text;
                        },
                        formatSelection: function (option) {
                            return option.text;
                        }

                    }
                }
            ));
        });
        </script>';

        // remove the spaces / line feeds
        $initScript = preg_replace('/\s+/', ' ', $initScript);

        return $this->_getFormComponent
        (
            $name,
            parent::getSelect($name, $options, $params),
            $params
        ).$initScript;
    }

    // Utils functions
    // ---------------------------------------------------------------------

    private function _getFormComponent($name, $inputContents, $params)
    {
        $labelContents = isset($params['caption'])
            ? '<label for="'.$name.'">'.$params['caption'].'</label>'
            : '';
        unset($params['caption']);

        return '
        <div class="form-group">
            '.$labelContents.'
            '.$inputContents.'
        </div>';
    }

    private function _addCommonAttributes(&$params)
    {
        // add class attribute
        if (!isset($params['class']))
            $params['class'] = '';

        // Add readonly attribute if readonly is true
        if (!empty($params['readonly']))
            $params['readonly'] = 'readonly';
        else
            unset($params['readonly']);

        // Add disabled attribute if disabled is true
        if (!empty($params['disabled']))
            $params['disabled'] = 'disabled';
        else
            unset($params['disabled']);

        // Add checked attribute if checked is true
        if (!empty($params['checked']))
            $params['checked'] = 'checked';
        else
            unset($params['checked']);

        // Add required attribute if required is true
        if (!empty($params['required']))
            $params['required'] = 'required';
        else
            unset($params['required']);

        return $params;
    }
}