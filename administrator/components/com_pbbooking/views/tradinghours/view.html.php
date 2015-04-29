<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
class PbbookingViewTradinghours extends JViewLegacy
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
            JToolBarHelper::title( JText::_( 'COM_PBBOOKING_OFFICE_TRADING_HOURS' ), 'generic.png' );
            JToolbarHelper::custom('cpanel.display','dashboard','','COM_PBBOOKING_DASHBOARD',false);
            JToolbarHelper::save('tradinghours.save');
            JToolbarHelper::cancel('cpanel.display', 'JTOOLBAR_CLOSE');

            //pull in my own model since this is an unusual case.
            $model = JModelLegacy::getInstance('Tradinghours','PbbookingModel');
            $this->setModel($model,true);

            $this->item = $this->get('Item');


            // Display the template
            parent::display($tpl);
        }
}