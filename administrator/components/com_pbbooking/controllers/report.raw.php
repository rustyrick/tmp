<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 


jimport('joomla.application.component.controlleradmin');

class PbbookingControllerReport extends JControllerAdmin
{

    public function download()
    {
        $input = JFactory::getApplication()->input;

        $id = $input->get('id',null,'integer');
        if (!$id || !in_array($id,array(1,2,3)))
            throw new \Exception("Invalid report ID", 1);

        //load the report model
        $model = $this->getModel('Report','PbbookingModel');

        $view = $this->getView('Report','raw');
        $view->setLayout('default');
        $view->setModel($model,true);
        $view->rid = $id;
        $view->display();
    }

}