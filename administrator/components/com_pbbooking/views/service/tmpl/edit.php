<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('jquery.framework');
Jhtml::_('script','com_pbbooking/knockoutjs/dist/knockout.js',true,true);
Jhtml::_('script','com_pbbooking/admin/calendar.create.js',true,true);
JHtml::_('stylesheet','com_pbbooking/font-awesome/font-awesome.min.css',array(),true);

$bow = date_create("last Sunday",new DateTimeZone(PBBOOKING_TIMEZONE));
$eow = date_create("this Sunday",new DateTimeZone(PBBOOKING_TIMEZONE));

?> 

<script type="text/javascript">

</script>



<form id="adminForm" name="adminForm" method="POST" action="?option=com_pbbooking&layout=edit&id=<?php echo $this->item->id;?>" class="form form-horizontal">
    <div class="row-fluid">
        <div class="span3">
            <?php echo JText::_('COM_PBBOOKING_EDIT_SERVICE_INSTRUCTIONS');?>
        </div>
        <div class="span9">
            <h2><?php echo JText::_('COM_PBBOOKING_SUB_MENU_SERVICES');?></h2>
            <?php foreach ($this->form->getGroup('') as $field) :?>
                <?php echo $field->getControlGroup();?>
            <?php endforeach;?>

            <!-- [FNFN] ADDED DIV FOR SERVICE DATETIME SETTINGS -->
 			<div id="ko-tradinghours">
            	<legend><?php echo JText::_('COM_PBBOOKING_CALENDAR_OVERRIDE_TRADING_TIMES');?></legend>
                <!-- this needs to be done manually rather than using JForm to get the layout I want -->
                <table width="100%">
	                <tr>
	                	<th></th>
	                    <th align="center"><?php echo JText::_('COM_PBBOOKING_DAY_IS_OPEN');?></th>
	                     <th align="center"><?php echo JText::_('COM_PBBOOKING_DAY_OPENING_TIME');?></th>
	                     <th align="center"><?php echo JText::_('COM_PBBOOKING_DAY_CLOSING_TIME');?></th>
	                     <th align="center"><?php echo JText::_('COM_PBBOOKING_CAL_MAX_BOOKINGS');?></th>
	                </tr>
            	    <?php while ($bow < $eow) :?>
                     <tr>
                     	<td><?php echo JHtml::_('date',$bow->format(DATE_ATOM),'l');?></td>
                        <td align="center">
                        	<select class="input-mini" name="status[<?php echo $bow->format('w');?>]" data-bind="value: hours()[<?php echo $bow->format('w');?>]['status']">
                            	<option value="open"><?php echo JText::_('JYES');?></option>
                                <option value="closed"><?php echo JText::_('JNO');?></option>
                            </select>
                        </td>
                        <td align="center">
                        	<input type="text" class="input-medium" name="open_time[<?php echo $bow->format('w');?>]" data-bind="value: hours()[<?php echo $bow->format('w');?>]['open_time']">
                        </td>
                        <td align="center">
                        	<input type="text" class="input-medium" name="close_time[<?php echo $bow->format('w');?>]" data-bind="value: hours()[<?php echo $bow->format('w');?>]['close_time']">                             
                        </td>
                        <td align="center">
                        	<input type="text" class="input-medium" name="max_bookings[<?php echo $bow->format('w');?>]" data-bind="value: hours()[<?php echo $bow->format('w');?>]['max_bookings']">                             
                        </td>   
                     </tr>
                     <?php $bow->modify('+1 day');?>
                     <?php endwhile;?>
                </table>
             </div>
             <!-- [FNFN] END ADDED DIV FOR SERVICE DATETIME SETTINGS -->
         </div>
    </div>

    <input type="hidden" name="task" value="service.edit"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
