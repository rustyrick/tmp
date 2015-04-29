<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

// import Joomla view library
jimport('joomla.application.component.view');
jimport('joomla.languge.helper');

class PbbookingViewMultilangs extends JViewLegacy
{


    public function display($tpl = null)
    {
        JToolBarHelper::title( JText::_( 'COM_PBBOOKING_ENABLE_MULTILANGUAGE' ), 'generic.png' );
        JToolbarHelper::custom('','dashboard','','COM_PBBOOKING_DASHBOARD',false);
        JToolbarHelper::editList('multilang.edit');
        JToolbarHelper::addNew('multilang.add');
        JToolbarHelper::deleteList('','multilangs.delete', 'JTOOLBAR_DELETE');

        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        $this->languages = JLanguageHelper::createLanguageList(null);

        parent::display($tpl);
    }
}