<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

?>

<h2><?php echo JText::_('COM_PBBOOKING_ADMIN_CONFIRM_DELETE');?></h2>
<?php echo JText::_('COM_PBBOOKING_ADMIN_CONFIRM_DELETE_NOTE');?>

<form class="form form-horizontal" action="?option=com_pbbooking&task=event.delete" method="POST">
    <div class="control-group">
        <label class="control-label"><?php echo JText::_('COM_PBBOOKING_CONFIG_EMAIL_SUBJECT');?></label>
        <div class="controls">
            <input type="text" name="admin_pending_cancel_subject" class="input-xlarge" value="<?php echo $this->admin_pending_cancel_subject;?>" aria-required="true"/>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label"><?php echo JText::_('COM_PBBOOKING_CONFIG_EMAIL_BODY');?></label>
        <div class="controls">
            <textarea name="admin_pending_cancel_body" class="input-xlarge" rows="10" aria-required="true"><?php echo $this->admin_pending_cancel_body;?></textarea>
        </div>
    </div>

    <div class="control-group">
        <button class="btn btn-danger"><?php echo JText::_('COM_PBBOOKING_DELETE_EVENT');?></button>
    </div>

    <input type="hidden" name="id" value="<?php echo $this->item->id;?>"/>
    <input type="hidden" name="task" value="event.delete"/>
    <input type="hidden" name="cid[]" value="<?php echo $this->item->id;?>"/>
    <?php echo Jhtml::_('form.token');?>

</form>