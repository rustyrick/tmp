<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 


class PbbookingModelHoliday extends JModelAdmin

{
    public function __construct($config= array())
    {
        parent::__construct($config);
    }

    /**
     * the save method needs to be extended to change the linked calendars back
     */

    public function save($data)
    {
        $calendars = $data['calendars'];

        $data['calendars'] = implode(',',$calendars);

        if (parent::save($data))
            return true;
        else
            return false;
    }

    /**
     * the get form method needs to be overridden for holidauy to load the linked calendars
     */

    public function getForm($data= array(),$loadData = true)
    {
        //$form = $this->loadForm('com_pbbooking.service','service',array('control' => 'jform', 'load_data' => $loadData));
        //return $form;

        $f = file_get_contents(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'forms'.DS.'holiday.xml');
        $form = new JForm('jform',array('control'=>'jform'));               //the control jform is needed to ensure compatibilty with the CRUD classes
        $formXml = new SimpleXMLElement($f);

        //modify the XML to add the calendars
        $calnode = $formXml->xpath("//field[@name='calendars']");
        $calnode = $calnode[0];
        foreach ($GLOBALS['com_pbbooking_data']['calendars'] as $cal) {
            $child = $calnode->addChild('option',$cal->name);
            $child->addAttribute('value',$cal->cal_id);
        }

        //load the XML into the form
        $form->load($formXml);

        //bind the data if needed
        $holiday = $this->getItem();
        $holiday->calendars = explode(',',$holiday->calendars);
        $form->bind($holiday);

        return $form;
    }



    public function getTable($type='Holiday',$prefix='PbbookingsTable',$config= array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    protected function loadFormData() 
    {

        $data = JFactory::getApplication()->getUserState('com_pbbooking.edit.calendar.data',array());
        if (empty($data)) 
        {
                $data = $this->getItem();
        }
        return $data;
    }


}