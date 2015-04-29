<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 



class PbbookingModelReminders extends JModelAdmin

{


    public function getForm($data= array(),$loadData = true)
    {
        $form = $this->loadForm('com_pbbooking.reminders','reminders',array('control' => 'jform', 'load_data' => $loadData));
        return $form;
    }



    public function getTable($type='Reminders',$prefix='PbbookingTable',$config= array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    protected function loadFormData() 
    {

        $data = JFactory::getApplication()->getUserState('com_pbbooking.edit.reminders.data',array());
        if (empty($data)) 
        {
                $data = $this->getItem();
        }
        return $data;
    }


    /**
     * need to extend the save function to add B/C
     */

    public function save($data)
    {
        $data['reminder_settings'] = json_encode(array('reminder_days_in_advance'=>$data['reminder_days_in_advance']));

        return parent::save($data);
    }

    /**
     * need to extend the getItem function fro B/c
     */

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        $reminder_settings = json_decode($item->reminder_settings,true);
        $item->reminder_days_in_advance = $reminder_settings['reminder_days_in_advance'];

        return $item;
    }


 

}