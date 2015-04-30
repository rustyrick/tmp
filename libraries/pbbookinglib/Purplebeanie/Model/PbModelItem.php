<?php

namespace Purplebeanie\Model;

class PbmodelItem extends \JModelAdmin

{


	public function buildForm($fields = array(),$name='')
	{
		//load and parse the custom fields form.
		$formxml= new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\"?><form></form>");	//holds the formxml
		foreach ($fields as $field) {
			$newnode = $formxml->addChild('field');
			$newnode->addAttribute('name',$field['name']);
			$newnode->addAttribute('type',$field['type']);
			$newnode->addAttribute('label',$field['label']);
			$newnode->addAttribute('required',$field['required']);
			$newnode->addAttribute('labelclass','control-label');
			if (isset($field['class']))
				$newnode->addAttribute('class',$field['class']);
			if (isset($field['default']))
				$newnode->addAttribute('default',$field['default']);
			if (isset($field['readonly']))
				$newnode->addAttribute('readonly','true');
			if (isset($field['value']))
				$newnode->addAttribute('value',$field['value']);
			if (in_array($field['type'],array('list','checkboxes','radio','select')) ) {
				//need to add some options.
				$options = explode('|',$field['options']);
				foreach ($options as $option)
					$newnode->addChild('option',$option)->addAttribute('value',$option);
			}
		}

		//create a form from this.
		$cfform = new \JForm($name);
		$cfform->load($formxml);
		return $cfform;
	}

	public function getForm($data = Array(), $loadData = true)
	{}


}

?>