<?php
/**
 * @package    PurpleBeanie.PBBooking
 * @link http://www.purplebeanie.com
 * @license    GNU/GPL
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');



class PbbookingControllerReminders extends JControllerForm
{
	
    function display($cachable = false, $urlparams = false) 
    {
        
        $input = JFactory::getApplication()->input;
        $input->set('view', $input->getCmd('view', 'Reminders'));

        
        parent::display($cachable);
    }

    /**
     * have to override the save function to set the redir to the cpanel
     */

    public function save($key = null, $urlVar = null)
    {
        $app = JFactory::getApplication();
        $input = JFactory::getApplication()->input;
        $data  = $this->input->post->get('jform', array(), 'array');

        $model = $this->getModel('Reminders','PbbookingModel');
        if ($model->save($data))
        {
            $app->enqueueMessage(JText::_('COM_PBBOOKING_CONFIG_SUCCESSUL_UPDATE'));
            $this->setRedirect(JRoute::_('index.php?option=com_pbbooking'));
        } else
        {
            $app->enqueueMessage(JText::_('COM_PBBOOKING_CONFIG_FAILED_UPDATE'));
            $this->setRedirect(JRoute::_('index.php?option=com_pbbooking'));            
        }
    }

    public function cancel($key=null)
    {
        $this->setRedirect(JRoute::_('index.php?option=com_pbbooking'));
    }


}