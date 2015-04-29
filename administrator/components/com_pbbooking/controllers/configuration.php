<?php
/**
 * @package    PurpleBeanie.PBBooking
 * @link http://www.purplebeanie.com
 * @license    GNU/GPL
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');
 

class PbbookingControllerconfiguration extends JControllerForm
{
	
    function __construct()
    {
        parent::__construct();
        
        //check the user authorisation
        $input = JFactory::getApplication()->input;
        $app = JFactory::getApplication();
        $user = JFactory::getUser();

        //check user auth for manage
        if (!$user->authorise('pbbooking.editconfiguration','com_pbbooking')) {
            $app->enqueueMessage(JText::_('COM_PBBOOKING_ACCESS_EDIT_CONFIGURATION_NOT_AUTHORISED'));
            $app->redirect('index.php?option=com_pbbooking&task=cpanel.display');
            return;
        }

    }
	
    function display($cachable = false, $urlparams = false) 
    {
        
        $input = JFactory::getApplication()->input;
        $input->set('view', $input->getCmd('view', 'Configuration'));

        
        parent::display($cachable);
    }

    public function save($key = null, $urlVar = null)
    {
        $app = JFactory::getApplication();

    	$data  = $this->input->post->get('jform', array(), 'array');

    	$model = $this->getModel('Configuration','PbbookingModel');
        $this->_process_color_change($data);

    	if ($model->save($data)) {
    		$app->enqueueMessage(JText::_('COM_PBBOOKING_CONFIG_SUCCESSUL_UPDATE'));
            $this->setRedirect(JRoute::_('index.php?option=com_pbbooking'));
        }
    	else {
            $app->enqueueMessage(JText::_('COM_PBBOOKING_CONFIG_FAILED_UPDATE'));
            $this->setRedirect(JRoute::_('index.php?option=com_pbbooking'));
        }

        return;


    }

	/**
	* this processes a possible colour change to the cal slices
	* @access private
	* @since 2.4.5.11a2
	* @return none
	*/

	private function _process_color_change($data)
	{
		$db = JFactory::getDbo();
		$input = JFactory::getApplication()->input;

		$new_colour = $data['calendar_color'];
		$config = $GLOBALS['com_pbbooking_data']['config'];

		if ($config->calendar_color != $new_colour) {
			//there is a change let's make it.
			$files = array('td-bottom-left.svg','td-bottom-right.svg','td-bottom-slice.svg','td-cal-center-header.svg','td-cal-right-body.svg','td-cal-top-left.svg','td-cal-top-right.svg',
							'td-cal-top-rings.svg','td-content-fill-slice.svg','td-content-left.svg','td-gap-row-center.svg','td-gap-row-left.svg','td-gap-row-right.svg','td-header-left.svg',
							'td-header-right.svg');
			foreach ($files as $file) {
				$path = JPATH_SITE.DS.'media'.DS.'com_pbbooking'.DS.'images'.DS.'calslices'.DS.$file;
				$fcontents = JFile::read($path);
				$fcontents = str_ireplace($config->calendar_color, $new_colour, $fcontents);
				JFile::write($path,$fcontents);
			}
		}
	}

    /**
     * just displays a system information panel
     */

    public function sysinfo()
    {
        $input = JFactory::getApplication()->input;
        $view = $this->getView('Configuration','html');

        //get the pbbookign version
        $xml = JFactory::getXML(JPATH_ADMINISTRATOR .'/components/com_pbbooking/pbbooking.xml');
        $pbbversion = (string)$xml->version;

        //get the Jversion
        $jversion = new JVersion;
        $jversion = $jversion->getLongVersion();

        //check for template overrides.
        $db = JFactory::getDbo();
        $template = $db->setQuery('select * from #__template_styles where client_id = 0 and home = 1')->loadObject();
        $overridedir = JPATH_SITE.DS.'templates'.DS.$template->template.DS.'html'.DS.'com_pbbooking';
        $files = array();
        if (file_exists($overridedir))
            $files = scandir($overridedir);
        
        //prepare data
        $view->configdata = array('jversion'=>$jversion,'pbbversion'=>$pbbversion,'files'=>$files);

        $view->setLayout('sysinfo');
        $view->display();
    }

}