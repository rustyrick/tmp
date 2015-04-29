<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 



class PbbookingModelSurveyconfig extends JModelAdmin

{


    public function getForm($data= array(),$loadData = true)
    {
        $form = $this->loadForm('com_pbbooking.surveyconfig','surveyconfig',array('control' => 'jform', 'load_data' => $loadData));
        return $form;
    }



    public function getTable($type='Surveyconfig',$prefix='PbbookingTable',$config= array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    protected function loadFormData() 
    {

        $data = JFactory::getApplication()->getUserState('com_pbbooking.edit.surveyconfig.data',array());
        if (empty($data)) 
        {
                $data = $this->getItem();
        }
        return $data;
    }


 

}