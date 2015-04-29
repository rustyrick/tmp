<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

jimport('joomla.crypt.crypt');


class PbbookingModelTestimonial extends JModelAdmin

{

    public function getForm($data= array(),$loadData = true)
    {
        $form = $this->loadForm('com_pbbooking.testimonial','testimonial',array('control' => 'jform', 'load_data' => $loadData));
        return $form;
    }


    public function getTable($type='Testimonial',$prefix='PbbookingTable',$config= array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    protected function loadFormData() 
    {

        $data = JFactory::getApplication()->getUserState('com_pbbooking.edit.testimonial.data',array());
        if (empty($data)) 
        {
                $data = $this->getItem();
        }
        return $data;
    }

    /**
     * overrides the getItem for the testimonial to load the relevant other data as well
     */

    public function getItem($pk=null)
    {
        $state = $this->getState();
        $pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');


        // Create a new query object.           
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        // Select some fields from the hello table
        $query
            ->select('#__pbbooking_surveys.*,#__pbbooking_cals.name as cal_name,#__pbbooking_treatments.name as service_name,#__pbbooking_events.dtstart')
            ->from('#__pbbooking_surveys')
            ->join('left','#__pbbooking_events on #__pbbooking_events.id = #__pbbooking_surveys.event_id')
            ->join('left','#__pbbooking_cals on #__pbbooking_cals.id = #__pbbooking_events.cal_id')
            ->join('left','#__pbbooking_treatments on #__pbbooking_treatments.id = #__pbbooking_events.service_id')
            ->where('#__pbbooking_surveys.id = '.(int)$pk)
            ->order('#__pbbooking_surveys.id DESC');

        $item = $db->setQuery($query)->loadObject();


        return $item;

    }

    /**
     * overrides the populate state method
     */

    protected function populateState()
    {
        $input = JFactory::getApplication()->input;

        $cids = $input->get('cid',null,'array');
        if (!$cids)
            throw new Exception("No valid CIDS found", 1);
            
        $this->setState('testimonial.id',$cids[0]);
    }


}