<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

// import Joomla view library
jimport('joomla.application.component.view');

class PbbookingViewTestimonials extends JViewLegacy
{


    public function display($tpl = null)
    {
        JToolBarHelper::title( JText::_( 'COM_PBBOOKING_SUB_MENU_TESTIMONIALS' ), 'generic.png' );
        JToolbarHelper::custom('','dashboard','','COM_PBBOOKING_DASHBOARD',false);
        JToolbarHelper::custom('testimonial.display','search','','COM_PBBOOKING_SURVEY_VIEW',true);

            $this->items = $this->get('Items');
            $this->pagination = $this->get('Pagination');

        parent::display($tpl);
    }
}