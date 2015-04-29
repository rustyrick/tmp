<?php
/**
* @package		PurpleBeanie.PBBooking
* @license		GNU General Public License version 2 or later; see LICENSE.txt
* @link		http://www.purplebeanie.com
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
 
jimport( 'joomla.application.component.view' );
 

class PbbookingViewReports extends Purplebeanie\View\PbAdminView
{
   
    function display($tpl = null)
    {
        \JToolBarHelper::title( JText::_( 'COM_PBBOOKING' ).' '.JText::_('COM_PBBOOKING_DASHBOARD_REPORTS'), 'generic.png' );
 
        JToolbarHelper::custom('cpanel.display','dashboard','','COM_PBBOOKING_DASHBOARD',false);

        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_pbbooking'.DS.'models');
        $model = JModelLegacy::getInstance('Reports','PbbookingModel');

        
        $this->items = $model->getItems(); 

        parent::display($tpl);
    }
}