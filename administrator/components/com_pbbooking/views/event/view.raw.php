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

            switch ($task) {
                case 'confirmdelete':
                    $this->admin_pending_cancel_subject = $input->get('admin_pending_cancel_subject',null,'string');
                    $this->admin_pending_cancel_body = $input->get('admin_pending_cancel_body',null,'string');
                    break;
            }
            $this->item = $this->get('Item');

            // Display the template
            parent::display($tpl);
        }
}