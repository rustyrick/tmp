<?php
/**
 * @package    PurpleBeanie.PBBooking
 * @link http://www.purplebeanie.com
 * @license    GNU/GPL
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');
jimport('joomla.library.crypt');



class PbbookingControllerTradinghours extends JControllerLegacy
{
	

    public function save()
    {
        $app = JFactory::getApplication();
        $model = $this->getModel('Tradinghours','PbbookingModel');
        if ($model->save($_POST))
        {
            $app->enqueueMessage(JText::_('COM_PBBOOKING_TRADING_HOURS_UPDATED'));
            $this->setRedirect(JRoute::_('index.php?option=com_pbbooking'));
        } else
        {
            $app->enqueueMessage(JText::_('COM_PBBOOKING_TRADING_HOURS_UPDATE_FAILED'));
            $this->setRedirect(JRoute::_('index.php?option=com_pbbooking'));            
        }
    }

    /**
     * this method overrides the standard edit task as the trading hours are only row in the existing config.
     */

    public function edit($key = null, $urlVar = null)
    {
        $input = JFactory::getApplication()->input;
        $input->set('view',$input->getCmd('view','Tradinghours'));
        $input->set('layout','edit');

        parent::display(false);
    }

    function display($cachable = false, $urlparams = false) 
    {
        
        $input = JFactory::getApplication()->input;
        $input->set('view', $input->getCmd('view', 'Tradinghours'));

        
        parent::display($cachable);
    }


}