<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('jquery.framework');
Jhtml::_('script','com_pbbooking/knockoutjs/dist/knockout.js',true,true);
Jhtml::_('script','com_pbbooking/admin/tradinghours.edit.js',true,true);


$bow = date_create("last Sunday",new DateTimeZone(PBBOOKING_TIMEZONE));
$eow = date_create("this Sunday",new DateTimeZone(PBBOOKING_TIMEZONE));

$doc = JFactory::getDocument();
$doc->addScriptDeclaration('var shift_times = '.$this->item->time_groupings.';');

?> 

<script type="text/javascript">

</script>



<form id="adminForm" name="adminForm" method="POST" action="?option=com_pbbooking&layout=edit" class="form form-horizontal">
    <div class="row-fluid">
        <div class="span3">
            <?php echo JText::_('COM_PBBOOKING_TRADING_HOURS_INSTRUCTIONS');?>
        </div>
        <div class="span9">

            <fieldset>
                <legend><?php echo JText::_('COM_PBBOOKING_OFFICE_TRADING_HOURS');?></legend>
                <table width="100%">
                    <tr>
                        <th></th>
                        <th align="center"><?php echo JText::_('COM_PBBOOKING_DAY_IS_OPEN');?></th>
                        <th align="center"><?php echo JText::_('COM_PBBOOKING_DAY_OPENING_TIME');?></th>
                        <th align="center"><?php echo JText::_('COM_PBBOOKING_DAY_CLOSING_TIME');?></th>
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
                 
                        </tr>
                        <?php $bow->modify('+1 day');?>
                    <?php endwhile;?>
                </table>
            </fieldset>

            <fieldset>
                <legend><?php echo JText::_('COM_PBBOOKING_SHIFT_TIMES');?></legend>
                <p><?php echo JText::_('COM_PBBOOKING_SHIFT_DESCRIPTION');?></p>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th width="20px"></th>
                            <th><?php echo JText::_('COM_PBBOOKING_FIELDS_VARNAME');?></th>
                            <th><?php echo JText::_('COM_PBBOOKING_SHIFT_DISPLAY_LABEL');?></th>
                            <th><?php echo JText::_('COM_PBBOOKING_SHIFT_START');?></th>
                            <th><?php echo JText::_('COM_PBBOOKING_SHIFT_END');?></th>
                        </tr>
                    </thead>
                    <tbody data-bind="foreach: time_groupings">
                        <tr>
                            <td><input type="checkbox" data-bind="checked: checked"/></td>
                            <td><input type="text" class="input-medium" aria-required="true" data-bind="value: varname"/></td>
                            <td><input type="text" class="input-medium" aria-required="true" data-bind="value: display_label"/></td>
                            <td><input type="text" class="input-medium" aria-required="true" data-bind="value: shift_start"/></td>
                            <td><input type="text" class="input-medium" aria-required="true" data-bind="value: shift_end"/></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4"></td>
                            <td>
                                <button class="btn btn-success" data-bind="click:addShift"><?php echo JText::_('COM_PBBOOKING_ADD_SHIFT');?></button>
                                <button class="btn btn-danger" data-bind="click:removeShift"><?php echo JText::_('COM_PBBOOKING_DELETE_SHIFT');?></button>
                            </td>
                    </tfoot>
                </table>
            </fieldset>

        </div>
    </div>
    <input type="hidden" name="tradinghours" value='<?php echo $this->item->trading_hours;?>'/>
    <input type="hidden" name="time_groupings" value=""/>
    <input type="hidden" name="task" value="tradinghours.edit"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
