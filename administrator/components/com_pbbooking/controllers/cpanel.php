<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 


class PbbookingControllerCpanel extends JControllerLegacy
{
    /**
     * Method to display the view
     *
     * @access    public
     */
    function display($cachable = false, $urlparams = array())
    {   
        $db = JFactory::getDbo();
        $config = $GLOBALS['com_pbbooking_data']['config'];

        $view = $this->getView('cpanel','html');

        //load some data for the view....
        $view->upcoming_events = $db->setQuery('select * from #__pbbooking_events where dtstart >= NOW() and verified = 1 and deleted = 0 order by dtstart ASC limit 10')->loadObjectList();

        $view->pending_events = $db->setQuery('select * from #__pbbooking_events where verified = 0 and deleted = 0 order by dtstart DESC limit 10')->loadObjectList();
        $view->last_syncs = $db->setQuery('select * from #__pbbooking_sync order by id DESC limit 10')->loadObjectList();
    
        //define dates for calendar util calc...
        if (date_create("now",new DateTimeZone(PBBOOKING_TIMEZONE))->format('w') == $config->calendar_start_day) {
            //we're at start of week....
            $view->dtstart = date_create("now",new DateTimeZone(PBBOOKING_TIMEZONE));
            $view->dtend = date_create("next week", new DateTimeZone(PBBOOKING_TIMEZONE));
        } else {
            //we're not at start of week..... let's go back to start of week....
            $view->dtstart = date_create("now",new DateTimeZone(PBBOOKING_TIMEZONE));
            if ($view->dtstart->format('w') > $config->calendar_start_day) {
                Purplebeanie\Util\Pbdebug::log_msg('Dashboard - current date > $config->caeldnar_start_day','com_pbbooking');
                $view->dtstart->modify('- '.($view->dtstart->format('w') - $config->calendar_start_day).' days');
                $view->dtend = date_create($view->dtstart->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
                $view->dtend->modify('+7 days');
            } else {
                Purplebeanie\Util\Pbdebug::log_msg('Dashboard - current date < $config->caeldnar_start_day','com_pbbooking');
                $view->dtend = date_create($view->dtstart->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
                $view->dtend->modify('+ '.($config->calendar_start_day - $view->dtstart->format('w')).' days');
                $view->dtstart = date_create($view->dtend->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
                $view->dtstart->modify('-7 days');
            }
        }

      

        //calc cal utilizations
        $view->cals = array();
        $cals = $db->setQuery('select * from #__pbbooking_cals')->loadObjectList();
        foreach ($cals as $i=>$cal) {
            $view->cals[$i] = new Pbbooking\Model\Calendar($config);
            $view->cals[$i]->loadCalendarFromDbase(array($cal->id),$view->dtstart,$view->dtend); 
            $view->cals[$i]->name = $cal->name;
        }



        //get the latest announcemenets into the view
        if ($config->disable_announcements != 1)
            $view->announcements = $this->_load_announcements();

        $view->config = $config;
        
        $view->display();
    }

    /**
    * loads the latest announcments of the purplebeanie.com website for display in the dashboard
    * @access private
    * @since 2.4.2
    */

    private function _load_announcements()
    {

        $announce_url = "http://www.purplebeanie.com/Announcements/feed/rss.html";

        $simplepie = new \SimplePie();

        $simplepie->set_feed_url($announce_url);
        $simplepie->init();

        return $simplepie->get_items(0,5);
    }
}

