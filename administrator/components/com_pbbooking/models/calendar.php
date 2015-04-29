<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

jimport('joomla.crypt.crypt');


class PbbookingModelCalendar extends JModelAdmin

{
    public function __construct($config= array())
    {
        parent::__construct($config);
    }

    public function getForm($data= array(),$loadData = true)
    {
        $form = $this->loadForm('com_pbbooking.calendar','calendar',array('control' => 'jform', 'load_data' => $loadData));
        return $form;
    }

    /**
     * the save function is overridden to get the token information based on the user linking the calendar
     */

    public function save($data)
    {


    
        return parent::save($data);
    }

    public function getTable($type='Calendar',$prefix='PbbookingsTable',$config= array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    protected function loadFormData() 
    {

        $data = JFactory::getApplication()->getUserState('com_pbbooking.edit.calendar.data',array());
        if (empty($data)) 
        {
                $data = $this->getItem();
        }
        return $data;
    }


    /**
     * extending the default getItem method to descrypt the google account password and pull the default trading hours.
     */

    public function getItem($pk=null)
    {
        $item = parent::getItem($pk);

        if (!isset($item->hours)) {
            //the item has no trading hours so set based on the default.
            $item->hours = $GLOBALS['com_pbbooking_data']['config']->trading_hours;
        }
        return $item;
    }

}