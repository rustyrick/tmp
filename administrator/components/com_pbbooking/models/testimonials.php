<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

class PbbookingModelTestimonials extends JModelList
{

        protected function getListQuery()
        {
                // Create a new query object.           
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                // Select some fields from the hello table
                $query
                    ->select('#__pbbooking_surveys.*,#__pbbooking_cals.name as cal_name,#__pbbooking_treatments.name as service_name,#__pbbooking_events.dtstart')
                    ->from('#__pbbooking_surveys')
                    ->join('left','#__pbbooking_events on #__pbbooking_events.id = #__pbbooking_surveys.event_id')
                    ->join('left','#__pbbooking_cals on #__pbbooking_cals.id = #__pbbooking_events.cal_id')
                    ->join('left','#__pbbooking_treatments on #__pbbooking_treatments.id = #__pbbooking_events.service_id')
                    ->order('#__pbbooking_surveys.id DESC');
 
                return $query;
        }
}