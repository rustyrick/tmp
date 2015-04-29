<?php

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.formfield');

class JFormFieldPbdtpicker extends JFormField
{

    protected $type = 'Pbdtpicker';


    protected function getInput()
    {
        $doc = JFactory::getDocument();

        $class        = !empty($this->class) ? ' class="' . $this->class . '"' : '';
        $readonly     = $this->readonly ? ' readonly' : '';
        $disabled     = $this->disabled ? ' disabled' : '';
        $required     = $this->required ? ' required aria-required="true"' : '';

        // Initialize JavaScript field attributes.
        $onchange = !empty($this->onchange) ? ' onchange="' . $this->onchange . '"' : '';

        JHtml::_('jquery.framework');
        JHtml::_('script', 'system/html5fallback.js', false, true);
        JHtml::_('jquery.ui');
        Jhtml::_('script','com_pbbooking/jqueryui/ui/minified/jquery.ui.datepicker.min.js',true,true);
        Jhtml::_('script','com_pbbooking/jqueryui/ui/minified/jquery.ui.slider.min.js',true,true);
        Jhtml::_('script','com_pbbooking/jqueryui-timepicker-addon/src/jquery-ui-timepicker-addon.js',true,true);
        JHtml::_('stylesheet','com_pbbooking/jqueryui/themes/base/minified/jquery-ui.min.css',null,true);
        JHtml::_('stylesheet','com_pbbooking/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.css',null,true);
        $doc->addScriptDeclaration('jQuery(document).ready(function(){jQuery("#'.$this->id.'").datetimepicker(dtLocalisation);});');


        $html[] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '" value="'
            . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $disabled . $readonly . $onchange . $required . ' />';

        return implode($html);
    }

}