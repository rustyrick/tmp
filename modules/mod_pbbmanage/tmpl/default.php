<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 
$doc = JFactory::getDocument();


//load scripts.

JHtml::_('jquery.framework');
JHtml::_('bootstrap.framework');
JHtml::_('bootstrap.loadCss');
JHtml::_('script','com_pbbooking/jqueryui/ui/minified/jquery-ui.min.js',true,true);
JHtml::_('stylesheet','com_pbbooking/jqueryui/themes/base/minified/jquery-ui.min.css',array(),true);
JHtml::_('stylesheet','com_pbbooking/fullcalendar/fullcalendar.css',array(),true);
Jhtml::_('script','com_pbbooking/moment/min/moment.min.js',true,true);
JHtml::_('script','com_pbbooking/fullcalendar/dist/fullcalendar.js',true,true);
JHtml::_('script','com_pbbooking/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js',true,true);
JHtml::_('stylesheet','com_pbbooking/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.css',array(),true);
JHtml::_('script','com_pbbooking/jquery-dateFormat/jquery-dateFormat.min.js',true,true);
JHtml::_('script','mod_pbbmanage/mod_pbbmanage.js',true,true);

//push the language strings into the dom
JText::script('COM_PBBOOKING_CREATE_ERROR_CHOOSE_CALENDAR');
JText::script('COM_PBBOOKING_DELETE_CONFIRM_TITLE');
JText::script('COM_PBBOOKING_DELETE_CONFIRM_MESSAGE');
JText::script('COM_PBBOOKING_DELETE_JUST_THIS_ONE');
JText::script('COM_PBBOOKING_DELETE_ALL_FUTURE_CHILDREN');
JText::script('COM_PBBOOKING_EXTERNAL_EVENT_WARNING');



?>

<script>
<!-- // push the date into the js....
    curr_date = '<?php echo date_create("now", new DateTimeZone(PBBOOKING_TIMEZONE))->format("Y-m-d");?>';
    var base_url = "<?php echo JURI::root(false);?>";
//-->
</script>





<div class="bootstrap">
    <div class="container-fluid">  

        <div class="row-fluid">
            <div class="span12"><h4><?php echo JText::_('COM_PBBOOKING_CALENDAR_LEGEND');?></h4></div>
            <?php foreach ($GLOBALS['com_pbbooking_data']['calendars'] as $cal):?>
                <div class="span2">
                    <div class="pull-left">
                        <div style="min-width:20px;min-height:20px;background-color:<?php echo $cal->color;?>;margin-right:20px;"/></div>
                    </div>
                    <div class="pull-left">
                        <?php echo $cal->name;?>
                    </div>
                </div>
            <?php endforeach;?>
        </div>
        <div class="row-fluid">
            <div class="span12">
                <div id='calendar'></div>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12"><h4><?php echo JText::_('COM_PBBOOKING_CALENDAR_LEGEND');?></h4></div>
            <?php foreach ($GLOBALS['com_pbbooking_data']['calendars'] as $cal):?>
                <div class="span2">
                    <div class="pull-left">
                        <div style="min-width:20px;min-height:20px;background-color:<?php echo $cal->color;?>;margin-right:20px;"/></div>
                    </div>
                    <div class="pull-left">
                        <?php echo $cal->name;?>
                    </div>
                </div>
            <?php endforeach;?>
        </div>

    </div>
</div>


<div id="modal"></div>





