<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access');

jimport('cms.component.helpler');



class PbbookingModelConfiguration extends JModelAdmin

{
    public function __construct($config= array())
    {
        parent::__construct($config);
    }

    public function getForm($data= array(),$loadData = true)
    {
        //$form = $this->loadForm('com_pbbooking.configuration','configuration',array('control' => 'jform', 'load_data' => $loadData));
        $f = file_get_contents(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'forms'.DS.'configuration.xml' );
        $formXml = new SimpleXmlElement($f);

        //modify the form to include the custom fields.
        $cfnode = $formXml->xpath("//field[@name='manage_fields']");
        $cfnode = $cfnode[0];
        foreach ($GLOBALS['com_pbbooking_data']['customfields'] as $field) {
            $child = $cfnode->addChild('option',$field->fieldname);
            $child->addAttribute('value',$field->varname);
        }
        //need to add the service tag in there as well
        $child = $cfnode->addChild('option',JText::_('COM_PBBOOKING_BOOKINGTYPE'));
        $child->addAttribute('value','_service_');

        $form = new JForm('jform',array('control'=>'jform'));
        $form->load($formXml);

        //bind the form to the data
        $item = $this->getItem();
        $form->bind($item);

        return $form;
    }

    /**
     * extends the save function for BC purposes.
     */

    public function save($data)
    {
        $input = JFactory::getApplication()->input;

        $data['manage_fields'] = (isset($data['manage_fields'])) ? json_encode($data['manage_fields']) : null;
        $data['reminder_settings'] = (isset($data['reminder_days_in_advance'])) ? json_encode(array('reminder_days_in_advance'=>$data['reminder_days_in_advance'])) : $data['reminder_settings'];


        $oldauthcode = trim( $input->get('oldauthcode',null,'string' ));

        if ( isset($data['authcode']) && $data['authcode'] != $oldauthcode )
        {
            // The authcode has changed do an immediate login to get the token / refresh token and save to the database

            // Get the client details from the component params
            $clientsecret = JComponentHelper::getParams('com_pbbooking')->get('clientsecret');
            $clientid = JComponentHelper::getParams('com_pbbooking')->get('clientid');

            $client = new \Google_Client();
            $client->setClientId($clientid);
            $client->setClientSecret($clientsecret);
            $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
            $client->addScope('https://www.googleapis.com/auth/calendar');
            $client->setAccessType('offline');

            // Exchange authorization code for access token
            $accessToken = $client->authenticate($data['authcode']);
            $client->setAccessToken($accessToken);

            $data['token'] = $accessToken;
        }



        return parent::save($data);
    }



    public function getTable($type='Configuration',$prefix='PbbookingTable',$config= array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    protected function loadFormData() 
    {

        $data = JFactory::getApplication()->getUserState('com_pbbooking.edit.configuration.data',array());
        if (empty($data)) 
        {
                $data = $this->getItem();
        }
        return $data;
    }

    /**
     * extended to preprocess some data for BC
     */

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if ($item && isset($item->manage_fields) && $item->manage_fields != '')
            $item->manage_fields = json_decode($item->manage_fields,true);

        if ($item && isset($item->reminder_settings) && $item->reminder_settings != '') {
            $reminder_settings = json_decode($item->reminder_settings,true);
            $item->reminder_days_in_advance = $reminder_settings['reminder_days_in_advance'];
        }

        // Create a new link to the authcode login in case the client wants to connect to google cal
        if ($item && (!isset($item->authcode) || $item->authcode =='' ))
        {
            // Get the client details from the component params
            $clientsecret = JComponentHelper::getParams('com_pbbooking')->get('clientsecret');
            $clientid = JComponentHelper::getParams('com_pbbooking')->get('clientid');

            $client = new \Google_Client();
            $client->setClientId($clientid);
            $client->setClientSecret($clientsecret);
            $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
            $client->addScope('https://www.googleapis.com/auth/calendar');
            $client->setAccessType('offline');

            $item->authUrl = $client->createAuthUrl();
        }

        return $item;
    }



}