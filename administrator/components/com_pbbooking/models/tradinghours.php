<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 



class PbbookingModelTradinghours extends JModelAdmin

{
    public function __construct($config= array())
    {
        parent::__construct($config);
    }


    public function getForm($data = array(), $loadData = true) 
    {
        return;
    }

    /**
     * the save function is overridden to just save a subset of the config fields
     */

    public function save($data)
    {
        $db = JFactory::getDbo();

        $updatedHours = new JObject(array(
                'trading_hours'=>$data['tradinghours'],
                'id'=>$GLOBALS['com_pbbooking_data']['config']->id,
                'time_groupings'=>$data['time_groupings']
            ));
        $db->updateObject('#__pbbooking_config',$updatedHours,'id');

        return true;
    }



    /**
     * completely overriding the default getItem
     */

    public function getItem($pk=null)
    {
        $db = JFactory::getDbo();
        $item = $db->setQuery('select `trading_hours`,`time_groupings` from `#__pbbooking_config`')->loadObject();
        return $item;
    }

}