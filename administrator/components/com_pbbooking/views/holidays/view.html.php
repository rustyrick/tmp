<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

// import Joomla view library
jimport('joomla.application.component.view');

class PbbookingViewHolidays extends JViewLegacy
{


    public function display($tpl = null)
    {
        JToolBarHelper::title( JText::_( 'COM_PBBOOKING_HOLIDAYS' ), 'generic.png' );
        JToolbarHelper::custom('','dashboard','','COM_PBBOOKING_DASHBOARD',false);
        JToolbarHelper::editList('holiday.edit');
        JToolbarHelper::addNew('holiday.add');
        JToolbarHelper::deleteList('','holidays.delete', 'JTOOLBAR_DELETE');

            $this->items = $this->get('Items');
            $this->pagination = $this->get('Pagination');

        parent::display($tpl);
    }
}