<?php

namespace Purplebeanie\Google;


// No direct access
 
defined('_JEXEC') or die('Restricted access');

jimport('cms.component.helper');

class Syncer
{



    /**
     * This stores a reference to the \Google_Client object
     */

    private $googleClient;


    public function __construct()
    {
    }

    /**
     * adds an event to the sync queue with a specified action.
     */

    public static function addEventToQueue($event,$action)
    {
        //need to check if the evnet should be added (ie. is it created on a calendar that has google cal support)?
        if (!isset($GLOBALS['com_pbbooking_data']['calendars'][$event->cal_id]->enable_google_cal) || $GLOBALS['com_pbbooking_data']['calendars'][$event->cal_id]->enable_google_cal == 0)
            return true;                //doesn't need to be added to the queue

        $db = \JFactory::getDbo();
        $db->insertObject('#__pbbooking_sync',new \JObject(array(
                'date_added'=>date_create("now",new \DateTimeZone(PBBOOKING_TIMEZONE))->format(DATE_ATOM),
                'action'=>$action,
                'data'=>json_encode($event)
            )));
        return true;
    }

    /**
     * processes the sync queue
     */

    public function processEventQueue()
    {
        if (!$this->googleClient)
        {
            try 
            {
                $this->googleClient = $this->createGoogleConnection();    
            } 
            catch (\Exception $e) 
            {
                $this->syncProblemNotification($e->getMessage());
                die($e->getMessage());
            }
            
        }

        $db = \JFactory::getDbo();

        $query = $db->getQuery(true);
        $query->select('*')->from('#__pbbooking_sync')->where('status is null')->order('id ASC');
        $events = $db->setQuery($query)->loadObjectList();

        foreach ($events as $event)
        {
            $success = false;
            switch ($event->action)
            {
                case 'create':
                    $success = $this->sendEvent($event);
                    break;
                case 'delete':
                    $success = $this->deleteEvent($event);
                    break;
            }

            //update the error flag so that it can be reported on in the admin console.
            $event->status = ($success) ? 'success' : 'error';
            if ($success)
                echo '<br/>Event '.$event->action.' success';
            else
                echo '<br/>Event '.$event->action.' failed';
            
            $db->updateObject('#__pbbooking_sync',$event,'id');
        }
    }

    /**
     * sends an event to the connected google calendar
     *
     * @param    Object    $event    The event as pulled from the sync queue
     *
     * @return   Boolean   True on success of false on failure.
     */

    private function sendEvent($event)
    {

        if (!$this->googleClient)
        {
            throw new \Exception("No active connection to Google found.", 1);
            
        }

        $db = \JFactory::getDbo();

        // Just extract the event's data and calendar details
        $data = json_decode($event->data,true);
        $cal = $GLOBALS['com_pbbooking_data']['calendars'][$data['cal_id']];

        // Set the \Google_Service service
        $service = new \Google_Service_Calendar($this->googleClient);

        // Event will now be overwritten with the new \Google_Service_Calendar_Event object
        $event = new \Google_Service_Calendar_Event;
        $event->setSummary($data['summary']);

        $start = new \Google_Service_Calendar_EventDateTime();
        $start->setDateTime(date_create($data['dtstart']['date'],new \DateTimeZone(PBBOOKING_TIMEZONE))->format(DATE_RFC3339));
        $event->setStart($start);

        $end = new \Google_Service_Calendar_EventDateTime();
        $end->setDateTime(date_create($data['dtend']['date'],new \DateTimeZone(PBBOOKING_TIMEZONE))->format(DATE_RFC3339));
        $event->setEnd($end);

        $createdEvent = $service->events->insert(trim($cal->gcal_id), $event);

        if ($createdEvent)
        {
            // Update the gcalid of the event
            $db->updateObject('#__pbbooking_events',new \JObject(array(
                    'id'    =>    $data['id'],
                    'gcal_id'    =>    $createdEvent->getId()
                )),'id');

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * deletes an event from the connected google calendar
     *
     * @param    Object    $event    The event object from the sync queue
     *
     * @return   Boolean   True or false for success or failure.
     */

    private function deleteEvent($event)
    {
        $data = json_decode($event->data,true);

        //now we need to see what the actual event gcal_id is as the event data may not have a current google cal if the event
        //was moved or deleted etc.
        //but it could also have been moved to a calendar that no longer has gcal or created and have a gcal already

        /*
            use case 1: the event has previously been created and synced but is now being deleted means there will be an gcal id
                        in the json encoded event data BUT the cal_id could be wrong in the event. it will be correct in teh data

            use case 2: the event has previously been created but was only synced in the current sync run in which case the event
                        object will hold the gcal_id. the cal_id would be correct in the event object and in the data.

            SOLUTION: if there is no gcal_id in the event data then pull the gcal_id from the event object BUT ALWAYS get the 
                        event cal id from the event data

            */



        $evObj = new \Pbbooking\Model\Event($data['id']);
        $event_gcal_id = (isset($data['gcal_id']) && $data['gcal_id'] != '') ? $data['gcal_id'] : $evObj->gcal_id;

        if (!$event_gcal_id || $event_gcal_id == '')
            return false;               //can't delete an event with no gcal id

        //now we are assuming we have a gcalid as $event_gcal_id
        $service = new \Google_Service_Calendar($this->googleClient);
        $cal = $GLOBALS['com_pbbooking_data']['calendars'][$evObj->cal_id];

        $service->events->delete(trim($cal->gcal_id),$event_gcal_id);

        return true;
    }

    /**
     * this will just loop through the calendars and pull in all external events that may be there.
     */

    public function fetchExternalEvents()
    {
        $db = \JFactory::getDbo();
        $config = $GLOBALS['com_pbbooking_data']['config'];

        foreach ($GLOBALS['com_pbbooking_data']['calendars'] as $cal)
        {
            if ( isset($cal->enable_google_cal) && $cal->enable_google_cal == 1 )
            {
                // This has a google cal and should get external events.
                $service = new \Google_Service_Calendar($this->googleClient);

                // Get the date range
                $dtfrom = date_create("now", new \DateTimeZone(PBBOOKING_TIMEZONE));
                $dtto = date_create("now", new \DateTimeZone(PBBOOKING_TIMEZONE))->modify('+ ' . $config->sync_future_events . 'months');

                $params = array(
                        'timeMin'       =>    $dtfrom->format(DATE_ATOM),
                        'timeMax'       =>    $dtto->format(DATE_ATOM),
                        'maxResults'    =>    $config->google_max_results
                    );

                $googleEvents = $service->events->listEvents(trim($cal->gcal_id),$params);

                // Now loop through the events

                //let's first of all the gcalids of the external events then I can unset the element to be left with an array
                //of events that USED to be in the goolge cal but aren't any more.  Then I can just deleted.
                $cur_externals = $db->setQuery('select gcal_id from #__pbbooking_events where cal_id = ' . (int)$cal->id.' and externalevent = 1')->loadColumn();
                $real_externals = array();

                foreach ($googleEvents as $gEvent)
                {

                    //first check to see if the event with that gcal_id exists
                    $db_event = $db->setQuery('select * from #__pbbooking_events where gcal_id = "'.$db->escape($gEvent->getId()).'"')->loadObject();
                    //if it exists check to see if it is "owned" by externalevent
                    if ($db_event && isset($db_event->externalevent) && $db_event->externalevent == 1)
                    {
                        //if it is owned by externalevent then update in the database
                        $db_event->summary = $gEvent->getSummary();
                        $db_event->dtend = date_create($gEvent->getEnd()->getDateTime(),new \DateTimeZone(PBBOOKING_TIMEZONE))->format(DATE_ATOM);
                        $db_event->dtstart = date_create($gEvent->getStart()->getDateTime(),new \DateTimeZone(PBBOOKING_TIMEZONE))->format(DATE_ATOM);
                        $db->updateObject('#__pbbooking_events',$db_event,'id');

                        $real_externals[] = $gEvent->getId();

                        echo '<br/>External event updated succesfully';
                    }   

                    //if it's not ownedby externalevent then we don't need to do anything as it's owned by pbbooking
                    
                
                    if (!$db_event)
                    {
                        //else it doesn't exist in the database so we need to create    
                        $new_event = array(
                                'cal_id'           =>    $cal->id,
                                'summary'          =>    $gEvent->getSummary(),
                                'dtend'            =>    date_create($gEvent->getEnd()->getDateTime(),new \DateTimeZone(PBBOOKING_TIMEZONE))->format(DATE_ATOM),
                                'dtstart'          =>    date_create($gEvent->getStart()->getDateTime(),new \DateTimeZone(PBBOOKING_TIMEZONE))->format(DATE_ATOM),
                                'verified'         =>    1,
                                'externalevent'    =>    1,
                                'gcal_id'          =>    $gEvent->getId()
                            );
                        $db->insertObject('#__pbbooking_events',new \JObject($new_event),'id');

                        echo '<br/>External event created succesfully';
                    }
                }

                $stale_externals = array_diff($cur_externals,$real_externals);
                //delete stale gcal events
                foreach ($stale_externals as $rmevent) {
                    $db->setQuery('delete from #__pbbooking_events where gcal_id = "'.$db->escape($rmevent).'"')->execute();
                    echo '<br/>External event deleted succesfully';
                }

            }
        }
        
    }

    /**
     * A function to create the \Google_Client connection.
     *
     * @return    \Google_Client   A connected and initialised \Google_Client object.
     */

    private function createGoogleConnection()
    {
        $clientid = \JComponentHelper::getParams('com_pbbooking')->get('clientid');
        $clientsecret = \JComponentHelper::getParams('com_pbbooking')->get('clientsecret');

        if (!$clientid || !$clientsecret || $clientid == '' || $clientsecret == '')
        {
            throw new \Exception('clientid or clientsecret is blank or missing',0);
        }

        $token = $GLOBALS['com_pbbooking_data']['config']->token;

        if (!$token || $token == '')
        {
            throw new \Exception('token is missing.  Ensure calendar is linked correctly',0);
        }

        // All settings appear to be available so let's create the client.
        $client = new \Google_Client();

        $client->setClientId($clientid);
        $client->setClientSecret($clientsecret);
        $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
        $client->addScope('https://www.googleapis.com/auth/calendar');
        $client->setAccessType('offline');

        $client->setAccessToken($token);

        return $client;
    }

    /**
     * Notifies the admin of any problems with the sync
     *
     * @param    String    $message    The message
     */

    private function syncProblemNotification($message)
    {
        $details = array(
                'date'         =>    date_create("now",new \DateTimeZone(PBBOOKING_TIMEZONE))->format(DATE_ATOM),
                'remote_ip'    =>    $_SERVER['REMOTE_ADDR']
            ); 
        \Pbbooking\Pbbookinghelper::send_admin_alert('Google Cal Sync Problems','There are problems with the cal sync. ' . $message,$details);
    }
}