<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 


jimport('joomla.application.component.controlleradmin');

class PbbookingControllerCustomfields extends JControllerAdmin
{
    /**
     * this is needed for the calendars.delete to work
     */
    
    public function getModel($name = 'Customfield', $prefix = 'PbbookingModel',$config=array()) 
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }
}