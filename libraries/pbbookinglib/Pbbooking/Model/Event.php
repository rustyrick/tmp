<?php

/**
* @package		PurpleBeanie.PBBooking
* @license		GNU General Public License version 2 or later; see LICENSE.txt
* @link		http://www.purplebeanie.com
*/


namespace Pbbooking\Model;

//class definition for event
class Event

{
	public $summary;
	public $dtend;
	public $dtstart;
	public $kTimeslots;
	public $recurring;
	public $description;
	public $id;
	public $r_int;
	public $r_end;
	public $r_freq;
	public $gcal_id;
	public $cal_id;
	public $uid;
	public $service_id;
	public $customfields_data;
	public $email;
	public $deposit_paid;
	public $amount_paid;
	public $reminder_sent;
	public $testimonial_request_sent;
	public $user_offset;
	public $verified;
	public $validation_token;
	public $parent;
	public $externalevent;

	
	/**
	* Default constructor for event.
	*
	* @optional int An event_id to load from the database
	* @return Event the event
	*/
	
	function __construct($id = null)
	{		
		$this->fields = array('id','cal_id','summary','dtend','dtstart','description','uid','service_id','r_int','r_freq','r_end','customfields_data','email',
				'deposit_paid','amount_paid','reminder_sent','testimonial_request_sent','gcal_id','user_offset','verified','validation_token','parent','externalevent');

		//load and set timezone
		$db = \JFactory::getDBO();
		$config =\JFactory::getConfig();
    	date_default_timezone_set($config->get('offset'));			

    	//pull the customfields out of the database
    	$this->customfields = $GLOBALS['com_pbbooking_data']['customfields'];
    	$this->requiredfields = array('dtend','dtstart','cal_id','email','service_id');								//holds the required fields for ensuring the event is valid

		if ($id) {
			\Purplebeanie\Util\Pbdebug::log_msg('Event::__construct() trying to load id  '.(int)$id,'com_pbbooking');
			$event = $db->setQuery("select * from #__pbbooking_events where id = ".(int)$db->escape($id))->loadObject();
			if ($event) {
				foreach ($this->fields as $field)
					$this->$field = (isset($event->$field)) ? $event->$field : null;

				$this->dtend = date_create($this->dtend,new \DateTimeZone(PBBOOKING_TIMEZONE));
				$this->dtstart = date_create($this->dtstart,new \DateTimeZone(PBBOOKING_TIMEZONE));
			}
		}
		return $this;
	}
	
	/**
	* Method to delete the current event
	*
	* @return bool succesful or not...
	*/
	
	function delete()
	{
		$db = \JFactory::getDBO();
		$db->setQuery("delete from #__pbbooking_events where id = ".$db->escape($this->id));
		
		//specfic for TDG, need to update link to booking
		if ($db->query()) {
			$db->setQuery('select * from #__pbbooking_pending where id = '.$db->escape($this->booking_id));
			$booking = $db->loadObject();
			$linked_bookings_arr = explode(',',$booking->linked_bookings);
			$new_linked_bookings_arr = array();
			foreach($linked_bookings_arr as $linked_booking) {
				if ($linked_booking != $this->id) {
					array_push($new_linked_bookings_arr,$linked_booking);
				}
			}
			$db->setQuery("update #__pbbooking_pending set linked_bookings = '".implode(',',$new_linked_bookings_arr)."' where id = ".$booking->id);
			$db->query();
			
			return true;
		} 
	}
	
	function setProperties($date,$timeslot,$duration,$summary) {
		
		$db = \JFactory::getDBO();
		$db->setQuery("select * from #__pbbooking_slots where id=".$timeslot);
		$booked_slot = $db->loadObject();
		
		$this->summary = $summary;
		$dtstart = date_create($date->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
		$dtstart->setTime($booked_slot->start_hour,$booked_slot->start_min);
		$dtend = date_create($dtstart->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
		$dtend->modify('+'.$duration.' minutes');
		$this->dtend = $dtend;
		$this->dtstart = $dtstart;
	}
	
	function setDescription($customfields) {
		#function to set the description to contact a list of custom fields
		if (count($customfields>0)) {
			foreach ($customfields as $field) {
				$this->description .= sprintf('%s = %s\n',$field['varname'],$field['data']);
			}
		}
	}
	
	function getEvent($event_id) {
		$db = \JFactory::getDBO();
		$db->setQuery("select * from #__pbbooking_events where id=".$db->escape($event_id));
		$event = $db->loadObject();
		return $event;
	}


	/**
	* gets the pbbooking id for an event given the gcal_id
	* @access public
	* @since 2.4.5
	* @param string gcal id
	* @return mixed int with the event id or null
	*/

	public static function get_pbbooking_id_for_event($gcal_id)
	{
		if ($gcal_id == null)
			return null;

		$db = \JFactory::getDbo();
		$event = $db->setQuery('select * from #__pbbooking_events where gcal_id = "'.$db->escape($gcal_id).'"')->loadObject();
		if ($event)
			return $event->id;
		else
			return null;
	}

	/**
	* saves an event back to the database
	* @access public
	* @since 2.4.5.9
	* @return the id of the inserted record or 0 on failure.
	*/

	public function save()
	{
		\Purplebeanie\Util\Pbdebug::log_msg('Event::save() - Trying to save the event back to the database','com_pbbooking');
		$db = \JFactory::getDbo();
		$config =$GLOBALS['com_pbbooking_data']['config'];

		//convert the times to strings
		$event_arr = (array)$this;
		$event_arr['dtstart'] = $this->dtstart->format(DATE_ATOM);
		$event_arr['dtend'] = $this->dtend->format(DATE_ATOM);
		if (isset($this->r_end)) 
			$event_arr['r_end'] = $this->r_end;			//r_end doesn't get converted to DateTime in constructor

		if (!isset($this->id) || $this->id == null) {
			//we never need to write this to google cal as all events start of as pending only!
			\Purplebeanie\Util\Pbdebug::log_msg('Event::save() - The event is a new event that is being saved','com_pbbooking');
			$result = $db->insertObject('#__pbbooking_events',new \JObject($event_arr));
			if ($result) {
				$this->id = $db->insertid();
				$pending_id = $db->insertid();
				if ($GLOBALS['com_pbbooking_data']['calendars'][$this->cal_id]->enable_google_cal == 1)
					
				\Purplebeanie\Util\Pbdebug::log_msg('Event::save() successful creation of new pending event with id '.$this->id,'com_pbbooking');
				return $pending_id;
			} else {
				echo $db->getErrorMsg();
				return 0;
			}
		}

		if (isset($this->id) && $this->id > 0) {
			\Purplebeanie\Util\Pbdebug::log_msg('Event::save() - Saving existing event with id '.$this->id,'com_pbbooking');

			$result = $db->updateObject('#__pbbooking_events',new \JObject($event_arr),'id');
			if ($result) {
				\Purplebeanie\Util\Pbdebug::log_msg('Event::save() successful update of event with id '.$this->id,'com_pbbooking');
				$this->summary = $this->getSummary();							//this line is needed to display a relevant summary in gcal
				\Purplebeanie\Google\Syncer::addEventToQueue($this,'create');
				return $this->id;
			} else {
				\Purplebeanie\Util\Pbdebug::log_msg('Event::save() update of event with id '.$this->id.' failed.','com_pbbooking');
				return 0;
			}
		}
	}

	/**
	* updates an event from an array.  Most likely the POST array
	* @access public  
	* @since 2.4.5.11a2
	* @param array
	* @return bool
	*/

	public function update($details)
	{
            // First before making changes add to the delete queue
            \Purplebeanie\Google\Syncer::addEventToQueue($this,'delete');	

            $db = \JFactory::getDbo();
            $cf_email = $db->setQuery('select * from #__pbbooking_customfields where is_email = 1')->loadObject();
            $service = $db->setQuery('select * from #__pbbooking_treatments where id = '.(int)$details['treatment_id'])->loadObject();

            $e_array = array();

            foreach ($this->fields as $field) {
                    if ($field!= 'id') {
                            $e_array[$field] = (isset($details[$field])) ? $details[$field] : $this->$field;
                            $this->$field = $e_array[$field];
                    }
            }

            $customfields = json_decode($this->customfields_data,true);
            foreach ($customfields as &$data) {
                    $data['data'] = $details[$data['varname']];
            }

            $e_array['email'] = $details[$cf_email->varname];
            $e_array['customfields_data'] = json_encode($customfields);
            $e_array['id'] = $this->id;
            $e_array['dtstart'] = $e_array['dtstart']->format(DATE_ATOM);
            $e_array['dtend'] = $e_array['dtend']->format(DATE_ATOM);
            $e_array['service_id'] = $details['treatment_id'];

            $db->getQuery(true);
            if ($db->updateObject('#__pbbooking_events',new \JObject($e_array),'id')) {
                    $this->summary = $this->getSummary();
                    \Purplebeanie\Google\Syncer::addEventToQueue($this,'create');
                    return true;
            }
            else
                    \Purplebeanie\Util\Pbdebug::log_msg('Event::update - problems with update.  Database said: '.$db->getErrorMsg(),'com_pbbooking');

            return false;
	}


	/**
	* creates a pending event from the POST array and tags the verified status as 0
	* @param array
	* @return bool - true for successful creation false for fail
	* @since 2.4.5.11a4
	*/

	public function createFromPost($data = array())
	{
		$error = false;																//a bool as an error flag.
		foreach ($this->fields as $field) {
			$this->$field = (isset($data[$field])) ? $data[$field] : null;
			if (in_array($field,$this->requiredfields) && ($this->$field == null || $this->$field == '')) {
				\Purplebeanie\Util\Pbdebug::log_msg('Event::createFromPost() failed.  '.$field.' is missing but required','com_pbbooking');
				$error = true;
			}
		}

		//now convert the dtstart & dtend objects.
		$this->dtstart = date_create($this->dtstart,new \DateTimeZone(PBBOOKING_TIMEZONE));
		$this->dtend = date_create($this->dtend,new \DateTimeZone(PBBOOKING_TIMEZONE));

		//build the custom fields json array
		$customfields = $this->customfields;
		foreach ($customfields as $field) {
			$field->data = (isset($data[$field->varname])) ? $data[$field->varname] : null;
			if ($field->is_required == 1 && !isset($data[$field->varname])) {
				$error = true;
				\Purplebeanie\Util\Pbdebug::log_msg('Event::createFromPost() failed. Custom field with varname '.$field->varname.' is missing but required','com_pbbooking');
			}
			if ($field->is_email == 1)
				$this->email = $data[$field->varname];
		}
		$this->customfields_data = json_encode($customfields);

		if ($error)
			return false;
		else
			return true;

	}

	/**
	* checks to see whether the event is valid. ie. is the diary free, can it be booked etc. etc.
	* @since 2.4.5.11a4
	* @access public
	* @return bool - true for is valid.  False for not.
	*/

	public function isValid()
	{
		$db = \JFactory::getDbo();
		$config = $GLOBALS['com_pbbooking_data']['config'];
		$service = $db->setQuery('select * from #__pbbooking_treatments where id = '.(int)$this->service_id)->loadObject();

		//load up the calendar for the nominated treatment
		$cal = $GLOBALS['com_pbbooking_data']['calendars'][$this->cal_id];
		
		//before we even get that far ahead we may be booking over the end of a shift end / day end ..... we need to check for that in here now as well with variable treatment durations
		//array('shift name'=>array('start_time'=>array(start_hour,start_min),'end_time'=>array(end_hour,end_min)))
		$shift_times = \Pbbooking\Pbbookinghelper::get_shift_times();
		foreach ($shift_times as $shift_time) {
			$shift_start = date_create($this->dtstart->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
			$shift_end = date_create($this->dtstart->format(DATE_ATOM), new \DateTimeZone(PBBOOKING_TIMEZONE));
			$shift_start->setTime((int)$shift_time['start_time']['start_hour'],(int)$shift_time['start_time']['start_min'],0);
			$shift_end->setTime((int)$shift_time['end_time']['end_hour'],(int)$shift_time['end_time']['end_min'],59);

			if ($this->dtstart >=$shift_start && $this->dtstart<= $shift_end) {
				if ($this->dtstart>$shift_end)
					return false;
			}
		}

		if (!$cal->is_free_from_to($this->dtstart,$this->dtend)) {
			\Purplebeanie\Util\Pbdebug::log_msg('Event::isValidEvent() event is valid from '.$this->dtstart->format(DATE_ATOM).' to '.$this->dtend->format(DATE_ATOM).' in calendar '.(int)$this->cal_id,'com_pbbooking');
			return true;
		} else {
			\Purplebeanie\Util\Pbdebug::log_msg('Event::isValidEvent() event is NOT valid from '.$this->dtstart->format(DATE_ATOM).' to '.$this->dtend->format(DATE_ATOM).' in calendar '.(int)$this->cal_id,'com_pbbooking');			
			return false;
		}
	}

	/**
	* marks a pending event as validated
	* @since 2.4.5.11a4
	* @access public
	* @param bool force true to force the event to validate regardless of whether it is free or not.  default is false
	* @return bool - true if validated and false if not validated
	*/

	public function validate($force = false)
	{
		\Purplebeanie\Util\Pbdebug::log_msg('Event::validate() - trying to validate event with id '.(int)$this->id,'com_pbbooking');

		$db = \JFactory::getDbo();
		$config = $GLOBALS['com_pbbooking_data']['config'];

		$customfields_data = json_decode($this->customfields_data,true);
		$service = $db->setQuery('select * from #__pbbooking_treatments where id = '.$this->service_id)->loadObject();
		$calendar = $db->setQuery('select * from #__pbbooking_cals where id = '.(int)$this->cal_id)->loadObject();

		if (!$force) {
			if (!$this->isValid()) {
				\Purplebeanie\Util\Pbdebug::log_msg('Event::validate() event with id '.(int)$this->id.' is no longer valid','com_pbbooking');
				return false;
			}
		}

		\Purplebeanie\Util\Pbdebug::log_msg('Event::validate() event with id '.(int)$this->id.' is still valid and can be validated','com_pbbooking');

		//event is still valid so can all be validated now.
		$this->verified = 1;
		$this->save();

		\Purplebeanie\Util\Pbdebug::log_msg('Event::validate() successful validation of event with id '.(int)$this->id,'com_pbbooking');

		return true;
	}

	/**
	* returns the event summary built up from the custom field data
	* @param    String    $leading      Any text that is needed for the lead in
	* @param    String    $seperator    Seperator between fields
	* @param    String    $trailing     Any text that is needed for lead out
	*
	* @return   String    The prepared html summary string.
	*/

	public function getSummary($leading = '', $seperator = ' ', $trailing = '')
	{
		$db = \JFactory::getDbo();
		$config = $GLOBALS['com_pbbooking_data']['config'];
		$manage_fields = json_decode($config->manage_fields,true);

		if (count($manage_fields)>0 && $this->externalevent ==0) {
			//user wants a custom summary

			$admin_array = array();						//holds the admin fields
			if ($this->customfields_data && $this->customfields_data != '') {
				foreach (json_decode($this->customfields_data,true) as $field) {
					if (in_array($field['varname'],json_decode($config->manage_fields,true))) {
						$admin_array[] = $field['data'];
					}
				}
			} 

			//check if the they want the service details in the manage diaries.
			if (in_array('_service_',json_decode($config->manage_fields,true))) {
				//we need to add the service
				$service = $db->setQuery('select * from #__pbbooking_treatments where id = '.(int)$this->service_id)->loadObject();
				if ($service)
				{
					$admin_array[] = strtoupper($service->name);
				}
			}

			return $leading . implode($seperator, $admin_array) . $trailing;
		}

		//no custom fields or we're happy with the defautl this could be either an external event or not!
		if ($this->externalevent == 0) {
			$service = $db->setQuery('select * from #__pbbooking_treatments where id = '.(int)$this->service_id)->loadObject();
			$summary = $service->name.' for ';
			$fname = null;
			$lname = null;

			foreach (json_decode($this->customfields_data,true) as $field) {
				if (isset($field['is_first_name']) && $field['is_first_name'] == 1)
					$fname = $field['data'];
				if (isset($field['is_last_name']) && $field['is_last_name'] == 1)
					$lname = $field['data'];					
			}
			return $leading . implode($seperator, array($summary, $fname, $lname)) . $trailing;
		} 

		//fallback...
		return $this->summary;
	}

	/**
	* just returns the custom fields as field name = data as a string
	* @since 2.4.5.11a4
	* @access public
	* @return string
	*/

	public function getDescription()
	{
		$ret_string = '';
		foreach (json_decode($this->customfields_data,true) as $field) {
			$ret_string.=$field['fieldname']." = ".$field['data']."\n";
		}

		return $ret_string;
	}

	/**
	* returns the calendar object for an event
	* @since 2.4.5.11a4
	* @access public
	* @return object
	*/

	public function getCalendar()
	{
		$db = \JFactory::getDbo();
		$calendar = $db->setQuery('select * from #__pbbooking_cals where id = '.(int)$this->cal_id)->loadObject();
		return $calendar;
	}

	/**
	* returns the service object for the event
	* @since 2.5.4.11a4
	* @access public
	* @return object
	*/

	public function getService()
	{
		$db = \JFactory::getDbo();
		$service = $db->setQuery('select * from #__pbbooking_treatments where id = '.(int)$this->service_id)->loadObject();
		return $service;
	}

	/**
	 * makes an event recurring and creates the child events that are needed in the database
	 * @since 2.4.5.11a12
	 * @param array $data an array containing the recurring data.  most likely this will be from a $_POST array.
	 * @access public
	 */

	public function makeRecurring($data = array())
	{
		//r_int,r_freq,r_end

		//first create the recurrance data in the parent event...
		$this->r_int = $data['interval'];
		$this->r_freq = $data['frequency'];
		$this->r_end = date_create($data['recur_end'],new \DateTimeZone(PBBOOKING_TIMEZONE));
		$this->save();

		//now create the child events.
		$evRecStart = clone $this->dtstart;
		$evRecEnd = clone $this->r_end;
		while ($evRecStart <= $evRecEnd) {
			$evRecStart->modify('+ '.$this->r_int.' '.$this->r_freq);
			$childEvent = clone $this;

			//unset the id and gcal id as these are created per event
			unset($childEvent->id);
			unset($childEvent->gcal_id);

			//modify the dtstart and the dtend
			$childEvent->dtstart->modify('+ '.$this->r_int.' '.$this->r_freq);
			$childEvent->dtend->modify('+ '.$this->r_int.' '.$this->r_freq);

			//set the referene for the parent
			$childEvent->parent = $this->id;

			//save the event
			$childEvent->save();
			$childEvent->validate(true);
		}

	}

	/**
	 * this method shouldn't really be needed but is used to update the validation token without calling event->save()
	 * this really just exists as a temporary work around to issue #196
	 */

	public function updateValidationToken()
	{
		$db = \JFactory::getDbo();

		$db->setQuery('update #__pbbooking_events set `validation_token` = "'.$db->escape($this->validation_token).'" where id = '.(int)$this->id)->execute();
	}

	/**
	 * this is used mainly from views to get the next five future sibs of a current event.
	 * 
	 * @since    2.4.5.11a12
	 * @param    integer    $occurances    the number of future recurrances to get
	 *
	 * @return   array      an array containing the objects.
	 */

	public static function getFutureSiblings($occurances = 5)
	{
		$db = \JFactory::getDbo();

		$futureSibs = $db->setQuery('select * from #__pbbooking_events where dtstart >= "'.$this->dtstart.'" order by dtstart ASC limit '.(int)$occurances)->loadObjectList();
		return $futureSibs;
	}

	public function getFirstName()
	{
		if (!isset($this->customfields_data) || $this->customfields_data == '')
			return '';

		foreach (json_decode($this->customfields_data,true) as $cfd) {
			if ((int)$cfd['is_first_name'] == 1)
				return $cfd['data'];
		}
			
	}

	public function getLastName()
	{
		if (!isset($this->customfields_data) || $this->customfields_data == '')
			return '';

		foreach (json_decode($this->customfields_data,true) as $cfd) {
			if ((int)$cfd['is_last_name'] == 1)
				return $cfd['data'];
		}
			
	}

}

?>