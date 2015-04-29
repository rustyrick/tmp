<?php
/**
 * @package    PurpleBeanie.PBBooking
 * @link http://www.purplebeanie.com
 * @license    GNU/GPL
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');

 

class PbbookingControllermanage extends JControllerLegacy
{
	

	/**
	* returns a json encoded array of events for the fullcalendar plugin in manage diaries
	* used by the new style manage diaries interface.
	* @access public
	* @since 2.4.5.9
	*/

	public function get_calendar_events()
	{
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
			\Purplebeanie\Util\Pbdebug::log_msg('Manage::get_calendar_events - parsing new event with id '.(int)$event->id,'com_pbbooking');
			if ((int)$event->id>0) {
				$ev_obj = new \Pbbooking\Model\Event($event->id);
				if ($ev_obj->externalevent == 1) {
					$events[] = array('id'=>$event->id,'title'=>htmlspecialchars($ev_obj->getSummary()),'allDay'=>false,'start'=>$event->dtstart->format(DATE_ATOM),'end'=>$event->dtend->format(DATE_ATOM),'externalevent'=>$ev_obj->externalevent,'editable'=>false,'startEditable'=>false,'durationEditable'=>false);
				} else {
					$events[] = array('id'=>$event->id,'title'=>htmlspecialchars($ev_obj->getSummary()),'allDay'=>false,'start'=>$event->dtstart->format(DATE_ATOM),'end'=>$event->dtend->format(DATE_ATOM),'externalevent'=>$ev_obj->externalevent);
				}
			}
		}

		echo json_encode($events);
	}

	/**
	* update_calendar_event responds to the drop event in the new manage diaries interface to write an event back to teh database
	* @access public
	* @since 2.4.5.9
	*/

	public function update_calendar_event()
	{
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
	* AJAX method
	* used to update an event from the full calendar plugin
	* @access public
	* @since 2.4.5.9
	*/

	public function ajax_edit()
	{
		$db = JFactory::getDbo();
		$input = JFactory::getApplication()->input;

		$view = $this->getView('manage','raw');
		$view->setLayout('ajax_edit_event');

		//load data
		$view->config = $GLOBALS['com_pbbooking_data']['config'];
		$view->event = $db->setQuery('select * from #__pbbooking_events where id = '.$db->escape($input->get('event_id',null,'integer')))->loadObject();
		$view->services = $db->setQuery('select * from #__pbbooking_treatments')->loadObjectList();
		$view->cals = $db->setQuery('select * from #__pbbooking_cals')->loadObjectList();
		$view->customfields = $db->setQuery('select * from #__pbbooking_customfields order by ordering ASC')->loadObjectList();
		$view->shift_times = \Pbbooking\Pbbookinghelper::get_shift_times();
		$view->selected_shift = \Pbbooking\Pbbookinghelper::get_shift_for_appointment($view->event->id);
		$view->date = date_create($view->event->dtstart, new DateTimeZone(PBBOOKING_TIMEZONE));

		//prep the customfield data
		$cf_data = array();
		foreach (json_decode($view->event->customfields_data,true) as $data)
			$cf_data[$data['varname']] = $data['data'];
		$view->customfields_data = $cf_data;

		$view->display();
	}

	/**
	* AJAX method
	* saves a record from an AJAX post request
	* @access public
	* @since 2.4.5.9
	*/

	public function ajax_save()
	{
		$input = JFactory::getApplication()->input;

		\Purplebeanie\Util\Pbdebug::log_msg('manage::ajax_save()','com_pbbooking');
		$db = JFactory::getDbo();

		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$event = new \Pbbooking\Model\Event($input->get('id',null,'integer'));	

			$t_start = explode(':',$input->get('dtstart',null,'string'));
			$t_end = explode(':',$input->get('dtend',null,'string'));
			$dtstart = date_create($input->get('date',null,'string'),new DateTimeZone(PBBOOKING_TIMEZONE));
			$dtstart->setTime((int)$t_start[0],(int)$t_start[1],(int)$t_start[2]);
			$dtend = date_create($dtstart->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
			$dtend->setTime((int)$t_end[0],(int)$t_end[1],(int)$t_end[2]);

			$_POST['dtstart'] = $dtstart;
			$_POST['dtend'] = $dtend;

			$result = $event->update($_POST);		

			if (isset($_POST['reccur']) && (int)$_POST['reccur'] == 1) {
				//TODO: need some smarts around updating with recurring events.
				//$event->makeRecurring($_POST);
 			}	

			if ($result)
				echo json_encode(array('status'=>'success'));
			else 
				echo json_encode(array('status'=>'fail','message'=>$db->getErrorMsg()));
		}
	}

	/**
	* AJAX method
	* deletes an event
	* @access public
	* @since 2.4.5.9
	*/

	public function ajax_delete()
	{

		$db = JFactory::getDbo();
		$input = JFactory::getApplication()->input;

		$eid = $input->get('id',null,'integer');
		$delChildren = $input->get('delete_children',0,'integer');

		\Purplebeanie\Util\Pbdebug::log_msg('manage::ajax_delete() for event_id '.$eid,'com_pbbooking');

		if ($eid) {
			//do delete event
			$deleted = \Pbbooking\Model\Calendar::delete_event((int)$eid,$delChildren);
			if ($deleted)
				echo json_encode(array('status'=>'success'));
			else 
				echo json_encode(array('status'=>'fail','message'=>JText::_('COM_PBBOOKING_DELETE_PROBLEM')));
		}

	}

	/**
	* AJAX method
	* renders the form to create an event
	* @access public
	* @since 2.4.5.9
	*/

	public function ajax_create()
	{
		\Purplebeanie\Util\Pbdebug::log_msg('manage::ajax_create()','com_pbbooking');
		$input = JFactory::getApplication()->input;
		$db = JFactory::getDbo();

		if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			$date = $input->get('date',null,'string');
			if ($date) {
				//we got valid date so let's create an object and load the create form
				$dtstart = date_create($date,new DateTimeZone(PBBOOKING_TIMEZONE));

				$view = $this->getView('manage','raw');
				$view->setLayout('ajax_create_event');

				//load data
				$view->customfields = $db->setQuery('select * from #__pbbooking_customfields order by ordering ASC')->loadObjectList();
				$view->services = $db->setQuery('select* from #__pbbooking_treatments')->loadObjectList();
				$view->config = $GLOBALS['com_pbbooking_data']['config'];
				$view->date = $dtstart;
				$view->cals = $db->setQuery('select * from #__pbbooking_cals')->loadObjectList();
				$view->shift_times = \Pbbooking\Pbbookinghelper::get_shift_times();

				//now display the view
				$view->display();
			}
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			\Purplebeanie\Util\Pbdebug::log_msg('manage::ajax_create() POST event','com_pbbooking');
			$service = $db->setQuery('select * from #__pbbooking_treatments where id = '.(int)$_POST['service_id'])->loadObject();
			$_POST['treatment_time'] = date_create($input->get('dtstart',null,'string'),new DateTimeZone(PBBOOKING_TIMEZONE))->format('H:i');
			$_POST['duration'] = $service->duration;
			$event = new \Pbbooking\Model\Event();
			$event->createFromPost($_POST);
			$event_id = $event->save();
			if ($event_id) {
				$validated = $event->validate();
				if ($validated) {
					if (isset($_POST['reccur']) && (int)$_POST['reccur'] == 1) {
						$event->makeRecurring($_POST);
					}
					echo json_encode(array('status'=>'success'));
				} else {
					echo json_encode(array('status'=>'fail','message'=>JText::_('COM_PBBOOKING_VALIDATION_ERROR')));
				}
			} else {
				echo json_encode(array('status'=>'fail','message'=>JText::_('COM_PBBOOKING_CREATE_ERROR').' '.$db->getErrorMsg()));
			}
		}
	}

	/**
	* AJAX method
	* used to return a json encoded array of search results
	* @access public
	* @since 2.4.5.10
	*/

	public function ajax_search()
	{
		$input = JFactory::getApplication()->input;
		$db = JFactory::getDbo();

		$search = $input->get('search',null,'string');
		if ($search) {
			$results = $db->setQuery('select * from #__pbbooking_events where customfields_data like "%'.$db->escape($search).'%"')->loadObjectList();
			foreach ($results as $result) {
				$ev = new \Pbbooking\Model\Event($result->id);
				$result->summary = $ev->getSummary();
			}
			echo json_encode($results);
		}
	}

}