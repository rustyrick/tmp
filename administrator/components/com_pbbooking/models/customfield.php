<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 



class PbbookingModelCustomfield extends JModelAdmin

{
    public function __construct($config= array())
    {
        parent::__construct($config);
    }

    public function getForm($data= array(),$loadData = true)
    {
        $form = $this->loadForm('com_pbbooking.customfield','customfield',array('control' => 'jform', 'load_data' => $loadData));
        return $form;
    }

  

    public function getTable($type='Customfield',$prefix='PbbookingsTable',$config= array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    protected function loadFormData() 
    {

        $data = JFactory::getApplication()->getUserState('com_pbbooking.edit.customfield.data',array());
        if (empty($data)) 
        {
                $data = $this->getItem();
        }
        return $data;
    }



}