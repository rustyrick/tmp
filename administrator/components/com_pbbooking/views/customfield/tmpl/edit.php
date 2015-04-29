<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 


?> 





<form id="adminForm" name="adminForm" method="POST" action="?option=com_pbbooking&layout=edit&id=<?php echo $this->item->id;?>" class="form form-horizontal">
    <div class="row-fluid">
        <div class="span3">
            <?php echo JText::_('COM_PBBOOKING_CUSTOMFIELDS_HINT_TEXT');?>
        </div>
        <div class="span9">
            <fieldset>
                <legend><?php echo JText::_('COM_PBBOOKIONG_CUSTOMFIELDS');?></legend>
                <?php foreach ($this->form->getGroup('') as $field) :?>
                    <?php echo $field->getControlGroup();?>
                <?php endforeach;?>
            </fieldset>


        </div>
    </div>

    <input type="hidden" name="task" value="customfield.edit"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
