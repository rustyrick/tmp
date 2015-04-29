<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

// import Joomla view library
jimport('joomla.application.component.view');

class PbbookingViewCalendars extends JViewLegacy
{


    public function display($tpl = null)
    {
        JToolBarHelper::title( JText::_( 'COM_PBBOOKING_RESOURCES_DISPLAY' ), 'generic.png' );
        JToolbarHelper::custom('','dashboard','','COM_PBBOOKING_DASHBOARD',false);
        JToolbarHelper::editList('calendar.edit');
        JToolbarHelper::addNew('calendar.add');
        JToolbarHelper::deleteList('','calendars.delete', 'JTOOLBAR_DELETE');

            $this->items = $this->get('Items');
            $this->pagination = $this->get('Pagination');

        parent::display($tpl);
    }
}