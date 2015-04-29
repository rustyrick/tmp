<?php
/**
 * @package    PurpleBeanie.PBBooking
 * @link http://www.purplebeanie.com
 * @license    GNU/GPL
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');
jimport('joomla.mail.helper');

 
class PbbookingController extends JControllerLegacy
{
	
	function __construct()
	{
		parent::__construct();
		$input = JFactory::getApplication()->input;
		$input->set('view','pbbooking');
		
		$config =JFactory::getConfig();
		$version = new JVersion();

    	date_default_timezone_set($config->get('offset'));	

    	$db = JFactory::getDbo();
    	$db->setQuery('select * from #__pbbooking_config');
    	$this->config = $db->loadObject();


		//set locale as well...
		$language = JFactory::getLanguage();
		$locale = $language->getLocale();		
		$str_locale = preg_replace(array('/-/','/\.utf8/'),array('_',''),$locale[0]);
		setlocale(LC_ALL,$str_locale);	

		Purplebeanie\Util\Pbdebug::log_msg('Joomla offset = '.$config->get('offset'),'com_pbbooking');	
		Purplebeanie\Util\Pbdebug::log_msg('Joomla is running on '.$version->RELEASE,'com_pbbooking');
		Purplebeanie\Util\Pbdebug::log_msg('PHP is '.phpversion(),'com_pbbooking');

	}
	
    /**
     * Method to display the view
     *
     * @access    public
     */
    function display($cachable = false, $urlparams = array())
    {	
    	Purplebeanie\Util\Pbdebug::log_msg('Calling display method in front end','com_pbbooking');    	
    	//load up the view
    	$view = $this->getView('PBBooking','html');
    	
    	//populate needed data into the view.
    	$db = JFactory::getDBO();
    	
    	$view->config = $this->config;
    	$view->customfields = $GLOBALS['com_pbbooking_data']['customfields'];
		$view->now = date_create("now",new DateTimeZone(PBBOOKING_TIMEZONE));
		$view->treatments = \Pbbooking\Pbbookinghelper::get_valid_services();
		$view->user = JFactory::getUser();
		$view->shift_times = \Pbbooking\Pbbookinghelper::get_shift_times();
		$view->master_trading_hours = json_decode($view->config->trading_hours,true);
		$view->latest = date_create("now",new DateTimeZone(PBBOOKING_TIMEZONE));
		if (isset($view->config->allow_booking_max_days_in_advance)) $view->latest->modify('+ '.$view->config->allow_booking_max_days_in_advance.' days');
	    	
		$view->dateparam = $GLOBALS['com_pbbooking_data']['dtdateparam'];

		$config =JFactory::getConfig();
    	$view->dateparam->setTimezone(new DateTimezone($config->get('offset')));
		
		//parse a valid cal from the database		
		$view->cals = $GLOBALS['com_pbbooking_data']['calendars'];
		
    	//choose the view depending on which one the config is set to use and whether the user is authorised
    	if ($view->user->authorise('pbbooking.browse','com_pbbooking'))
			$view->setLayout(($view->config->multi_page_checkout == 0) ? 'calendar' : 'multipagecheckout');
		else
			$view->setLayout('notauthorised');

		//display the view....
    	$view->display();			
    }
    
    /**
     * 
     * saves the appointment to the pending_events table and routes validation emails
     * 
     */
    	
	function save()
	{
		$db =JFactory::getDBO();
		$customfields = $db->setQuery('select * from #__pbbooking_customfields')->loadObjectList();		
		$config = $this->config;
		$user = JFactory::getUser();
		$input = JFactory::getApplication()->input;
		JPluginHelper::importPlugin('pbbooking');


		//check if user can save an appointment and bail early if they can't.
		if (!$user->authorise('pbbooking.create','com_pbbooking')) {
			$this->setRedirect('index.php?option=com_pbbooking',JText::_('COM_PBBOOKING_LOGIN_MESSAGE_CREATE'));
			return;
		}

		//check is user passed recaptcha and if not redirect
		if ($GLOBALS['com_pbbooking_data']['config']->enable_recaptcha) {
			$code= $input->get('recaptcha_response_field',null,'string');     
			JPluginHelper::importPlugin('captcha');
		   	$dispatcher = JDispatcher::getInstance();
		   	$res = $dispatcher->trigger('onCheckAnswer',$code);
		   	if(!$res[0]){
		   		$this->setRedirect(JRoute::_('index.php?option=com_pbbooking&dateparam='.$_POST['date']),JText::_('COM_PBBOOKING_INCORRECT_RECAPTCHA'));
		   		return;
		   }
		}

		$event = new \Pbbooking\Model\Event();
		if ($event->createFromPost($_POST)) {
			Purplebeanie\Util\Pbdebug::log_msg('PbbookingController::save() - Attempt to create pending event succeeded.','com_pbbooking');
		} else {
			Purplebeanie\Util\Pbdebug::log_msg('PbbookingController::save() - Attempt to create pending event failed.  Some data was missing.','com_pbbooking');
			if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']))
				$this->setRedirect(JRoute::_('index.php?option=com_pbbooking&dateparam='.$_POST['date']),JText::_('COM_PBBOOKING_BOOKING_PROBLEM'));
			return;
		}

		if ($event->isValid()) {
			Purplebeanie\Util\Pbdebug::log_msg('PbbookingController::save() - event is valid.','com_pbbooking');
		} else {
			Purplebeanie\Util\Pbdebug::log_msg('PbbookingController::save() - event is invalid','com_pbbooking');
			$this->setRedirect(JRoute::_('index.php?option=com_pbbooking&dateparam='.$_POST['date']),JText::_('COM_PBBOOKING_PROBLEM_EVENT_INVALID'));
			return;
		}
		
		//make sure that the email address is valid!
		if (!JMailHelper::isEmailAddress($event->email)) {
			Purplebeanie\Util\Pbdebug::log_msg('PbbookingController::save() - event email address is invalid.  Provided email address is '.$event->email,'com_pbbooking');
			$this->setRedirect(JRoute::_('index.php?option=com_pbbooking&dateparam='.$_POST['date']),JText::_('COM_PBBOOKING_PROBLEM_EVENT_INVALID_EMAIL'));
			return;
		}

		//create pending event and email user
		$pending_id = $event->save();

		//set the validation to paypal if dicated by the service
		$service = $db->setQuery('select * from #__pbbooking_treatments where id = '.(int)$event->service_id)->loadObject();
		if (isset($service->require_payment) && $service->require_payment == 1)
			$config->validation = 'paypal';

		if ($pending_id) {			
			//switch statement to handle the different validation types.
			switch ($config->validation) {
				case 'client':
					Purplebeanie\Util\Pbdebug::log_msg('PbbookingController::save() doing client validation for event id '.$event->id,'com_pbbooking');
					\Pbbooking\Pbbookinghelper::email_user($event);
					
					//now redirect - load up the view
					$view = $this->getView('PBBooking','html');
					$view->setLayout('success');
					
					//populate needed data into the view.
					$view->service = $service;
					$view->config = $config;
					$view->event = new \Pbbooking\Model\Event($event->id);				//a fresh copy is loaded because if multi time zone is used the event is polluted during emailing.
					break;
				case 'auto':
					$this->setRedirect(JRoute::_('index.php?option=com_pbbooking&task=validate&id='.$event->id.'&email='.$event->email));
					return;
					break;
				case 'admin':

					//create the token and update the record with the token
					$event->validation_token = md5($event->id.$event->email.$event->dtstart->format('Ymd'));
					
					//THIS IS A BAD WORK AROUND TO FIX ISSUE #196
					$event->updateValidationToken();
					//END FIX
					
					\Pbbooking\Pbbookinghelper::send_admin_validation($event);
					
					//now redirect - load up the view
					$view = $this->getView('PBBooking','html');
					$view->setLayout('success');
					
					//populate needed data into the view.
					$view->service = $db->setQuery('select * from #__pbbooking_treatments where id = '.$db->escape($event->service_id))->loadObject();
					$view->config = $config;
					$view->event = $event;
					break;
				case 'paypal':
					$this->setupPayment($event,$service);
					return;
					break;
			}

			JDispatcher::getInstance()->trigger('onPendingEventCreated', array($event->id));

			//display the view
			$view->display();
				    	
		} else {
			$this->setRedirect(JRoute::_('index.php?option=com_pbbooking&dateparam='.$event->dtstart->format('Ymd')),JText::_('COM_PBBOOKING_BOOKING_PROBLEM'));				
		}

	}
	
	function validate() {
		
		$db=JFactory::getDBO();
		$input = JFactory::getApplication()->input;

		$pendingid = $input->get('id',0,'integer');
		$email = $input->get('email',null,'string');
		$token = $input->get('token',null,'string');
		JPluginHelper::importPlugin('pbbooking');

		$event = new \Pbbooking\Model\Event($pendingid);
		if ($event->id != $pendingid || $event->email != $email || ($this->config->validation == 'admin' && $event->validation_token != $token)) {
			Purplebeanie\Util\Pbdebug::log_msg('PbbookingController::validate() - user tried to validate appt '.(int)$pendingid.' but email and id did not match saved details','com_pbbooking');
			$this->setRedirect(JRoute::_('index.php?option=com_pbbooking'),JText::_('COM_PBBOOKING_VALIDATION_PROBLEM'));
			return;
		}

		//the event id and the email match and if using auto validation the token match so let's validate.
		Purplebeanie\Util\Pbdebug::log_msg('Event::validate() - the loaded event is '.json_encode($event),'com_pbbooking');
		$validated = $event->validate();

		if ($validated == false) {
			Purplebeanie\Util\Pbdebug::log_msg('PBbookingController:validate() failed for event with id '.(int)$event->id.' redirecting user','com_pbbooking');
			$this->setRedirect(JRoute::_('index.php?option=com_pbbooking'),JText::_('COM_PBBOOKING_VALIDATION_PROBLEM'));
		}

		//from here on it is assumed with have a succesfully validated event that has been wirtten to the relevant diaries.
		\Pbbooking\Pbbookinghelper::email_admin($event->id,$event->id);
		JDispatcher::getInstance()->trigger('onPendingEventValidated',array($event->id));

		//load up the view
		$view = $this->getView('PBBooking','html');
		$view->setLayout('validated');
		
		//populate the view with data
		$view->event = $event;
		$view->service = $db->setQuery("select * from #__pbbooking_treatments where id = ".$event->service_id)->loadObject();
		$view->calendar = $db->setQuery("select * from #__pbbooking_cals where id = ".$event->cal_id)->loadObject();
		$view->config = $this->config;

		//check if the appt is sent to auto validate as if it is we need to email the user
		if ($view->config->validation == 'auto') {
			Purplebeanie\Util\Pbdebug::log_msg('validate() sending auto validation email for event id '.$event->id,'com_pbbooking');
			\Pbbooking\Pbbookinghelper::send_auto_validate_email($event->id);
		}

		//check if the appt is set to admin validate as we need to let the user know it has been validated.
		if ($this->config->validation == 'admin') {
			Purplebeanie\Util\Pbdebug::log_msg('PBbookingController::validate() - sending email to user to let hem know event id '.(int)$event->id.' is validated','com_pbbooking');
			$body = \Pbbooking\Pbbookinghelper::_prepare_email('admin_validation_confirmed_email_body',array('service_id'=>$event->service_id,'dtstart'=>$event->dtstart->format(DATE_ATOM),'url'=>null,'calendar'=>$view->calendar),json_decode($event->customfields_data,true));
			$msg = \Pbbooking\Pbbookinghelper::get_multilang_message('admin_validation_confirmed_email_body');
			\Pbbooking\Pbbookinghelper::send_email($msg['subject'],$body,$event->email);
		}
		
		//display the view
		$view->display();
	}
	
	function error() {
		//$this->setLayout('fail');
		$input = JFactory::getApplcation()->input;
		$input->set('layout','fail');
		parent::display();
	}
	
	
	/**
	* load_slots_for_day($date,$grouping,$treatment) - passes back a JSON encoded data for the browser to view time slots
	*
	* @param string $date - the date to return data for
	* @param string $grouping - the grouping to return data for
	* @param int $treatment - the treatement_id to return data for
	* @return string an html string to inject into the dom at the view
	*/	
	
	function load_slots_for_day()


	{
		$config =JFactory::getConfig();
		$db = JFactory::getDbo();
		$input = JFactory::getApplication()->input;

		$calendar = $input->get('calendar',0,'integer');
		$pbbooking_config = $this->config;

    	date_default_timezone_set($config->get('offset'));	

		//{'date':date,'option':'com_pbbooking','task':'load_slots_for_day','format':'raw','grouping':grouping,'treatment':treatment}')
		$date = date_create($input->get('dateparam',null,'string'),new DateTimeZone(PBBOOKING_TIMEZONE));
		$date->setTimezone(new DateTimeZone($config->get('offset')));
		$end_of_day = clone $date;
		$end_of_day->setTime(23,59,59);
		$end_of_day->modify('+1 day');

		
		Purplebeanie\Util\Pbdebug::log_msg('load_slots_for_day() using date '.$date->format(DATE_ATOM),'com_pbbooking');
		Purplebeanie\Util\Pbdebug::log_msg('load_slots_for_day() $config->enable_shifts = '.$pbbooking_config->enable_shifts,'com_pbbooking');

		$grouping = $input->get('grouping',null,'string');
		$treatment_id = $input->get('treatment',null,'integer');
		$db->setQuery('select * from #__pbbooking_treatments where id ='.$db->escape((int)$treatment_id));
		$treatment=$db->loadObject();
		if ($pbbooking_config->select_calendar_individual == 1) 
		{
			$valid_cals = array($GLOBALS['com_pbbooking_data']['calendars'][$calendar]);
		}
		else
		{
			$valid_cals = array();
			foreach ($GLOBALS['com_pbbooking_data']['calendars'] as $cal) {
				if (in_array($cal->cal_id,explode(',',$treatment->calendar)))
					$valid_cals[] = $cal;
			}			
		}

		//get start_hour start_min end_hour end_min for groupings		
		Purplebeanie\Util\Pbdebug::log_msg('load_slots_for_day() $this->config->time_groupings = '.$this->config->time_groupings,'com_pbbooking');
		Purplebeanie\Util\Pbdebug::log_msg('load_slots_for_day() $grouping = '.$grouping,'com_pbbooking');
		$groupings = \Pbbooking\Pbbookinghelper::get_shift_times();
		
		//push vars into view
		$view = $this->getView('Pbbooking','raw');
		$view->setLayout('individual_freeflow_view_calendar');
		$view->date_start = date_create($date->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
		$view->date_start->setTimezone(new DateTimezone($config->get('offset')));
		$view->date_end = date_create($date->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
		$view->date_end->setTimezone(new DateTimezone($config->get('offset')));
		$view->config = $this->config;
		$view->user_offset = $input->get('user_offset',0,'integer');

		if ($pbbooking_config->enable_shifts == 1) {
			$view->date_start->setTime($groupings[$grouping]['start_time']['start_hour'],$groupings[$grouping]['start_time']['start_min'],'0');
			$view->date_end->setTime($groupings[$grouping]['end_time']['end_hour'],$groupings[$grouping]['end_time']['end_min'],'0');
		} else {
            //need to loop through all calendars and find the earliest start time as presently it will just find the first
            if (count($valid_cals) == 1) {
                $calhours = $valid_cals[key($valid_cals)]->getCalendarTradingHoursForDay($date);
                if (!$calhours) {
	                $view->date_start->setTime(0,0,0);
	                $view->date_end->setTime(23,59,59);
                } else {
			    	$view->date_start = date_create($calhours['dtstart']->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
			    	$view->date_end = date_create($calhours['dtend']->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
			    }
            } else {
                $view->date_start->setTime(0,0,0);
                $view->date_end->setTime(23,59,59);
            }
		}
		
		//make sure the time is far enough ahead and adjust accordingly.
		$earliest = date_create("now",new DateTimeZone(PBBOOKING_TIMEZONE));
		$earliest->modify('+ '.$pbbooking_config->prevent_bookings_within.' minutes');
		while ($view->date_start <= $earliest)
			$view->date_start->modify('+ '.$pbbooking_config->time_increment.' minutes');
		

		Purplebeanie\Util\Pbdebug::log_msg('load_slots_for_day() $view->date_start = '.$view->date_start->format(DATE_ATOM),'com_pbbooking');
		Purplebeanie\Util\Pbdebug::log_msg('load_slots_for_day() $view->date_end = '.$view->date_end->format(DATE_ATOM),'com_pbbooking');	

		$view->time_increment = $this->config->time_increment;
		$view->treatment = $treatment;
		$view->cals = $valid_cals;	
		
		//render view
		$view->display();

	}




	/**
	* renders the day view based on the free version layout. based on the code from the feww version with modifications.
	* @access public
	* @since 2.4.1
	*/

	public function dayview()
	{
		$db = JFactory::getDbo();
		$input = JFactory::getApplication()->input;

		//load the view
		$view = $this->getView('pbbooking','html');

		//getparam
		$view->config = $this->config;
		$view->dateparam = date_create($input->get('dateparam','now','string'),new DateTimeZone(PBBOOKING_TIMEZONE));
		$nday = date_create($view->dateparam->format(DATE_ATOM), new DateTimeZone(PBBOOKING_TIMEZONE));  //next day --- needed for google cal compatibility in setQueryMax
		$nday->modify('+1 day');


		//load up the calendars....
		$view->cals = $GLOBALS['com_pbbooking_data']['calendars'];

		//calc start and end day
		$errorLevel = error_reporting();
		error_reporting(0);
		$opening_hours = json_decode($view->config->trading_hours,true);
		$view->opening_hours = $opening_hours[(int)$view->dateparam->format('w')];
		$start_time_arr = str_split($opening_hours[(int)$view->dateparam->format('w')]['open_time'],2);
		$end_time_arr = str_split($opening_hours[(int)$view->dateparam->format('w')]['close_time'],2);
		$view->day_dt_start = date_create($input->get('dateparam','now','string'),new DateTimezone(PBBOOKING_TIMEZONE));
		$view->day_dt_end = date_create($input->get('dateparam','now','string'),new DateTimezone(PBBOOKING_TIMEZONE));
		$view->day_dt_start->setTime($start_time_arr[0],$start_time_arr[1],0);
		$view->day_dt_end->setTime($end_time_arr[0],$end_time_arr[1],0);
		$view->user = JFactory::getUser();
		$view->earliest = date_create("now",new DateTimeZone(PBBOOKING_TIMEZONE));
		$view->earliest->modify('+ '.$view->config->prevent_bookings_within.' minutes');
		error_reporting($errorLevel);

		if ($view->config->enable_shifts == 1) {
			//load the shifts in ... we may not use them depends on the config.....
			$view->shifts = \Pbbooking\Pbbookinghelper::get_shift_times();
			$view->setLayout('dayviewinshifts');
		} else {
			$view->setLayout('dayview');
			$view->last_slot_for_day = date_create($view->day_dt_end->format(DATE_ATOM),new DateTimezone(PBBOOKING_TIMEZONE));
			$view->last_slot_for_day->modify('- '.$view->config->time_increment.' minutes');
		}

		//change the layout if the user is not authorised.
    	if (!JFactory::getUser()->authorise('pbbooking.browse','com_pbbooking'))
			$view->setLayout('notauthorised');

		//render the display
		$view->display();
	}

	/**
	* responds to the create task
	* @param string the date and time from the command ?dtstart=201209041030
	* @param string the cal_id &cal=1
	* @since 2.4.1
	* @access public
	*/
	public function create()
	{
		$input = JFactory::getApplication()->input;
		$db = JFactory::getDbo();
		$view = $this->getView('pbbooking','html');
		$config = $this->config;

		//push the dateparam into the view now cause we need it so often...
		$view->dateparam = date_create($input->get('dtstart',null,'string'), new DateTimeZone(PBBOOKING_TIMEZONE));
		$nday = date_create($view->dateparam->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE)); //required for google cal compatiblility with setQueryMax
		$nday->modify('+1 day');

		//check if I'm working with shifts and get the relevant shift
		if ($config->enable_shifts == 1) {
			$shifts = \Pbbooking\Pbbookinghelper::get_shift_times();
			foreach ($shifts as $label=>$shift) {
				$shift_start = date_create($view->dateparam->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
				$shift_end = date_create($view->dateparam->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
				$shift_start->setTime($shift['start_time']['start_hour'],$shift['start_time']['start_min'],0);
				$shift_end->setTime($shift['end_time']['end_hour'],$shift['end_time']['end_min'],0);
				if ( $view->dateparam >= $shift_start && $view->dateparam <= $shift_end )
					$view->closing_time = date_create($shift_end->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
			}
		} else {
			$opening_hours = json_decode($config->trading_hours,true);		
			$closing_time_arr = str_split( $opening_hours[date_create($view->dateparam->format(DATE_ATOM),new DateTimezone(PBBOOKING_TIMEZONE))->format('w')]['close_time'],2 );
			$view->closing_time = date_create($input->get('dtstart',null,'string'),new DateTimeZone(PBBOOKING_TIMEZONE));
			$view->closing_time->setTime($closing_time_arr[0],$closing_time_arr[1],0);
		}
		
		$dateparam = $input->get('dtstart',date_create('now',new DateTimeZone(PBBOOKING_TIMEZONE))->format('YmdHi'),'string');
		$cal_id = $input->get('cal_id',0,'integer');
		$opening_hours = json_decode($config->trading_hours,true);
		$closing_time_arr = str_split( $opening_hours[date_create($dateparam,new DateTimezone(PBBOOKING_TIMEZONE))->format('w')]['close_time'],2 );
		
		$view->dateparam = date_create($dateparam,new DateTimeZone(PBBOOKING_TIMEZONE));
		$view->customfields = $db->setQuery('select * from #__pbbooking_customfields order by ordering desc')->loadObjectList();
		$view->treatments = $db->setQuery('select * from #__pbbooking_treatments order by ordering desc')->loadObjectList();
		$view->cal = $GLOBALS['com_pbbooking_data']['calendars'][$cal_id];
		$view->longest_time = $view->cal->get_longest_available_booking($view->dateparam);

		$view->config = $config;

		$view->setLayout('create');
		$view->display();
	}

	/**
	* runs any pending cron jobs such as reminders etc
	* @access public
	* @since 2.4.2
	*/

	public function cron()
	{
		$db = JFactory::getDbo();
		$view = $this->getView('pbbooking','html');
		$view->setLayout('cron');
		JPluginHelper::importPlugin('pbbooking');


		$view->config = $GLOBALS['com_pbbooking_data']['config'];
		if ($view->config->enable_cron) {

			//log for tracking / auditing purposes....
			Purplebeanie\Util\Pbdebug::log_msg('cron(): running cron by web request at '.date_create("now",new DateTimeZone(PBBOOKING_TIMEZONE))->format(DATE_ATOM).' from client '.$_SERVER['REMOTE_ADDR'],'com_pbbooking');

			//what cron tasks do i need to do?
			if ($view->config->enable_reminders == 1) {
				Purplebeanie\Util\Pbdebug::log_msg('cron(): got task enable_reminders','com_pbbooking');
				$reminder_details = json_decode($view->config->reminder_settings,true);
				$date_from = date_create("today",new DateTimeZone(PBBOOKING_TIMEZONE));
				$date_from->modify('+ '.$reminder_details['reminder_days_in_advance'].' days');
				$date_to = date_create($date_from->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
				$date_to->setTime(23,59,59);
				Purplebeanie\Util\Pbdebug::log_msg('cron(): going to run task enable_reminders for date range $date_from '.$date_from->format(DATE_ATOM).' $date_to '.$date_to->format(DATE_ATOM),'com_pbbooking');

				//get all the events I should send for...
				$events = $db->setQuery('select * from #__pbbooking_events where verified = 1 and externalevent = 0 and dtstart >= "'.$date_from->format(DATE_ATOM).'" and dtstart <= "'.$date_to->format(DATE_ATOM).'"')->loadObjectList();
				Purplebeanie\Util\Pbdebug::log_msg('cron(): found '.count($events).' events to send reminders for','com_pbbooking');

				//loop through all the events and send the reminder...
				foreach ($events as $event) {
					if ($event->reminder_sent == 0) {
						Purplebeanie\Util\Pbdebug::log_msg('cron(): sending reminder for event with id '.$event->id,'com_pbbooking');
						if (\Pbbooking\Pbbookinghelper::send_reminder_email_for_event($event))
							//update the event with the status....
							$db->updateObject('#__pbbooking_events',new JObject(array('id'=>$event->id,'reminder_sent'=>1)),'id');
						JDispatcher::getInstance()->trigger('onEventReminderSent', array($event->id));
					}
				}
			}

			if ($view->config->enable_testimonials) {
				Purplebeanie\Util\Pbdebug::log_msg('cron(): got task enable_testimonials','com_pbbooking');
				$date_from = date_create("today",new DateTimeZone(PBBOOKING_TIMEZONE));
				$date_from->modify('- '.$view->config->testimonial_days_after.' days');
				$date_from->setTime(0,0,0);
				$date_to = date_create($date_from->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
				$date_to->setTime(23,59,59);

				//get all the events....
				$events = $db->setQuery('select * from #__pbbooking_events where verified = 1 and externalevent = 0 and dtstart >= "'.$date_from->format(DATE_ATOM).'" and dtstart <= "'.$date_to->format(DATE_ATOM).'"')->loadObjectList();
				Purplebeanie\Util\Pbdebug::log_msg('cron(): going to run task enable_testimonials for date range $date_from '.$date_from->format(DATE_ATOM).' $date_to '.$date_to->format(DATE_ATOM),'com_pbbooking');

				foreach ($events as $event) {
					if ($event->testimonial_request_sent == 0) {
						Purplebeanie\Util\Pbdebug::log_msg('cron(): sending testimonial request for event with id '.$event->id,'com_pbbooking');
						if (\Pbbooking\Pbbookinghelper::send_testimonial_email_for_event($event))
							$db->updateObject('#__pbbooking_events',new JObject(array('id'=>$event->id,'testimonial_request_sent'=>1)),'id');
					}
				}
			}

			$view->display();
		} else {
			$this->setRedirect(JRoute::_('index.php?option=com_pbbooking'),JText::_('COM_PBBBOOKING_CRON_NOT_ENABLED'));
		}
	}


	/**
	* loads and presents a survey for the user to complete. 
	* @access public
	* @since 2.4.3
	*/

	public function survey()
	{
		$db = JFactory::getDbo();
		$input = JFactory::getApplication()->input;
		$view = $this->getView('pbbooking','html');

		//define an error flag to prevent dodginess
		$error = false;

		//load the user survey object up and check if there is actually an event with the nominated id and whether the email matches
		$email = $input->get('email',null,'string');
		$id = $input->get('id',null,'integer');
		if (!$email && !$id)
			$error = true;
		else {
			//load up the config & the event
			$event = $db->setQuery('select * from #__pbbooking_events where id = '.(int)$id)->loadObject();
			$config = $GLOBALS['com_pbbooking_data']['config'];
			if ($event->email != $email)
				$error = true;
		}

		if ($_SERVER['REQUEST_METHOD'] == 'GET' && !$error) {
			$view->setLayout('survey');
			$view->event = $event;
			$view->config = $config;
			$view->questions = json_decode($config->testimonial_questions,true);
			//require for BC
			foreach ($view->questions as &$question)
				$question = array('type'=>$question['testimonial_field_type'],'name'=>$question['testimonial_field_varname'],
								'label'=>$question['testimonial_field_label'],'values'=>$question['testimonial_field_values']);
			$view->form = \Purplebeanie\Util\PbFrameUtil::buildJFormForArray($view->questions);
			$view->display();
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$error)
		{
			$survey = $this->getModel('Survey');
			$s_response = array();
			$s_response['event_id'] = $id;

			foreach (json_decode($config->testimonial_questions,true) as $question)
				$s_response['content'][$question['testimonial_field_varname']] = $input->get($question['testimonial_field_varname'],null,'string');
			$s_response['content'] = json_encode($s_response['content']);
			$s_response['submission_ip'] = $_SERVER['REMOTE_ADDR'];

			if ($survey->save_survey($s_response)) {
				$j_config = JFactory::getConfig();
				\Pbbooking\Pbbookinghelper::send_email(JText::_('COM_PBBOOKING_EMAIL_NEW_SURVEY_SUBJECT'),JText::_('COM_PBBOOKING_EMAIL_NEW_SURVEY_BODY'),$j_config->get('mailfrom'),$bcc=null);
				$this->setRedirect('index.php',JText::_('COM_PBBOOKING_SURVEY_SUCCESS'));
			}
			else
				$error = true;
		}

		if ($error)
			$this->setRedirect('index.php',JText::_('COM_PBBOOKING_SURVEY_ERROR'));

	}

	/**
	* called by AJAX.  loads and returns json_encoded services for a calendar.
	* @param int the id of the calendar to load services for
	* @return string json encoded string of treatments
	* @since 2.4.5.10
	* @access public
	*/

	public function load_calendar_services()
	{
		$input = JFactory::getApplication()->input;

		$services = \Pbbooking\Model\Calendar::get_services_for_calendar((int)$input->get('cal_id'));

		//process the returned services array to get ready for output.
		foreach ($services as $service) {
			if (isset($this->config->show_prices) && $this->config->show_prices == 1) {
				$service->name = \Pbbooking\Pbbookinghelper::print_multilang_name($service,'service').' - '.\Pbbooking\Pbbookinghelper::pbb_money_format($service->price);	
			}
		}
		echo json_encode($services);
	}

	/**
	 * this will setup the paypal payment and redirect accordingly
	 * @param    Event       $event      the event object
	 * @param    JObhject    $service    the service object
	 */

	protected function setupPayment($event,$service)
	{
		$config = $GLOBALS['com_pbbooking_data']['config'];
		Purplebeanie\Util\Pbdebug::log_msg('setupPayment() - processign payment for event '.$event->id,'com_pbbooking');
                
                if (!$config->paypal_api_username || !$config->paypal_api_password || !$config->paypal_api_signature)
                {
                    echo JText::_('COM_PBBOOKING_PAYMENT_SETUP_PROBLEM_MISSING_CREDENTIALS');
                    return;
                }

		//create a new gateway
		$gateway = \Omnipay\Omnipay::create('PayPal_Express');
		$gateway->setTestMode($config->paypal_test);
		$gateway->setUsername($config->paypal_api_username);
		$gateway->setPassword($config->paypal_api_password);
		$gateway->setSignature($config->paypal_api_signature);

		//set the purchase information appropriately.
		$purchaseDetails = array(
		        'cancelUrl' => (isset($_SERVER['HTTPS'])) ? 'https://' . $_SERVER['HTTP_HOST'].JRoute::_('index.php?option=com_pbbooking&controller=pbbooking&task=cancel') : 'http://' . $_SERVER['HTTP_HOST'].JRoute::_('index.php?option=com_pbbooking&controller=pbbooking&task=cancel'),
		        'returnUrl' => (isset($_SERVER['HTTPS'])) ? 'https://' . $_SERVER['HTTP_HOST'].JRoute::_('index.php?option=com_pbbooking&controller=pbbooking&task=doConfirmPayment&id='.$event->id) : 'http://' . $_SERVER['HTTP_HOST'].JRoute::_('index.php?option=com_pbbooking&controller=pbbooking&task=doConfirmPayment&id='.$event->id), 
		        'amount' => str_replace(',','.',sprintf('%0.2f',$service->price)),
		        'currency' => $config->paypal_currency
		    );

		try {
			$response = $gateway->purchase($purchaseDetails)->send();
                       			
			//first check if it's a re-direct
			if ($response->isRedirect()){
				$this->setRedirect($response->getRedirectUrl());
				return;
			}
		} catch (\Exception $e) {
			$message = 'Exception: '.$e->getMessage();
                        echo '<h1>' . JText::_('COM_PBBOOKING_PAYMENT_SETUP_PROBLEM_HEADING') . '</h1>';
                        echo '<p>' . JText::_('COM_PBBOOKING_PAYMENT_SETUP_PROBLEM_DESCRIPTION') .'</p>';
			echo $message;
		}
		return;
	}

	/**
	 * the return url lands here to process
	 */

	public function doConfirmPayment()
	{
		$config = $GLOBALS['com_pbbooking_data']['config'];
		$input = JFactory::getApplication()->input;
		$view = $this->getView('Pbbooking','html');
		$db = JFactory::getDbo();

		$id = $input->get('id',null,'integer');
		$event = new \Pbbooking\Model\Event($id);

		if (!$id || !isset($event->id))
			die('The event is invalid.  This requires a valid event');

		$service = $db->setQuery('select * from #__pbbooking_treatments where id = '.(int)$event->service_id)->loadObject();

		//re-construct the gateway
		//create a new gateway
		$gateway = \Omnipay\Omnipay::create('PayPal_Express');
		$gateway->setTestMode($config->paypal_test);
		$gateway->setUsername($config->paypal_api_username);
		$gateway->setPassword($config->paypal_api_password);
		$gateway->setSignature($config->paypal_api_signature);

		
		$response = $gateway->completePurchase(
		    array(
		        'cancelUrl' => (isset($_SERVER['HTTPS'])) ? 'https://' . $_SERVER['HTTP_HOST'].JRoute::_('index.php?option=com_pbbooking&controller=pbbooking&task=cancel') : 'http://' . $_SERVER['HTTP_HOST'].JRoute::_('index.php?option=com_pbbooking&controller=pbbooking&task=cancel'),
		        'returnUrl' => (isset($_SERVER['HTTPS'])) ? 'https://' . $_SERVER['HTTP_HOST'].JRoute::_('index.php?option=com_pbbooking&controller=pbbooking&task=doConfirmPayment&id='.$event->id) : 'http://' . $_SERVER['HTTP_HOST'].JRoute::_('index.php?option=com_pbbooking&controller=pbbooking&task=doConfirmPayment&id='.$event->id), 
		        'amount' => str_replace(',','.',sprintf('%0.2f',$service->price)),
		        'currency' => $config->paypal_currency
		    )
		)->send();

		if ($response->isSuccessful()) {
			//send the notification emails
            \Pbbooking\Pbbookinghelper::confirm_paypal_payment($event->id,sprintf('%0.2f',$service->price));
            $view->setLayout('paypalpending');
		} else {
			$view->setLayout('fail');
		}

		$view->display();


	}
	

}