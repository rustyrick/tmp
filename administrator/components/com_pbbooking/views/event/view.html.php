<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
class PbbookingViewEvent extends JViewLegacy
{
        /**
         * HelloWorlds view display method
         * @return void
         */
        function display($tpl = null) 
        {
            

            $input = JFactory::getApplication()->input;
            $task = $input->get('task');
            $this->item = $this->get('Item');


            $this->form = $this->get('Form');
            
   
            //load the items we need
            JToolBarHelper::title( JText::_( 'COM_PBBOOKING_RESOURCES_DISPLAY' ), 'generic.png' );
            JToolbarHelper::custom('cpanel.display','dashboard','','COM_PBBOOKING_DASHBOARD',false);
            JToolbarHelper::custom('event.edit','edit','','JTOOLBAR_EDIT',false);
            JToolbarHelper::cancel('event.cancel', 'JTOOLBAR_CLOSE');


            // Display the template
            parent::display($tpl);
        }
}