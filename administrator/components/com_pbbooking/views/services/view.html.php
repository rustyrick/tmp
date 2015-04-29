<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

// import Joomla view library
jimport('joomla.application.component.view');

class PbbookingViewServices extends JViewLegacy
{


    public function display($tpl = null)
    {
        JToolBarHelper::title( JText::_( 'COM_PBBOOKING_SUB_MENU_SERVICES' ), 'generic.png' );
        JToolbarHelper::custom('','dashboard','','COM_PBBOOKING_DASHBOARD',false);
        JToolbarHelper::editList('service.edit');
        JToolbarHelper::addNew('service.add');
        JToolbarHelper::deleteList('','services.delete', 'JTOOLBAR_DELETE');

            $this->items = $this->get('Items');
            $this->pagination = $this->get('Pagination');

        parent::display($tpl);
    }
}