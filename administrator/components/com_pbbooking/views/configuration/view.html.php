<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
class PbbookingViewConfiguration extends JViewLegacy
{
        /**
         * @return void
         */
        function display($tpl = null) 
        {
            

            $input = JFactory::getApplication()->input;
            $task = $input->get('task');
            $viewdata = $input->get('viewdata',null,'array');

            //load the items we need
            JToolBarHelper::title( JText::_( 'COM_PBBOOKING_CONFIG_DETAILS' ), 'generic.png' );
            JToolbarHelper::custom('cpanel.display','dashboard','','COM_PBBOOKING_DASHBOARD',false);
            JToolbarHelper::custom('configuration.sysinfo','wrench','wrench','COM_PBBOOKING_SYSTEM_INFORMATION',false);
            JToolbarHelper::save('configuration.save');
            JToolbarHelper::cancel('cpanel.display', 'JTOOLBAR_CLOSE');
            JToolbarHelper::preferences('com_pbbooking');

            $this->form = $this->get('Form');
            $this->item = $this->get('Item');

            // Display the template
            parent::display($tpl);
        }
}