<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 



class PbbookingModelMultilang extends JModelAdmin

{

    public $messageMap;
    public $subjectMap;

    public function __construct($config= array())
    {
        $this->messageMap = array(
                '',                                 //sets array indexing to 1 in keeping with the others
                'email_body',
                'auto_validated_appt_body',
                'admin_validation_pending_email_body',
                'admin_validation_confirmed_email_body',
                'admin_paypal_confirm',
                'client_paypal_confirm'

            );
        $this->subjectMap = array(
                '',                                 //sets array indexing to 1 in keeping with the others
                'email_subject',
                'auto_validated_appt_email_subject',
                'admin_validation_pending_email_subject',
                'admin_validation_confirmed_email_subject',
                'admin_paypal_confirm_subject',
                'client_paypal_confirm_subject'
            );
        parent::__construct($config);
    }

    /**
     * modify the getForm to return the form, populated with data but also with the language codes
     */

    public function getForm($data= array(),$loadData = true)
    {
        $languages = JLanguageHelper::createLanguageList(null);
        $item = $this->getItem();

        $formXml = new \SimpleXmlElement(file_get_contents(JPATH_COMPONENT.DS.'models'.DS.'forms'.DS.'multilang.xml'));
        $langnode = $formXml->xpath("//field[@name='langtag']");
        $langnode = $langnode[0];
        foreach ($languages as $lang) {
            $child = $langnode->addChild('option',$lang['text']);
            $child->addAttribute('value',$lang['value']);
        }

        //check if the $item->id is set.  If it is I need to load the jform_primarylangvalue and set (contains id and varname)
        if (isset($item->id) && $item->id > 0) 
        {
            switch ($item->type)
            {
                case 'customfield':
                    $primarylangvalues = $this->_db->setQuery('select id,varname from #__pbbooking_customfields')->loadObjectList();
                    break;
                case 'calendar':
                    $primarylangvalues = $this->_db->setQuery('select id,name as varname from #__pbbooking_cals')->loadObjectList();
                    break;
                case 'service':
                    $primarylangvalues = $this->_db->setQuery('select id,name as varname from #__pbbooking_treatments')->loadObjectList();
                case 'message':
                    $primarylangvalues = array();
                    foreach ($this->messageMap as $k=>$v) {
                        if (isset($v) && $v != '')
                            $primarylangvalues[] = new JObject(array('id'=>$k,'varname'=>$v));
                    }
                    break;
                case 'subject':
                    $primarylangvalues = array();
                    foreach ($this->subjectMap as $k=>$v) {
                        if (isset($v) && $v != '')
                            $primarylangvalues[] = new JObject(array('id'=>$k,'varname'=>$v));
                    }
                    break;
            }

            $plvnode = $formXml->xpath('//field[@name="primarylangvalue"]');
            $plvnode = $plvnode[0];
            foreach ($primarylangvalues as $option)
            {
                $child = $plvnode->addChild('option',$option->varname);
                $child->addAttribute('value',$option->id);
            }
        }


        $form = new JForm('test',array('control'=>'jform'));
        $form->load($formXml);
        $form->bind($item);

        //$form = $this->loadForm('com_pbbooking.multilang','multilang',array('control' => 'jform', 'load_data' => $loadData));
        return $form;
    }



    public function getTable($type='Multilang',$prefix='PbbookingTable',$config= array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    protected function loadFormData() 
    {

        $data = JFactory::getApplication()->getUserState('com_pbbooking.edit.multilang.data',array());
        if (empty($data)) 
        {
                $data = $this->getItem();
        }
        return $data;
    }

    /**
     * get item needs to be extended to load in the fields needed for the form.
     */

    public function getItem($pk=null)
    {
        $item = parent::getItem($pk);
        $item->primarylangvalue = $item->original_id;

        #also need to set the primaryvalue
        if (isset($item->type)) {
            switch ($item->type)
            {
                case 'customfield':
                    $item->original = $this->_db->setQuery('select id,varname,fieldname as original from #__pbbooking_customfields')->loadObject();
                    break;
                case 'calendar':
                    $item->original = $this->_db->setQuery('select id,name as varname, name as original from #__pbbooking_cals')->loadObject();
                    break;
                case 'service':
                    $item->original = $this->_db->setQuery('select id,name as varname, name as original from #__pbbooking_treatments')->loadObject();
                case 'message':
                    $messagekey = $this->messageMap[$item->original_id];
                    $item->original = new JObject(array('id'=>$item->original_id,'varname'=>$messagekey,'original'=>$GLOBALS['com_pbbooking_data']['config']->$messagekey));
                    break;
                case 'subject':
                    $subjectkey = $this->subjectMap[$item->original_id];
                    $item->original = new JObject(array('id'=>$item->original_id,'varname'=>$subjectkey,'original'=>$GLOBALS['com_pbbooking_data']['config']->$subjectkey));
                    break;
            }

            $item->primaryvalue = $item->original->original;
        }

        return $item;

    }

    /**
     * retuns an array of messages with the current data following the pattern used for others:
     *                  select id,fieldname as primaryvalue,varname
     */

    public function getMessages($withDefaults=true)
    {
        $messages = array();
        foreach ($this->messageMap as $k=>$v)
        {
            if (isset($v) && $v !='') {
                $messages[] = array('id'=>$k,'fieldname'=>$v,'varname'=>$v,'primaryvalue'=>$GLOBALS['com_pbbooking_data']['config']->$v);
            }
        }
        return $messages;
    }

    /**
     * retuns an array of subjects with the current data following the pattern used for others:
     *                  select id,fieldname as primaryvalue,varname
     */

    public function getSubjects($withDefaults=true)
    {
        $subjects = array();
        foreach ($this->subjectMap as $k=>$v)
        {
            if (isset($v) && $v !='') {
                $subjects[] = array('id'=>$k,'fieldname'=>$v,'varname'=>$v,'primaryvalue'=>$GLOBALS['com_pbbooking_data']['config']->$v);
            }
        }
        return $subjects;
    }



}