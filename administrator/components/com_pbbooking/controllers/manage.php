<?php
/**
 * @package    PurpleBeanie.PBBooking
 * @link http://www.purplebeanie.com
 * @license    GNU/GPL
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');
jimport('joomla.html.pagination');
 

class PbbookingControllermanage extends JControllerLegacy
{
	
	function __construct()
	{
	    parent::__construct();
	    
	    $config =JFactory::getConfig();
    	date_default_timezone_set($config->get('offset'));	


		//check the user authorisation
		$input = JFactory::getApplication()->input;
		$user = JFactory::getUser();
		$manageTasks = array('display','edit','create','delete');
		if (in_array($input->get('task','display','string'),$manageTasks)) {
			//check user auth for manage
			if (!$user->authorise('pbbooking.managediaries','com_pbbooking')) {
				$this->setRedirect('index.php?option=com_pbbooking',JText::_('COM_PBBOOKING_ACCESS_MANAGE_DIARIES_NOT_AUTHORISED'));
				return;
			}
		}
	}
	
    /**
     * Method to display the view
     *
     * @access    public
     */
    function display($cachable = false, $urlparams = array())
    {		
		$db = JFactory::getDbo();
		$input = JFactory::getApplication()->input;
		$input->set('view','manage');		
		
		parent::display($cachable,$urlparams);
    }

}