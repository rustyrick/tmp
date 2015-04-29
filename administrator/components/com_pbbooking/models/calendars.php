<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

class PbbookingModelCalendars extends JModelList
{

        protected function getListQuery()
        {
                // Create a new query object.           
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                // Select some fields from the hello table
                $query
                    ->select('*')
                    ->from('#__pbbooking_cals')
                    ->order('ordering DESC');
 
                return $query;
        }
}