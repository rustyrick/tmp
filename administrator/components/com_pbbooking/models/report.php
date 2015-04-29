<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');


class PbbookingModelReport extends JModelAdmin
{


    public function getItem($pk=null)
    {
        $db = \JFactory::getDbo();
        $input= \JFactory::getApplication()->input;

        $reportid = (int)$this->getState('report.id');

        $query = $db->getQuery(true);
        switch ($reportid) {
            case 1:
                $query->select('*')->from('#__pbbooking_events')->where('verified = 1')->order('id DESC');
                break;
            case 2:
                $query->select('distinct(email),id')->from('#__pbbooking_events')->where('verified = 1')->order('id DESC');
                break;
            case 3:
                $uid = $input->get('uid',null,'integer');
                if (!$uid)
                {
                    throw new \Exception("No UID has been specified", 1);
                }

                $user = JFactory::getUser($uid);
                if (!$user->email)
                {
                    throw new \Exception("User has no email", 1);   
                }

                $query->select('*')->from('#__pbbooking_events')->where('email = "'.$db->escape($user->email).'"')->order('dtstart DESC');
                break;
        }

        $results = $db->setQuery($query)->loadAssocList();

        return $results;
    }

    protected function populateState()
    {

        // Get the pk of the record from the request.
        $pk = JFactory::getApplication()->input->getInt('id');
        $this->setState($this->getName() . '.id', $pk);

        // Load the parameters.
        $value = JComponentHelper::getParams($this->option);
        $this->setState('params', $value);
    }

    public function getForm($data = array(), $loadData = true) {}

}