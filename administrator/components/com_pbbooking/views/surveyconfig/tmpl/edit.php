<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('jquery.framework');
Jhtml::_('script','com_pbbooking/knockoutjs/dist/knockout.js',true,true);
Jhtml::_('script','com_pbbooking/admin/surveyconfig.edit.js',true,true);
JHtml::_('stylesheet','com_pbbooking/font-awesome/font-awesome.min.css',array(),true);



?> 

<script type="text/javascript">

</script>



<form id="adminForm" name="adminForm" method="POST" action="?option=com_pbbooking&layout=edit&id=<?php echo $this->item->id;?>" class="form form-horizontal">
    <div class="row-fluid">
        <div class="span3">
            <?php echo JText::_('COM_PBBOOKING_SUB_MENU_TESTIMONIAL_CONFIG_TIPS');?>
        </div>
        <div class="span9">
            <fieldset>
                <legend><?php echo JText::_('COM_PBBOOKING_SUB_MENU_TESTIMONIAL_CONFIG');?></legend>
                <?php foreach ($this->form->getGroup('') as $field) :?>
                    <?php echo $field->getControlGroup();?>
                <?php endforeach;?>
            </fieldset>

            <fieldset>
                <legend><?php echo JText::_('COM_PBBOOKING_CONFIGURATION_TESTIMONIALS_QUESTIONS');?></legend>
                <table class="admin-table table-striped" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo JText::_('COM_PBBOOKING_CONFIGURATION_TESTIMONIAL_FIELD_LABEL');?></th>
                            <th><?php echo JText::_('COM_PBBOOKING_CONFIGURATION_TESTIMINIAL_FIELD_VARNAME');?></th>
                            <th><?php echo JText::_('COM_PBBOOKING_CONFIGURATION_TESTIMONIAL_FIELD_TYPE');?></th>
                            <th><?php echo JText::_('COM_PBBOOKING_CONFIGURATION_TESTIMONIAL_FIELD_VALUES');?></th>
                            <th><?php echo JText::_('COM_PBBOOKING_CONFIGURATION_TESTIMONIAL_FIELD_ACTIONS');?></th>
                        </tr>
                    </thead>
                    <tbody data-bind="foreach: questions">
                        <tr>
                            <td><input name="testimonial_field_label" data-bind="value: testimonial_field_label"/></td>
                            <td><input name="testimonial_field_varname" data-bind="value: testimonial_field_varname"/></td>
                            <td>
                                <select name="testimonial_field_type" data-bind="value: testimonial_field_type">
                                    <option value="text"><?php echo JText::_('COM_PBBOOKING_FIELD_TYPE_TEXT');?></option>
                                    <option value="radio"><?php echo JText::_('COM_PBBOOKING_FIELD_TYPE_RADIO');?></option>
                                    <option value="checkbox"><?php echo JText::_('COM_PBBOOKING_FIELD_TYPE_CHECKBOX');?></option>
                                    <option value="select"><?php echo JText::_('COM_PBBOOKING_FIELD_TYPE_SELECT');?></option>
                                    <option value="textarea"><?php echo JText::_('COM_PBBOOKING_FIELD_TYPE_TEXTAREA');?></option>
                                </select>
                            </td>
                            <td><input name="testimonial_field_values" data-bind="value: testimonial_field_values"/></td>
                            <td><a href="#" data-bind="click: $parent.removeQuestion"><div class="fa fa-trash-o" style="color:red;"></div></a></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" align="right"><button class="btn btn-primary" data-bind="click: addQuestion"><?php echo JText::_('COM_PBBOOKING_TESTIMONAIL_ADD_QUESTION');?></button></td>
                        </tr>
                    </tfoot>
                </table>
            </fieldset>

        </div>
    </div>

    <input type="hidden" name="task" value="surveyconfig.edit"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
