<?php
/**
 * @package    PurpleBeanie.PBBooking
 * @link http://www.purplebeanie.com
 * @license    GNU/GPL
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');



class PbbookingControllerEvent extends JControllerAdmin
{
	
    function display($cachable = false, $urlparams = false) 
    {
        
        $input = JFactory::getApplication()->input;
        $input->set('view', $input->getCmd('view', 'Event'));

        parent::display($cachable);
    }

    /**
     * overrides / adds the delete function.  needs to be overridden becaause an email needs to be sent for the pending and we don't want a redirect.
     */

    public function delete()
    {
        $input = JFactory::getApplication()->input;

        $event_id = $input->get('id',null,'integer');
        if (!$event_id) 
        {
            $this->setRedirect(JRoute::_('index.php?option=com_pbbooking'));
            return;
        }

        $model = $this->getModel('Event','PbbookingModel');
        $event = $model->getItem($event_id);

        $admin_pending_cancel_body = $input->get('admin_pending_cancel_body',null,'string');
        $admin_pending_cancel_subject = $input->get('admin_pending_cancel_subject',null,'string');

        if ($admin_pending_cancel_subject && $admin_pending_cancel_body) {
            $config = JFactory::getConfig();
            $mailer = JFactory::getMailer();
            $mailer->setSender(array($config->get('mailfrom'),$config->get('fromname')));
            $mailer->addRecipient($event->email);
            $mailer->setSubject($admin_pending_cancel_subject);
            $mailer->setBody($admin_pending_cancel_body);
            $mailer->Send();
        }

        //now just delete the event.
        $cids = array($event_id);
        $model->delete($cids);

        $this->setRedirect(JRoute::_('index.php?option=com_pbbooking'));
        return;
    }

}