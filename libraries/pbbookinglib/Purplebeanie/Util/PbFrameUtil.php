<?php

namespace Purplebeanie\Util;


class PbFrameUtil {

    static function setupPbFrame()
    {
        

        \JForm::addFieldPath(__DIR__ . '/../../fields');

    }

    /**
     * takes an array of fields and options and returns a jform object
     */

    public static function buildJFormForArray($fields = array(),$addbsstyling=true)
    {
        if (count($fields)==0)
            throw new Exception("Problem with Form", 1);

        //create the new form object
        $form = new \JForm('myform');
        $formXml = new \SimpleXmlElement("<?xml version=\"1.0\" encoding=\"utf-8\"?><form></form>");

        $rootnode = $formXml->addChild('fields');
        foreach ($fields as $field) {
            $childnode = $rootnode->addChild('field');
            
            //for BC need to convert select to list
            if ($field['type'] == 'select') $field['type'] = 'list';

            $childnode->addAttribute('type',$field['type']);
            $childnode->addAttribute('required',(isset($field['is_required'])) ? 1 : 0);
            $childnode->addAttribute('name',$field['name']);
            $childnode->addAttribute('label',$field['label']);

            //now need to add options.
            if ($field['type'] == 'list' || $field['type'] == 'radio') {
                foreach (explode('|',$field['values']) as $value) {
                    if (strpos($value, '=')) {
                        list($value,$option) = explode('=',$value);
                        $option = $childnode->addChild('option',$value);
                        $option->addAttribute('value',$value);
                    } else {
                        $option = $childnode->addChild('option',$value);
                        $option->addAttribute('value',$value);
                    }
                }
            }

            //add client side email validation & default if used is logged in
            if (isset($field['is_email']) && $field['is_email'] == 1)
                $childnode->addAttribute('class','validate-email');
    

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

?> 