<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
class PbbookingViewHoliday extends JViewLegacy
{
        /**
         * HelloWorlds view display method
         * @return void
         */
        function display($tpl = null) 
        {
            

            $input = JFactory::getApplication()->input;
            $task = $input->get('task');

            //load the items we need
            JToolBarHelper::title( JText::_( 'COM_PBBOOKING_HOLIDAYS' ), 'generic.png' );
            JToolbarHelper::custom('cpanel.display','dashboard','','COM_PBBOOKING_DASHBOARD',false);
            JToolbarHelper::save('holiday.save');
            JToolbarHelper::cancel('holiday.cancel', 'JTOOLBAR_CLOSE');

            $this->form = $this->get('Form');
            $this->item = $this->get('Item');

            switch ($task) {
                case 'add':
                case 'edit':
                    break;
            }


            // Display the template
            parent::display($tpl);
        }
}