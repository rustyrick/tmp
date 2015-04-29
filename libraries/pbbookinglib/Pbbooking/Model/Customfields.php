<?php


namespace Pbbooking\Model;
 
// No direct access
defined('_JEXEC') or die('Restricted access'); 


jimport('joomla.form.form');

class Customfields
{
    /**
     * converts the custom fields into a JForm object
     */

    public static function buildFormForCustomfields($addbsstyling=false)
    {   

        if (!isset($GLOBALS['com_pbbooking_data']['customfields'])) {
            throw new \Exception(JText::_('COM_PBBOOKING_MISSING_CUSTOMFIELDS_ERROR'));
        }

        $user = \JFactory::getUser();
        if ($user->id != 0) {
            //get the user fields
            $username = explode(' ', $user->name);
        }

        $customfields = $GLOBALS['com_pbbooking_data']['customfields'];

        //create the new form object
        $form = new \JForm('customfields');
        $formXml = new \SimpleXmlElement("<?xml version=\"1.0\" encoding=\"utf-8\"?><form></form>");

        $rootnode = $formXml->addChild('fields');
        foreach ($customfields as $field) {
            $childnode = $rootnode->addChild('field');
            
            //for BC need to convert select to list
            if ($field->fieldtype == 'select') $field->fieldtype = 'list';

            $childnode->addAttribute('type',$field->fieldtype);
            $childnode->addAttribute('required',$field->is_required);
            $childnode->addAttribute('name',$field->varname);
            $childnode->addAttribute('label',\Pbbooking\Pbbookinghelper::print_multilang_name($field,'customfield'));

            //now need to add options.
            if ($field->fieldtype == 'list' || $field->fieldtype == 'radio') {
                foreach (explode('|',$field->values) as $value) {
                    $option = $childnode->addChild('option',$value);
                    $option->addAttribute('value',$value);
                }
            }

            //add client side email validation & default if used is logged in
            if ($field->is_email) {
                $childnode->addAttribute('class','validate-email');
                $childnode->addAttribute('default',$user->email);
            }

            if (isset($username) && count($username)>0) {
                if ($field->is_first_name) $childnode->addAttribute('default',$username[0]);
                if ($field->is_last_name) $childnode->addAttribute('default',$username[1]);
            }

            //add some bootstrap styling
            if ($addbsstyling){
                $childnode->addAttribute('class','input-medium');
                $childnode->addAttribute('label-class','control-label');
            }
        }
        $form->load($formXml);
        return $form;
    }
}