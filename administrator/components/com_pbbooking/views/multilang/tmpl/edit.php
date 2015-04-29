<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('jquery.framework');
Jhtml::_('script','com_pbbooking/knockoutjs/dist/knockout.js',true,true);
Jhtml::_('script','com_pbbooking/admin/multilang.create.js',true,true);
JText::_('script','JGLOBAL_SELECT_AN_OPTION');



?> 




<form id="adminForm" name="adminForm" method="POST" action="?option=com_pbbooking&layout=edit&id=<?php echo $this->item->id;?>" class="form form-horizontal">
    <div class="row-fluid">
        <div class="span3">
            <?php echo JText::_('COM_PBBOOKING_MULTILANG_HINT_TEXT');?>
        </div>
        <div class="span9">
            <fieldset>
                <legend><?php echo JText::_('COM_PBBOOKING_MULTILANG_OVERRIDE_DETAILS');?></legend>
                <?php foreach ($this->form->getGroup('') as $field) :?>
                    <?php echo $field->getControlGroup();?>
                <?php endforeach;?>
            </fieldset>

        </div>
    </div>

    <input type="hidden" name="task" value="multilang.edit"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
