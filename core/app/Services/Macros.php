<?php
namespace App\Services;
use Collective\Html\FormBuilder;
use Illuminate\Support\Facades\Request;

class Macros extends FormBuilder
{
    public function menu_active($route)
    {
        if (Request::is($route . '/*') OR Request::is($route)) {
            $active = "active";
        } else {
            $active = '';
        }
        return $active;
    }

    public function customSelect($name, $list = array(), $selected = null, $options = array())
    {
        $selected = $this->getValueAttribute($name, $selected);
        $options['id'] = $this->getIdAttribute($name, $options);
        if (!isset($options['name'])) $options['name'] = $name;
        $html = array();
        foreach ($list as $list_el) {
            $selected_attr = e($list_el['value']) == $selected ? 'selected' : '';
            $option_attr = array('value' => e($list_el['value']), $selected_attr != '' ? $selected_attr : null, 'data-value' => $list_el['data-value']);
            $html[] = '<option' . $this->html->attributes($option_attr) . '>' . e($list_el['display']) . '</option>';
        }
        $options = $this->html->attributes($options);
        $list = implode('', $html);
        return "<select{$options}>{$list}</select>";
    }
}