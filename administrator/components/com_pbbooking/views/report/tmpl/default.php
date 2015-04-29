<?php

// No direct access

defined('_JEXEC') or die('Restricted access'); 
$fp= fopen("php://output",'w');
header("Content-type: text/csv");
header("Content-Disposition: attachment;filename=report.csv");

switch ($this->rid)
{
    case 1:
        foreach ($this->get('Item') as $item)
        {
            $event = new \Pbbooking\Model\Event($item['id']);
            fputcsv($fp,array($event->getSummary(),$event->dtstart->format(DATE_ATOM),$event->getService()->name),',','"');
        }
        break;
    case 2:
        foreach ($this->get('Item') as $item)
        {
            $event = new \Pbbooking\Model\Event($item['id']);
            fputcsv($fp,array(htmlspecialchars($event->email),
                            htmlspecialchars($event->getFirstName()),
                            htmlspecialchars($event->getLastName())),',','"');
        }

        break;
    case 3:
        foreach ($this->get('Item') as $item)
        {
            $event = new \Pbbooking\Model\Event($item['id']);
            fputcsv($fp,array($event->getSummary(),$event->dtstart->format(DATE_ATOM),$event->getService()->name),',','"');
        }
        break;
}

fclose($fp);
?> 