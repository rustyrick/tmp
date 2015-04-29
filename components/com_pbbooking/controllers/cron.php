<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

jimport('joomla.crypt.crypt');



class PbbookingControllerCron extends JControllerLegacy
{
    
    /**
     * this is being called on a scheduled task. It needs to execute as quickly as possible as depending on the user settings it may very frequently called
     */

    public function sync()
    {
        
        $input = JFactory::getApplication()->input;
        $db = JFactory::getDbo();
        
        echo '<h2>Google Calendar Sync Scheduled Task</h2>';    
        
        // Make sure sync is enabled on this installed
        if (!isset($GLOBALS['com_pbbooking_data']['config']->enable_google_cal) || $GLOBALS['com_pbbooking_data']['config']->enable_google_cal == 0 )
        {
            die("Sync not enabled");
        }

        //make sure it's a legit request
        $google_cal_sync_secret = $input->get('google_cal_sync_secret',null,'string');
        if (!isset($GLOBALS['com_pbbooking_data']['config']->google_cal_sync_secret) || $GLOBALS['com_pbbooking_data']['config']->google_cal_sync_secret != $google_cal_sync_secret)
            die('Invalid secret');

        //we only want to make sure 1 instance of this is running and depending on user settings / network congestion there could be more
        $fp = fopen(JPATH_COMPONENT.DS.'lock.txt','r+');
        if (!flock($fp, LOCK_EX)) {
            //could not get exclusive access to lock file
            fclose($fp);
            die('Process already running');
        }
        echo '<p>Starting Sync....</p>';
        $syncer = new \Purplebeanie\Google\Syncer();
        $syncer->processEventQueue();

        $syncer->fetchExternalEvents();
        echo '<p>Ending Sync....</p>';
        flock($fp, LOCK_UN);
        fclose($fp); //don't forget to close the lock file on the way out.
    }

}