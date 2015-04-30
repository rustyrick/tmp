<?php

/**
* @package		PurpleBeanie.PBBooking
* @license		GNU General Public License version 2 or later; see LICENSE.txt
* @link		http://www.purplebeanie.com this is a test.
*/

namespace Pbbooking\Model;


class Calendar

{

public $events;
public $kTimeslots;
public $cal_id;
public $name;
public $dayEventMap;


/**
* designated constructor for the calendar model
* @param object the config object.
*/

function __construct($config) {
	
	//set the timezone for the calendars
    date_default_timezone_set(PBBOOKING_TIMEZONE);	
	
	//set a default cal_id;
	$this->cal_id = -1;

	//load the config once to use for the whole model
	$this->config = $config;
}


/**
* Loads the events from the database
*
* @param array An array of calendar ID's to be parsed
* @param datetime an optional starting date from
* @param datetime an optional starting date to
* @param Boolean    true of false to load events up as part of the calendar load.
* @return bool or false loads $this->events with appointments or false on failure to communication with gcal.
* @since 2.1
*/

function loadCalendarFromDbase($cals,$dtfrom=null,$dtto = null,$loadEvents = true)
{
	//set the cal_id for future calendars
	$this->cal_id = (count($cals)>1) ? 0 : $cals[0];

	$db = \JFactory::getDBO();
	$events = array(); //holds the parsed events returned from the specific load method
	
	foreach($cals as $cal) {
		//check the calendar and see where it is stored
		$db_cal = $db->setQuery('select * from #__pbbooking_cals where id = '.(int)$cal)->loadObject();
		$this->name = $db_cal->name;
		$this->languages = $db_cal->languages;
		$this->hours = $db_cal->hours;
		$this->color = $db_cal->color;
		$this->enable_google_cal = $db_cal->enable_google_cal;
		$this->gcal_id = $db_cal->gcal_id;
		$this->groupbookings = $db_cal->groupbookings;
		$this->groupclass_max = $db_cal->groupclass_max;
		$this->calendar_schedule = json_decode($db_cal->calendar_schedule,true);
		$this->id = $db_cal->id;

		if ($loadEvents)
			$events = array_merge($events,self::_get_events_from_dbase($db_cal,$dtfrom,$dtto));
	}

	if ($loadEvents)
		$this->events = $events;

	return true;
}



/**
* gets events for a specified date range from the database
* @param object the calendar object from the database
* @param datetime an optional starting date from
* @param datetime an option starting dateto
* @return array of events
* @since 2.4.5
* @access private
*/

private function _get_events_from_dbase($cal,$dtfrom,$dtto)
{
	$db = \JFactory::getDbo();
	$events = array();
	\Purplebeanie\Util\Pbdebug::log_msg('calendar::_get_events_from_dbase from for calendar id = '.(int)$cal->id,'com_pbbooking');

	if ($dtfrom && $dtto) {
		\Purplebeanie\Util\Pbdebug::log_msg('loadCalendarFromDbase() - $dtfrom and $dtto are set','com_pbbooking');
		$db->setQuery('select * from #__pbbooking_events where cal_id = '.$db->escape($cal->id).' and dtstart>= "'.$dtfrom->format(DATE_ATOM).'" and dtstart<= "'.$dtto->format(DATE_ATOM).'" and verified = 1 and deleted =0');	
	} else {
		\Purplebeanie\Util\Pbdebug::log_msg('loadCalendarFromDbase() - no override dates set','com_pbbooking');
		$db->setQuery("select * from #__pbbooking_events where cal_id = ".$db->escape($cal->id)." and verified = 1 and deleted = 0");
	}
	$cal_events = $db->loadObjectList();
	if (count($cal_events) > 0 ) {
		foreach ($cal_events as $cal_event) {
			//$date_string = date(DATE_ATOM,$cal_event->dtend);
			$event = new \Pbbooking\Model\Event($cal_event->id);
			$event->summary = $cal_event->summary;
			$event->dtend = date_create($cal_event->dtend,new \DateTimeZone(PBBOOKING_TIMEZONE));
			$event->dtstart = date_create($cal_event->dtstart,new \DateTimeZone(PBBOOKING_TIMEZONE));
			$event->description = $cal_event->description;
			$event->id = $cal_event->id;
			$event->r_int = $cal_event->r_int;
			$event->r_end = $cal_event->r_end;
			$event->r_freq = $cal_event->r_freq;
			$events[] = $event;

			if (!isset($this->dayEventMap[$event->dtstart->format('dmY')]))
				$this->dayEventMap[$event->dtstart->format('dmY')] = array();

			$this->dayEventMap[$event->dtstart->format('dmY')][] = $event;
		}
	}

	return $events;
}



/**
* used to calculate whether a date is a blocked date
*
* @param datetime the date to block
* @return bool true or false true = is open and false = is closed
*/

function isOpen($date)
{

	//is the date a block day?
	$blocked_days = $GLOBALS['com_pbbooking_data']['blockdays'];
	$config = \JFactory::getConfig();
	$offset = $config->get('offset');
	
	if (count($blocked_days)>0) {
		foreach ($blocked_days as $blocked_day) {
			$block_from = date_create($blocked_day->block_start_date,new \DateTimeZone(PBBOOKING_TIMEZONE));
			
			$block_to = date_create($blocked_day->block_end_date,new \DateTimeZone(PBBOOKING_TIMEZONE));
			
			if ($date>=$block_from && $date<=$block_to && in_array($this->cal_id,explode(',',$blocked_day->calendars))) {
				\Purplebeanie\Util\Pbdebug::log_msg('Calendar model found single block at '.$date->format(DATE_ATOM),'com_pbbooking');
				return false;
			}
			$blocked_day->r_dtend = date_create($blocked_day->r_end,new \DateTimeZone(PBBOOKING_TIMEZONE));
			$blocked_day->r_dtend->setTimezone(new \DateTimeZone($offset));
			if ((isset($blocked_day->r_int) && isset($blocked_day->r_freq)) && $date <= $blocked_day->r_dtend) {
				while (($block_from<=$date && $block_to <= $date) || ($block_from<=$blocked_day->r_dtend && $block_to <= $blocked_day->r_dtend)) {
					$block_from->modify('+ '.$blocked_day->r_int.' '.$blocked_day->r_freq);
					$block_to->modify('+ '.$blocked_day->r_int.' '.$blocked_day->r_freq);
					if ($date>=$block_from && $date<=$block_to && in_array($this->cal_id,explode(',',$blocked_day->calendars))) {
						\Purplebeanie\Util\Pbdebug::log_msg('Calendar model found recurrant block with id '.$blocked_day->id.' at '.$date->format(DATE_ATOM),'com_pbbooking');
						return false;
					}
				}
			}
		}
	}
	return true;		
}

/**
* is_free_from_to() - returns either an event or false (ie false = available) to indicate whether the nominated calendar is free from from date to to date used for newer views based on times
* 						since version 3.0.0 this does some common checks then branches to separate methods for group or non group
*						calendars
*
* @param datetime from_date - the datetime to check from
* @param datetime to_date - the datetime to check to
* @param bool is_admin - whether the method is being called from an admin view or not	
* @return event the event that is in the time slot if one is there.
* @since 2.2
*/	
public function is_free_from_to($from_date,$to_date,$is_admin=false) {

	\Purplebeanie\Util\Pbdebug::log_msg('is_free_from_to(): checking dates $from_date '.$from_date->format(DATE_ATOM).' and $to_date '.$to_date->format(DATE_ATOM).' in calendar '.$this->cal_id,'com_pbbooking');

	//can we bail early due to being outside trading hours?  There are a couple of test cases
	//		1. the time is outside the trading hours
	//		2. the day it not a trading day
	$pbb_config = $GLOBALS['com_pbbooking_data']['config'];
	
	$trading_hours = ($this->cal_id != 0 && $this->hours > '') ? json_decode($this->hours,true) : json_decode($pbb_config->trading_hours,true);
	
	if (!$is_admin) {
		if ($trading_hours[$from_date->format('w')]['status'] == 'open') {
			//catches for outside trading times.
			$str_opening_time = $trading_hours[$from_date->format('w')]['open_time'];
			$str_closing_time = $trading_hours[$from_date->format('w')]['close_time'];
			$opening_time_arr = str_split($str_opening_time,2);
			$closing_time_arr = str_split($str_closing_time,2);
			$opening_date = date_create($from_date->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
			$closing_date = date_create($from_date->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
			$opening_date->setTime((int)ltrim($opening_time_arr[0],'0'),(int)ltrim($opening_time_arr[1],'0'));
			$closing_date->setTime((int)ltrim($closing_time_arr[0],'0'),(int)ltrim($closing_time_arr[1],'0'));
			if ($from_date < $opening_date || $from_date > $closing_date) return true;
			if ($to_date > $closing_date) return true;
		} else {
			//catches for non-trading day
			return true;
		}
	}
	
	//check to see if it's in a block date range.
	if (!$this->isOpen($from_date)) {
		return true;
	}

	//a check to see if the calendar has scheduled class times
	if ( $this->groupbookings == 1 ) 
	{

		return $this->is_free_from_to_group($from_date,$to_date,$is_admin=false);
	
	} else {
		$event =  $this->is_free_from_to_nongroup($from_date,$to_date,$is_admin=false);
		return $event;
	}


}


    public function possibleToBookAnyServiceAtTime($dt)
    {
        $bookable = false; // assume it's not bookable
        $dtend    = date_create($dt->format(DATE_ATOM), new \DateTimeZone(PBBOOKING_TIMEZONE))->setTime(23,59,59);

        foreach ($this::get_services_for_calendar($this->cal_id) as $service)
        {
            if ($this->can_book_treatment_at_time($service->id, $dt, $dtend))
            {
                $bookable = true;
                break;
            }
        }
        return $bookable;
    }


private function is_free_from_to_nongroup($from_date,$to_date,$is_admin=false)
{

	//check day eventmap first.  If it doesn't exist there are presently no bookings for current day
	if (!isset($this->dayEventMap[$from_date->format('dmY')])) {
		return false;									
	}

	$free = true;
	$bookedEvent = null;


	foreach($this->dayEventMap[$from_date->format('dmY')] as $event) {
	//foreach($this->events as $event) {

		$event->dtend->modify("-1 second");
		
		if ($event->dtend >= $from_date && $event->dtend <= $to_date) {
			$free = false;
			$bookedEvent = $event;
			return $bookedEvent;
		}
		
		//check for multi day events
		if ($event->dtstart <= $from_date && $event->dtend >= $to_date) {
			$free = false;
			$bookedEvent = $event;
			return $bookedEvent;
		}
	}

	return $bookedEvent;

}


private function is_free_from_to_group($from_date,$to_date,$is_admin=false)
{
	$is_closed = true;
	foreach ( $this->calendar_schedule as $classtime )
	{
		if ($from_date->format('w') == $classtime['dayofweek'])
		{
			//it's a scheduled day now just check times.
			list($starthour,$startmin) = explode(':',$classtime['time']);
			list($endhour,$endmin) = explode(':',$classtime['endtime']);
			$class_start = date_create($from_date->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE))
										->setTime( (int)ltrim($starthour,0) , (int)ltrim($startmin,0) , 0 );
			$class_end = date_create($from_date->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE))
										->setTime( (int)ltrim($endhour,0) , (int)ltrim($endmin,0) , 0 );
			
			if ( $from_date>=$class_start && $to_date <= $class_end )
				$is_closed = false;
		}
	}

	if ($is_closed)
		return true;

	//group training slot is open so just need to check if it is full
	//check day eventmap first.  If it doesn't exist there are presently no bookings for current day
	if (!isset($this->dayEventMap[$from_date->format('dmY')])) {
		return false;									
	}

	$booked_events = array();
	foreach ($this->dayEventMap[$from_date->format('dmY')] as $event) {
		$event->dtend->modify("-1 second");
		
		if ($event->dtend >= $from_date && $event->dtend <= $to_date) {
			$free = false;
			$booked_events[] = $event;

		}
		
		//check for multi day events
		if ($event->dtstart <= $from_date && $event->dtend >= $to_date) {
			$free = false;
			$booked_events[] = $event;
		}

	}
	if (count($booked_events)>=$this->groupclass_max)
		return $booked_events[0];
	else
		return false;
}

/**
* can_book_treatment_at_time() - returns bool to indicate whether a treatment can be booked at a specified time.
* 								there are a number of reasons why it might not be possible:
* 										- slot is busy? caught by is_free_from_to
*										- too close to end of shift
*										- not enough time before next treatment
*
* @param int treatment_id - the id of the treatement to be booked
* @param datetime treatment_date - the datetime to be booked
* @param datetime shift_end - the ending time of the shift
* @returns bool
* @since 2.2
*/	

public function can_book_treatment_at_time($treatment_id,$treatment_date,$shift_end)
{

	\Purplebeanie\Util\Pbdebug::log_msg('can_book_treatment_at_time: $treatment_id = '.(int)$treatment_id.' and $treatment_date = '.$treatment_date->format(DATE_ATOM).' and $shift_end = '.$shift_end->format(DATE_ATOM),'com_pbbooking');


	$check_date = date_create($treatment_date->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
	$db = \JFactory::getDbo();
	$db->setQuery('select * from #__pbbooking_treatments where id = '.$db->escape((int)$treatment_id));
	$treatment = $db->loadObject();
	
	$config = $GLOBALS['com_pbbooking_data']['config'];

	//can the calendar actually accept the treatment???
	if (!in_array($this->cal_id,explode(',',$treatment->calendar)))
		return false;


    if ($this->exceededMaxBookingsForDay($check_date)) {
        return false;
    }


    if ($treatment->duration<= $config->time_increment) {
        return true;
    }

	//all remaining are where treatment->duration > time_interval
	$poss_book = true;
	$treatment_end = date_create($check_date->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
	//$treatment_end->modify('+'.($treatment->duration-1).'minutes');
	$treatment_end->modify('+'.($treatment->duration).'minutes');	
	
	\Purplebeanie\Util\Pbdebug::log_msg('can_book_treatment_at_time: $treatment_end = '.$treatment_end->format(DATE_ATOM),'com_pbbooking');

	//we could also have a treatment that blows past the end..... need to catch this....
	if ($treatment_end > $shift_end) 
		return false;

	//now check all other conditions...
	while($check_date <= $treatment_end) {
		$slot_end = date_create($check_date->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
		$slot_end->modify('+'.$config->time_increment.' minutes');
		if ($slot_end<= $treatment_end) {
			if ($this->is_free_from_to($check_date,$slot_end)) {
				\Purplebeanie\Util\Pbdebug::log_msg('can_book_treatment_at_time: returning false from $this->is_free_from_to','com_pbbooking');
				return false;
			}
		}
		$check_date->modify('+'.$config->time_increment.' minutes');
	}
	
	return $poss_book;
}


	/**
	* returns the number of bookings on a given date including checking for recurring bookings
	* @param the date to check
	* @return int the number of bookings
	* @since 2.3
	* @todo merge the booking checking code in number_of_bookings_for_date and is_free_from_to
	*/

	public function number_of_bookings_for_date($check_date)
	{
		if (isset($this->dayEventMap[$check_date->format('dmY')]))
			$num_events = count($this->dayEventMap[$check_date->format('dmY')]);
		else
			$num_events = 0;
	
		\Purplebeanie\Util\Pbdebug::log_msg('calendar::number_of_bookings_for_date() with $check_date = '.$check_date->format(DATE_ATOM).' has num_events = '.$num_events,'com_pbbooking');
		return $num_events;


	}
	
	/**
	* returns the calendar utilization expressed as number of booked hours / number of working hours * 100
	* @param datetime the date to calculate from
	* @param datetime the date to calculate to
	* @return float
	* @since 2.4
	* @access public
	*/

	public function get_calendar_utilization($_x_date_from,$date_to)
	{
		$date_from = date_create($_x_date_from->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
		
		\Purplebeanie\Util\Pbdebug::log_msg('get_calendar_utilization with dates $date_from = '.$date_from->format(DATE_ATOM).' $date_to  = '.$date_to->format(DATE_ATOM),'com_pbbooking');
		$db = \JFactory::getDbo();

		$config = $GLOBALS['com_pbbooking_data']['config'];
		$calendar = $db->setQuery('select * from #__pbbooking_cals where id = '.(int)$this->cal_id)->loadObject();

		//calc total "avail hours" for period
		$cal_hours = json_decode($calendar->hours,true);
		$date_from->setTime(0,0,0);
		$date_to->setTime(23,59,59);
		$total_working_minutes = 0;

		$events = $db->setQuery('select * from #__pbbooking_events where dtstart >= "'.$date_from->format(DATE_ATOM).'" AND dtstart <= "'.$date_to->format(DATE_ATOM).'" and cal_id = '.(int)$this->cal_id)->loadObjectList();

		while ($date_from <= $date_to) {
			if ($cal_hours[$date_from->format('w')]['status'] == 'open') {
				$start_arr = str_split($cal_hours[$date_from->format('w')]['open_time'],2);
				$close_arr = str_split($cal_hours[$date_from->format('w')]['close_time'],2);
				$dtstart = new \DateTime($date_from->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
				$dtend = new \DateTime($date_from->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
				$dtstart->setTime((int)$start_arr[0],(int)$start_arr[1],0);
				$dtend->setTime((int)$close_arr[0],(int)$close_arr[1],59);
				$diff = $dtend->diff($dtstart);
				$total_working_minutes += (($diff->format('%h')*60) + $diff->format('%i'));
			}
			
			$date_from->modify('+1 day');
		}
		\Purplebeanie\Util\Pbdebug::log_msg('get_calendar_utilization() - total working minutes for calendar_id = '.$this->cal_id.' = '.$total_working_minutes,'com_pbbooking');

		//now get appt's in date range....
		$total_booked_minutes = 0;
		foreach ($events as $event) {
			$dtstart = date_create($event->dtstart,new \DateTimeZone(PBBOOKING_TIMEZONE));
			$dtend = date_create($event->dtend,new \DateTimeZone(PBBOOKING_TIMEZONE));
			$total_booked_minutes += (($dtstart->diff($dtend)->format('%h')*60)+$dtstart->diff($dtend)->format('%i'));
		}
		\Purplebeanie\Util\Pbdebug::log_msg('get_calendar_utilization() - total hours worked for calendar_id = '.$this->cal_id.' = '.$total_booked_minutes,'com_pbbooking');

		\Purplebeanie\Util\Pbdebug::log_msg('get_calendar_utilization() - calendar_utilization for calendar = '.$this->cal_id.' = '.($total_booked_minutes/$total_working_minutes)*100,'com_pbbooking');

		return ($total_booked_minutes/$total_working_minutes)*100;
		
	}




	/** 
	* deletes an event from the database and checks to see whether it needs to be deleted from google cal.
	* @param int the id of teh event to delete
	* @param int deleteChildren	whether to delete future children or not (0 = don't 1 = do)
	* @return bool true or false
	* @access public
	* @since 2.4.5.1
	*/

	public static function delete_event($event_id,$deleteChildren = 0)
	{
		$db = \JFactory::getDbo();

		$events = $db->setQuery('select * from #__pbbooking_events where id = '.(int)$event_id)->loadObjectList();   //gets the actual event from the database but as an array
		$event = $events[0];
		if ($event->parent != 0 && $deleteChildren != 0) {
			\Purplebeanie\Util\Pbdebug::log_msg('Calendar::delete_event() delete children and have event with Parent','com_pbbooking');
			$futureSibs = $db->setQuery('select * from #__pbbooking_events where parent = '.(int)$event->parent.' and dtstart >= "'.$db->escape($event->dtstart).'"')->loadObjectList();
			\Purplebeanie\Util\Pbdebug::log_msg('Calendar::delete_event() got '.count($futureSibs).' futureSibs','com_pbbooking');
			$events = array_merge($events,$futureSibs);
		}

		if ($event->parent == 0 && $deleteChildren != 0) {
			$futureChildren = $db->setQuery('select * from #__pbbooking_events where parent = '.(int)$event->id.' and dtstart >= "'.$db->escape($event->dtstart).'"')->loadObjectList();
			$events = array_merge($events,$futureChildren);
		}


		//we now have an events array containing either 1 or more events.
		foreach ($events as $event) {
			\Purplebeanie\Google\Syncer::addEventToQueue(new \Pbbooking\Model\Event($event->id),'delete');
			$db->setQuery('update #__pbbooking_events set deleted = 1 where id = '.(int)$event->id)->query();
		}

		//delete has happened...
		return true;
	}


	/**
	* calaculates what the longest booking available is at the given time in the given calendar.  Calendar must already be initialized with events for the given time period.
	* @param datetime the datetime the booking is scheduled to start
	* @return int the "gap" available for booking in minutes
	* @since 2.4.5.9
	*/

	public function get_longest_available_booking($dtstart)
	{		
		if ($this->config->enable_shifts == 0) {
			//2 the end of the day....
			$trading_hours = json_decode($this->config->trading_hours,true);
			$end_time = str_split($trading_hours[$dtstart->format('w')]['close_time'],2);
			$dtend = date_create($dtstart->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
			$dtend->setTime((int)$end_time[0],(int)$end_time[1],59);
		} else {
			//1 the end of the shift - maybe move this method this part out into the helper later as I can think of other sectiosn that might need it!
			$groupings = \Pbbooking\Pbbookinghelper::get_shift_times();
			foreach ($groupings as $grouping) {
				$group_dt_start = date_create($dtstart->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
				$group_dt_end = date_create($dtstart->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
				$group_dt_start->setTime((int)$grouping['start_time']['start_hour'],(int)$grouping['start_time']['start_min'],0);
				$group_dt_end->setTime((int)$grouping['end_time']['end_hour'],(int)$grouping['end_time']['end_min'],59);
				if ($dtstart>= $group_dt_start && $dtstart <= $group_dt_end)
					$dtend = date_create($group_dt_end->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
			}
		}

		$dt_int_longest_time = $dtstart->diff($dtend);
		$longest_time = ( (int)$dt_int_longest_time->format('%h') * 60 ) + (int)$dt_int_longest_time->format('%i');

		if (!isset($this->dayEventMap[$dtstart->format('dmY')]))
			return $longest_time;


		foreach ($this->dayEventMap[$dtstart->format('dmY')] as $event) {
			if ($event->dtstart >= $dtstart) {
				$diff = $dtstart->diff($event->dtstart);
				$diff_m = ( (int)$diff->format('%h') * 60 ) + (int)$diff->format('%i');
				$longest_time = ( $diff_m < $longest_time ) ? $diff_m : $longest_time;
			}
		}
		return $longest_time;
	}

	/**
	* get servies for calendar
	* @param int calendar
	* @return array an assoc array of services for this calendar
	* @since 2.4.5.10
	* @access public
	*/

	public static function get_services_for_calendar($calendar)
	{
		$ret_services = array();
		$services = $GLOBALS['com_pbbooking_data']['services'];
		foreach ($services as $service) {
			if (in_array($calendar, explode(',', $service->calendar)))
				$ret_services[] = $service;
		}
		return $ret_services;

	}


	/**
	* returns whether or not there is free time in a given day.  Does not necessarily mean the day is bookable.
	* @since 2.4.5.11a4
	* @access public
	* @param dt the day
	* @return bool true for free time false for none
	*/

	public function timeAvailableForDay($dt)
	{	

		$times = $this->getCalendarTradingHoursForDay(date_create($dt->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE)));


      	if (is_array($times)) {
            $dtstart = $times['dtstart'];
            $dtend = $times['dtend'];
        } else {
            $dtstart = date_create($dt->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE))->setTime(0,0,0);
            $dtend = date_create($dt->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE))->setTime(23,59,59);
        }

		while ($dtstart<=$dtend) {
			$slot_end = date_create($dtstart->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
			$slot_end->modify('+ '.$this->config->time_increment.' minutes');
			if (!$this->is_free_from_to($dtstart,$slot_end)) {
				\Purplebeanie\Util\Pbdebug::log_msg('Calendar::timeAvailableForDay() appt can be booked at '.$dtstart->format(DATE_ATOM).' to '.$slot_end->format(DATE_ATOM).' in cal '.(int)$this->cal_id,'com_pbbooking');				
				return true;
			} else {
			}
			$dtstart->modify('+ '.$this->config->time_increment.' minutes');
		}
		return false;
	}

    /**
     * gets the trading hours for the calendar as a dt object
     *
     * @since     2.4.5.11a14
     * @access    public
     * @param     datetime    Takes a datetime object to check.
     * @return    array       Returns an array containing the dtstart and dtend for the specified day of the week.
     */

    public function getCalendarTradingHoursForDay(\DateTime $checkdate)
    {
        $db = \JFactory::getDbo();

        $cal = $db->setQuery('select * from #__pbbooking_cals where id = '.(int)$this->cal_id)->loadObject();
        $calhours = json_decode($cal->hours,true);

        $dtstart = new \DateTime($checkdate->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));
        $dtend = new \DateTime($checkdate->format(DATE_ATOM),new \DateTimeZone(PBBOOKING_TIMEZONE));

        if ($calhours[$checkdate->format('w')]['status'] == 'open') {
            $starttime = str_split($calhours[$checkdate->format('w')]['open_time'],2);
            $endtime = str_split($calhours[$checkdate->format('w')]['close_time'],2);

            $dtstart->setTime((int)ltrim($starttime[0],'0'),(int)ltrim($starttime[1],'0'),'0');
            $dtend->setTime((int)ltrim($endtime[0],'0'),(int)ltrim($endtime[1],'0'),'59');

            return array('dtstart'=>$dtstart,'dtend'=>$dtend);
        }

        return false;
    }

    /**
     * Checks to see whether the calendar has exceeded its maximum number of bookings for a fiven day.
     * @param DateTime $checkdate
     * @return Boolean  true means the calendar has exceeded it's maximum number of bookings.
     */

    public function exceededMaxBookingsForDay(\DateTime $checkdate)
    {
        //decode the calendar for hours for the given day
        $calhours = json_decode($this->hours,true);
        $maxbookings = (isset($calhours[$checkdate->format('w')]['max_bookings'])) ? $calhours[$checkdate->format('w')]['max_bookings'] : 0;

        if ( ($maxbookings == 0) ||  ($maxbookings >0 && $maxbookings > $this->number_of_bookings_for_date($checkdate)) ) {
            return false;
        }
        else {
            return true;
        }
    }




}


?>