<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('jquery.framework');



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

        </div>
    </div>

    <input type="hidden" name="task" value="service.edit"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
