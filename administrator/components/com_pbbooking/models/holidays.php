<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

class PbbookingModelHolidays extends JModelList
{

        protected function getListQuery()
        {
                // Create a new query object.           
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                $query
                    ->select('*')
                    ->from('#__pbbooking_block_days')
                    ->order('block_start_date ASC');
 
                return $query;
        }
}