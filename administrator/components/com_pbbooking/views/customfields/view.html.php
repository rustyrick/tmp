<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

// import Joomla view library
jimport('joomla.application.component.view');

class PbbookingViewCustomfields extends JViewLegacy
{


    public function display($tpl = null)
    {
        JToolBarHelper::title( JText::_( 'COM_PBBOOKIONG_CUSTOMFIELDS' ), 'generic.png' );
        JToolbarHelper::custom('','dashboard','','COM_PBBOOKING_DASHBOARD',false);
        JToolbarHelper::editList('customfield.edit');
        JToolbarHelper::addNew('customfield.add');
        JToolbarHelper::deleteList('','customfields.delete', 'JTOOLBAR_DELETE');

            $this->items = $this->get('Items');
            $this->pagination = $this->get('Pagination');

        parent::display($tpl);
    }
}