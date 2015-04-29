<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

class PbbookingModelReports extends JModelLegacy
{

    /**
     * builds up the reports for the display and returns an assoc array with key=>data (array)
     */

    public function getItems()
    {
        $db = \JFactory::getDbo();

        $reports = array();
        $reports['last_10_validated'] = array();
        $reports['last_10_new'] = array();

        $last_10_validated = $db->setQuery('select * from #__pbbooking_events where verified = 1 order by id DESC limit 10')->loadObjectList(); 
        foreach ($last_10_validated as $validated)
        {
            $reports['last_10_validated'][] = new \Pbbooking\Model\Event($validated->id);
        }

        $last_10_new = $db->setQuery('select distinct(email),id from #__pbbooking_events where verified = 1 order by id DESC limit 10')->loadObjectList();
        foreach ($last_10_new as $new) {
            $reports['last_10_new'][] = new \Pbbooking\Model\Event($new->id);
        }
        return $reports;
    }

}