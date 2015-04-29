<?php


defined('_JEXEC') or die;
require_once(JPATH_LIBRARIES.DS.'purplebeanie'.DS.'autoload.php');


class PbbookingControllerEvent extends JControllerLegacy
{
   

    public function confirmdelete()
    {
        $input = JFactory::getApplication()->input;
        $input->set('view','Event');
        $input->set('layout','confirmdelete');

        //load the default cancellation email
        $input->set('admin_pending_cancel_subject',$GLOBALS['com_pbbooking_data']['config']->admin_pending_cancel_subject);
        $input->set('admin_pending_cancel_body',$this->renderCancellationEmail());

        parent::display();
    }

    private function renderCancellationEmail()
    {
        $template = $GLOBALS['com_pbbooking_data']['config']->admin_pending_cancel_body;
        $model = $this->getModel('Event','PbbookingModel');
        $serviceModel = $this->getModel('Service','PbbookingModel');

        $item = $model->getItem();
        $service = $serviceModel->getItem($item->service_id);

        $twig = new \Twig_Environment(new \Twig_Loader_String());
        $rendered = $twig->render(
          $template,
          array('event'=>$item,'service'=>$service)
        );

        return $rendered;

    }
}
