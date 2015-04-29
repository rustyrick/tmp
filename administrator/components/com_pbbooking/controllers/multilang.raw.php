<?php
/**
 * @package    PurpleBeanie.PBBooking
 * @link http://www.purplebeanie.com
 * @license    GNU/GPL
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');



class PbbookingControllerMultilang extends JControllerForm
{

    public function getprimaryvalues()
    {
        $input = JFactory::getApplication()->input;
        $db = JFactory::getDbo();
        $type = $input->get('type',null,'string');
        $model = $this->getModel('Multilang','PbbookingModel');

        switch ($type)
        {
            case 'customfield':
                $response = $db->setQuery('select id,fieldname as primaryvalue,varname from #__pbbooking_customfields')->loadObjectList();
                break;
            case 'calendar':
                $response = $db->setQuery('select id,name as primaryvalue, name as varname from #__pbbooking_cals')->loadObjectList();
                break;
            case 'service':
                $response = $db->setQuery('select id,name as primaryvalue, name as varname from #__pbbooking_treatments')->loadObjectList();
                break;
            case 'message':
                $response = $model->getMessages();
                break;
            case 'subject':
                $response = $model->getSubjects();
                break;
        }

        if ($response)
            echo json_encode(array('status'=>'success','data'=>$response));
        
        die();
    }


}