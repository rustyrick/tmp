<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

// import Joomla view library
jimport('joomla.application.component.view');

class PbbookingViewTestimonial extends JViewLegacy
{


    public function display($tpl = null)
    {
        JToolBarHelper::title( JText::_( 'COM_PBBOOKING_SUB_MENU_TESTIMONIALS' ), 'generic.png' );
        JToolbarHelper::custom('cpanel.display','dashboard','','COM_PBBOOKING_DASHBOARD',false);
        JToolbarHelper::back();


        $this->item = $this->get('Item');


        $this->pagination = $this->get('Pagination');

        parent::display($tpl);
    }
}