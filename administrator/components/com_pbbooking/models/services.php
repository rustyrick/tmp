<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

class PbbookingModelServices extends JModelList
{

        protected function getListQuery()
        {
                // Create a new query object.           
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                // Select some fields from the hello table
                $query
                    ->select('*')
                    ->from('#__pbbooking_treatments')
                    ->order('ordering DESC');
 
                return $query;
        }

        /**
         * extends on parent to get linked calendars
         */

        public function getItems()
        {
            $items = parent::getItems();
            $db = JFactory::getDbo();

   
            foreach ($items as $item) {
                $item->cals = array();
                foreach (explode(',',$item->calendar) as $cal_id) {

                    $cal = $db->setQuery('select * from #__pbbooking_cals where id = '.(int)$cal_id)->loadObject();
                    if (isset($cal->id)) {
                        $item->cals[] = $cal;
                    }
                } 
            }
            
            return $items;
        }
}