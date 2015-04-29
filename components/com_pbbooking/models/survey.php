<?php

/**
* @package		PurpleBeanie.PBBooking
* @license		GNU General Public License version 2 or la<ter; see LICENSE.txt
* @link		http://www.purplebeanie.com
*/

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 


jimport( 'joomla.application.component.modelitem' );

class PbbookingModelSurvey extends JModelItem

{

	var $table = '#__pbbooking_surveys';
	var $fields = array('id'=>'integer','event_id'=>'integer','date_submitted'=>'string','submission_ip'=>'string','publish'=>'integer','content'=>'string');

	public function __construct($config = array())
	{	
		parent::__construct($config);
	}

	/**
	* saves a completed survey to the database and returns
	* @param array the array containing the survey response
	* @return bool did it save or not?
	* @access public
	* @since 2.4.3
	*/
	
	public function save_survey($survey)
	{
		Purplebeanie\Util\Pbdebug::log_msg('PbbookingModelSurvey::save_survey() - with data '.json_encode($survey),'com_pbbooking');
		$db = JFactory::getDbo();

		//we shouldn't allow surveys to be changed / resubmitted for an event...
		$ex_survey = $db->setQuery('select * from #__pbbooking_surveys where event_id = '.(int)$survey['event_id'])->loadObject();
		if ($ex_survey) {
			Purplebeanie\Util\Pbdebug::log_msg('PbbookingModelSurvey::save_survey() - survey already exists','com_pbbooking');
			return false;
		}

		$survey['date_submitted'] = date_create("now", new DateTimeZone(PBBOOKING_TIMEZONE))->format(DATE_ATOM);

		if ($db->insertObject($this->table,new JObject($survey)))
			return true;
		else
			return false;

	}


	/**
	* get fields - returns the fields in use for the survey that should be collected
	* @return array an assoc array of fields and their types for use in getArray
	* @access public
	* @since 2.4.3
	*/

	public function get_fields()
	{
		$db = JFactory::getDbo();

		return $this->fields;

	}


}



?>


