<?php
namespace Library\Custom\View\Helper;

class OptionValues extends  HelperAbstract
{

    public function optionValues($options, $selected)
    {
        if (!is_array($selected)) {
            $selected = array($selected);
        }

        $values = array();
        foreach ($selected as $key) {
            if (isset($options[$key])) {
                $values[] = $options[$key];
            }
        }

        return $values;
    }
}