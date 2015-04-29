<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('stylesheet','com_pbbooking/font-awesome/font-awesome.min.css',array(),true);

?> 
<div class="row-fluid">

    <div class="span12">

    <h2><?php echo JText::_('COM_PBBOOKING_SURVEY_VIEW');?></h2>

        <div class="span6">
            <div class="well well-small">
                <h2 class="module-title nav-header"><?php echo JText::_('COM_PBBOOKING_CREATE_SUBHEADING');?></h2>
                <div class="row-striped">
                    <div class="row-fluid">
                        <div class="span3"><strong class="row-title"><?php echo JText::_('COM_PBBOOKING_TESTIMONIAL_SERVICE_DATE');?></strong></div>
                        <div class="span9"><?php echo Jhtml::_('date',date_create($this->item->dtstart,new DateTimeZone(PBBOOKING_TIMEZONE))->format(DATE_ATOM),JText::_('COM_PBBOOKING_TESTIMONIAL_DATE_FORMAT'));?></div>
                    </div>
                    <div class="row-fluid">
                        <div class="span3"><strong class="row-title"><?php echo JText::_('COM_PBBOOKING_TESTIMONIAL_SERVICE_CALENDAR');?></strong></div>
                        <div class="span9"><?php echo htmlspecialchars($this->item->cal_name);?></div>
                    </div>
                    <div class="row-fluid">
                        <div class="span3"><strong class="row-title"><?php echo JText::_('COM_PBBOOKING_SERVICE_NAME');?></strong></div>
                        <div class="span9"><?php echo htmlspecialchars($this->item->service_name);?></div>
                    </div>
                    <div class="row-fluid">
                        <div class="span3"><strong class="row-title"><?php echo JText::_('COM_PBBOOKING_SURVEY_REMOTE_IP');?></strong></div>
                        <div class="span9"><?php echo htmlspecialchars($this->item->submission_ip);?></div>
                    </div>

                </div>
            </div>
        </div>

        <div class="row-fluid">
            <div class="span12">
                <div class="well well-small">
                    <h2 class="module-title nav-header"><?php echo JText::_('COM_PBBOOKING_TESTIMONIAL_RESULTS');?></h2>
                    <div class="row-striped">
                    <?php foreach (json_decode($this->item->content,true) as $k=>$v) :?>
                        <div class="row-fluid">
                            <div class="span3">
                                <strong class="row-title"><?php echo htmlspecialchars($k);?></strong>
                            </div>
                            <div class="span9">
                                <?php echo htmlspecialchars($v);?>
                            </div>
                        </div>
                    <?php endforeach;?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


<form id="adminForm" name="adminForm" action="?option=com_pbbooking">
    <input type="hidden" name="task"/>
    <input type="hidden" name="option" value="com_pbbooking"/>
</form>