<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
class PbbookingViewSurveyconfig extends JViewLegacy
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
            JToolBarHelper::title( JText::_( 'COM_PBBOOKING_SUB_MENU_TESTIMONIAL_CONFIG' ), 'generic.png' );
            JToolbarHelper::custom('cpanel.display','dashboard','','COM_PBBOOKING_DASHBOARD',false);
            JToolbarHelper::save('surveyconfig.save');
            JToolbarHelper::cancel('surveyconfig.cancel', 'JTOOLBAR_CLOSE');

            $this->form = $this->get('Form');
            $this->item = $this->get('Item');



            // Display the template
            parent::display($tpl);
        }
}