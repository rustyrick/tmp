<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

?> 


<h3>Sysinfo</h3>

<form name="adminForm" id="adminForm" action="?option=com_pbbooking">
    <table class="table table-striped">
        <tr>
            <th width="25%"><?php echo JText::_('COM_PBBOOKING_SYSTEM_INFORMATION_PBBOOKING_VERSION');?></th>
            <td><?php echo $this->configdata['pbbversion'];?></td>
        </tr>
        <tr>
            <th width="25%"><?php echo JText::_('COM_PBBOOKING_SYSTEM_INFORMATION_JOOMLA_VERSION');?></th>
            <td><?php echo $this->configdata['jversion'];?></td>
        </tr>
        <tr>
            <th width="25%"><?php echo JText::_('COM_PBBOOKING_SYSTEM_INFORMATION_TEMPLATE_OVERIDES');?></th>
            <td><?php echo implode(',',$this->configdata['files']);?></td>
        </tr>
        <tr>
            <th width="25%"><?php echo JText::_('COM_PBBOOKING_SYSTEM_INFORMATION_BASE_PATH');?></th>
            <td><?php echo JPATH_SITE;?></td>
        </tr>
    </table>
    <input type="hidden" name="option" value="com_pbbooking"/>
    <input type="hidden" name="task"/>
</form>