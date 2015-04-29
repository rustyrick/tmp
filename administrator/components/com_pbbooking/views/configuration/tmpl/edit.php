<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('jquery.framework');


?>

<script>
    jQuery(document).ready(function(){
        jQuery('#linkCalendarsButton').on('click',function(){
            jQuery(this).addClass('disabled').css({'display':'none'});
        });
    });
</script>


<form id="adminForm" name="adminForm" method="POST" action="?option=com_pbbooking&layout=edit" class="form form-horizontal">
    <div class="row-fluid">
        <div class="span3">
            <?php echo JText::_('COM_PBBOOKING_CONFIG_DETAILS_INSTRUCTIONS');?>
        </div>
        <div class="span9">
            <div class="tabbable">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#messages" data-toggle="tab"><?php echo JText::_('COM_PBBOOKING_CONFIG_MESSAGES_SETUP');?></a></li>
                    <li><a href="#validation" data-toggle="tab"><?php echo JText::_('COM_PBBOOKING_CONFIG_VALIDATION_SETTINGS');?></a></li>
                    <li><a href="#view" data-toggle="tab"><?php echo JText::_('COM_PBBOOKING_CONFIG_VIEW_SETTINGS');?></a></li>
                    <li><a href="#integrations" data-toggle="tab"><?php echo JText::_('COM_PBBOOKING_INTEGRATION_SETTINGS');?></a></li>
                    <li><a href="#googlecalendar" data-toggle="tab"><?php echo JText::_('COM_PBBOOKING_CALENDAR_GOOGLE_SETTINGS');?></a></li>
                    <li><a href="#debug" data-toggle="tab"><?php echo JText::_('COM_PBBOOKING_CONFIG_DEBUG_SETTINGS');?></a></li>
                    <li><a href="#payment" data-toggle="tab"><?php echo JText::_('COM_PBBOOKING_CONFIG_PAYMENT_SETTINGS');?></a></li>
                    <li><a href="#advanced" data-toggle="tab"><?php echo JText::_('COM_PBBOOKING_CONFIG_ADVANCED_SETTINGS');?></a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="messages">
                        <fieldset>
                            <legend><?php echo JText::_('COM_PBBOOKING_CONFIG_MESSAGES_SETUP');?></legend>
                            <?php foreach ($this->form->getFieldset('messages') as $field) :?>
                                <?php echo $field->getControlGroup();?>
                            <?php endforeach;?>
                        </fieldset>
                    </div>
                    <div class="tab-pane" id="validation">
                        <fieldset>
                            <legend><?php echo JText::_('COM_PBBOOKING_CONFIG_VALIDATION_SETTINGS');?></legend>
                            <?php foreach ($this->form->getFieldset('validation') as $field) :?>
                                <?php echo $field->getControlGroup();?>
                            <?php endforeach;?>
                        </fieldset>
                    </div>
                    <div class="tab-pane" id="view">
                        <fieldset>
                            <legend><?php echo JText::_('COM_PBBOOKING_CONFIG_VIEW_SETTINGS');?></legend>
                            <?php foreach ($this->form->getFieldset('view') as $field) :?>
                                <?php echo $field->getControlGroup();?>
                            <?php endforeach;?>
                        </fieldset>
                    </div>
                    <div class="tab-pane" id="integrations">
                        <fieldset>
                            <legend><?php echo JText::_('COM_PBBOOKING_INTEGRATION_SETTINGS');?></legend>
                            <?php foreach ($this->form->getFieldset('integrations') as $field) :?>
                                <?php echo $field->getControlGroup();?>
                            <?php endforeach;?>
                        </fieldset>
                    </div>
                    <div class="tab-pane" id="googlecalendar">
                        <fieldset>
                            <legend><?php echo JText::_('COM_PBBOOKING_CALENDAR_GOOGLE_SETTINGS');?></legend>
                            <?php echo JText::_('COM_PBBOOKING_CONFIG_SYNC_FUTURE_EVENTS_NOTE');?>
                            <div class="alert alert-success">
                                <strong><?php echo JText::_('COM_PBBOOKING_YOUR_SYNC_URL');?>:</strong>
                                <?php echo JURI::root(false);?>index.php?option=com_pbbooking&controller=cron&task=sync&format=raw&view=cron&google_cal_sync_secret=<?php echo $this->item->google_cal_sync_secret;?>
                                <?php echo JText::_('COM_PBBOOKING_CRON_SETUP');?>
                            </div>
                            <?php foreach ($this->form->getFieldset('googlecalendar') as $field) :?>
                                <?php echo $field->getControlGroup();?>
                            <?php endforeach;?>
                            <?php if (!isset($this->item->authcode) || $this->item->authcode == '') :?>
                                <div class="control-group">
                                    <div class="controls">
                                        <a id="linkCalendarsButton" href="<?php echo $this->item->authUrl;?>" class="btn btn-success" target="_blank"><?php echo JText::_('COM_PBBOOKING_GOOGLE_LINK_CALENDAR');?></a>
                                    </div>
                                </div>
                            <?php endif;?>
                        </fieldset>
                    </div>
                    <div class="tab-pane" id="debug">
                        <fieldset>
                            <legend><?php echo JText::_('COM_PBBOOKING_CONFIG_DEBUG_SETTINGS');?></legend>
                            <?php foreach ($this->form->getFieldset('debug') as $field) :?>
                                <?php echo $field->getControlGroup();?>
                            <?php endforeach;?>
                        </fieldset>
                    </div>
                    <div class="tab-pane" id="payment">
                        <fieldset>
                            <legend><?php echo JText::_('COM_PBBOOKING_CONFIG_PAYMENT_SETTINGS');?></legend>
                            <?php foreach ($this->form->getFieldset('payment') as $field) :?>
                                <?php echo $field->getControlGroup();?>
                            <?php endforeach;?>
                        </fieldset>
                    </div>
                    <div class="tab-pane" id="advanced">
                        <fieldset>
                            <legend><?php echo JText::_('COM_PBBOOKING_CONFIG_ADVANCED_SETTINGS');?></legend>
                            <?php foreach ($this->form->getFieldset('advanced') as $field) :?>
                                <?php echo $field->getControlGroup();?>
                            <?php endforeach;?>
                        </fieldset>
                    </div>


                </div> <!-- end tab content -->

            </div> <!-- end tabbable -->


        </div>
    </div>
    <input type="hidden" name="oldauthcode" value=" <?php echo ( isset($this->item->authcode) ) ? $this->item->authcode : null;?> "/>
    <input type="hidden" name="task" value="configuration.edit"/>
    <?php foreach ($this->form->getFieldset('hidden') as $field) :?>
        <?php echo $field->getControlGroup();?>
    <?php endforeach;?>
    <?php echo JHtml::_('form.token'); ?>
</form>
