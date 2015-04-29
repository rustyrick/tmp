<?php

// No direct access

 
defined('_JEXEC') or die('Restricted access'); 

jimport('cms.html.html');
jimport('cms.module.helper');

class modPbbmanageHelper
{

    /**
     * called from the com_ajax entry point.  Bootstraps PBBooking & loads needed languages
     */

    public static function setupModule()
    {
        //load the libraries or bail.
        if (!file_exists(JPATH_LIBRARIES.'/purplebeanie/autoload.php') || !file_exists(JPATH_LIBRARIES.'/pbbookinglib/vendor/autoload.php'))
            throw new Exception("Libaries do not not exist", 1);

        $config = JFactory::getConfig();
        if (!defined('PBBOOKING_TIMEZONE'))
            define('PBBOOKING_TIMEZONE',$config->get('offset'));
            
        require_once(JPATH_LIBRARIES.'/purplebeanie/autoload.php');
        require_once(JPATH_LIBRARIES.'/pbbookinglib/vendor/autoload.php');
        \Pbbooking\Pbbookinghelper::bootstrapPbbooking(false);
    }


    /**
     * called from com_ajax entry point.  Retreives events for a given calendar from the database
     */

    public static function getEventsAjax()
    {
        self::setupModule();
        $readonly = self::isReadOnly();

        $db = JFactory::getDbo();
        $input = JFactory::getApplication()->input;
        $config = $GLOBALS['com_pbbooking_data']['config'];

        $cal_id = $input->get('calid',null,'integer');
        $start = $input->get('start',null,'string');
        $end = $input->get('end',null,'string');

        //convert times to dtobjects
        $dt_start = date_create($start,new DateTimeZone(PBBOOKING_TIMEZONE));
        $dt_end = date_create($end, new DateTimeZone(PBBOOKING_TIMEZONE));
        $dt_end->setTime(23,59,59);

        //load up the calendar
        $cal = new \Pbbooking\Model\Calendar($config);
        $cal->loadCalendarFromDbase(array($cal_id),$dt_start,$dt_end);

        $events = array();
        foreach ($cal->events as $event) {
            if ((int)$event->id>0) {
                $ev_obj = new \Pbbooking\Model\Event($event->id);
                $editable = true;
                if ($ev_obj->externalevent == 1 || $readonly) {
                    $editable = false;
                }
                
                $events[] = array('id'=>$event->id,'title'=>htmlspecialchars($ev_obj->getSummary()),'allDay'=>false,'start'=>$event->dtstart->format(DATE_ATOM),'end'=>$event->dtend->format(DATE_ATOM),'externalevent'=>$ev_obj->externalevent,'editable'=>$editable,'startEditable'=>$editable,'durationEditable'=>$editable);
               
            }
        }

        echo json_encode($events);
    }

    /**
     * called from the com_ajax entry point.  Renders and returns the create event form
     */

    public static function createEventAjax()
    {
        self::setupModule();

        $db = JFactory::getDbo();
        $input = JFactory::getApplication()->input;
        $config = $GLOBALS['com_pbbooking_data']['config'];
        $doc = JFactory::getDocument();

        $lang = JFactory::getLanguage();
        $lang->load('mod_pbbmanage',JPATH_SITE);

        $data = array();
        $data['date'] = date_create($input->get('date',null,'string'),new DateTimeZone(PBBOOKING_TIMEZONE));
        $data['customfields'] = \Pbbooking\Model\Customfields::buildFormForCustomfields()->getGroup('');
        $data['calendars'] = $GLOBALS['com_pbbooking_data']['calendars'];
        $data['services'] = $GLOBALS['com_pbbooking_data']['services'];

        $twig = new \Twig_Environment(new \Twig_Loader_String());
        $jtext = new \Twig_SimpleFunction('jtext',function($value) {
            return JText::_($value);
        });
        $twig->addFunction($jtext);
        $rendered = $twig->render(
          file_get_contents(JPATH_BASE.'/media/mod_pbbmanage/templates/create.tpl'),
          $data
        );

        echo $rendered;
    }

    /**
     * called from the com_ajax entry point.  saves an event to the database
     */

    public static function saveEventAjax()
    {
        self::setupModule();

        if (self::isReadOnly()) {
            die('Invalid access to function');
        }


        $input = JFactory::getApplication()->input;
        $db = JFactory::getDbo();


        $service = $db->setQuery('select * from #__pbbooking_treatments where id = '.(int)$_POST['service_id'])->loadObject();
        $_POST['treatment_time'] = date_create($input->get('dtstart',null,'string'),new DateTimeZone(PBBOOKING_TIMEZONE))->format('H:i');
        $_POST['duration'] = $service->duration;
        $event = new \Pbbooking\Model\Event();
        $event->createFromPost($_POST);
        $event_id = $event->save();
        if ($event_id) {
            $validated = $event->validate();
            if ($validated) 
                echo json_encode(array('status'=>'success'));
            else 
                echo json_encode(array('status'=>'fail','message'=>JText::_('COM_PBBOOKING_VALIDATION_ERROR')));
        } else
            echo json_encode(array('status'=>'fail','message'=>JText::_('COM_PBBOOKING_CREATE_ERROR').' '.$db->getErrorMsg()));
    }

    /**
     * called from the com_ajax entry point.  Displays an event for editing.
     */

    public static function displayEventAjax()
    {
        self::setupModule();
        if (self::isReadOnly()) {
            die('Invalid access to function');
        }

        $db = JFactory::getDbo();
        $input = JFactory::getApplication()->input;
        $event_id = $input->get('event_id',0,'integer');

        $lang = JFactory::getLanguage();
        $lang->load('mod_pbbmanage',JPATH_SITE);

        $event = $db->setQuery('select * from #__pbbooking_events where id = '.$event_id)->loadObject();
        
        //map the $cfdata into an array so that it can be bound to the form.
        $cfdata = array();
        foreach (json_decode($event->customfields_data,true) as $field)
            $cfdata[$field['varname']] = $field['data'];

        $form = \Pbbooking\Model\Customfields::buildFormForCustomfields();
        $form->bind($cfdata);

        //load and render the template.
        $data = array();
        $data['event'] = $event;
        $data['customfields'] = $form->getGroup('');
        $data['calendars'] = $GLOBALS['com_pbbooking_data']['calendars'];
        $data['services'] = $GLOBALS['com_pbbooking_data']['services'];

        $twig = new \Twig_Environment(new \Twig_Loader_String());
        $jtext = new \Twig_SimpleFunction('jtext',function($value) {
            return JText::_($value);
        });
        $twig->addFunction($jtext);
        $rendered = $twig->render(
          file_get_contents(JPATH_BASE.'/media/mod_pbbmanage/templates/edit.tpl'),
          $data
        );

        echo $rendered;
    }

    /**
     * called from the com_ajax entry point.  Used to delete an event out of the calendar
     */

    public static function deleteEventAjax()
    {
        self::setupModule();
        if (self::isReadOnly()) {
            die('Invalid access to function');
        }

        $input = JFactory::getApplication()->input;
        $event_id = $input->get('event_id',null,'integer');

        $lang = JFactory::getLanguage();
        $lang->load('mod_pbbmanage',JPATH_SITE);

        if (!$event_id)
            die('No event id specified');

        
        //do delete event
        $deleted = \Pbbooking\Model\Calendar::delete_event((int)$event_id,false);
        if ($deleted)
            echo json_encode(array('status'=>'success'));
        else 
            echo json_encode(array('status'=>'fail','message'=>JText::_('COM_PBBOOKING_DELETE_PROBLEM')));
    
    }

    /**
     * called from the com_ajax entry point.  Used to update an existing event in the diary.
     */

    public static function updateEventAjax()
    {
        self::setupModule();
        if (self::isReadOnly()) {
            die('Invalid access to function');
        }

        $input = JFactory::getApplication()->input;
        $event_id = $input->get('event_id',null,'integer');

        if (!$event_id)
            die('No event id specified');

        $event = new \Pbbooking\Model\Event($event_id);  
        \Purplebeanie\Google\Syncer::addEventToQueue($event,'delete'); 

        //fix the data to the format the model is expecting - model is being updated in 3.0.1 release.
        $_POST['treatment_id'] = $_POST['service_id'];
        $_POST['dtstart'] = date_create($_POST['dtstart'],new DateTimeZone(PBBOOKING_TIMEZONE));
        $_POST['dtend'] = date_create($_POST['dtend'],new DateTimeZone(PBBOOKING_TIMEZONE));
        
        $result = $event->update($_POST);   
        if ($result)
            echo json_encode(array('status'=>'success'));
        else 
            echo json_encode(array('status'=>'fail','message'=>'Something went wrong'));
    }

    /**
    * called by com_ajax.  responds to the drop event function.
    */

    public static function dropEventAjax()
    {
        self::setupModule();
        if (self::isReadOnly()) {
            die('Invalid access to function');
        }

        $input = JFactory::getApplication()->input;
        $event_id = $input->get('event',null,'integer');
        $action = $input->get('action',null,'string');
        $db = JFactory::getDbo();

        //load up the existing event
        $event = new \Pbbooking\Model\Event($event_id);
        \Purplebeanie\Google\Syncer::addEventToQueue($event,'delete');

        //modify event with info from view
        $seconds_delta = $input->get('delta',0,'interger');
        if (!$action)
            $event->dtstart->modify($seconds_delta." seconds");
        $event->dtend->modify($seconds_delta." seconds");

        //save the event back to the database
        if ($event->save()) {
            \Purplebeanie\Util\Pbdebug::log_msg('manage::update_calendar_event() - event updated successfuly','com_pbbooking');
            
        }
        else
            \Purplebeanie\Util\Pbdebug::log_msg('manage::update_calendar_event() - event update failed.','com_pbbooking');

    }

    /**
    * Check whether the module is read only
    */

    public static function isReadOnly()
    {
        $module = JModuleHelper::getModule('mod_pbbmanage');
        $params = json_decode($module->params,true);

        if (isset($params['readonly']) && $params['readonly'] == 1)
        {
            return true;
        } 
        else 
        {
            return false;
        }

    }
}
?>