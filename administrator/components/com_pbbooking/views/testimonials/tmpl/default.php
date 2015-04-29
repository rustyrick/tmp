<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('stylesheet','com_pbbooking/font-awesome/font-awesome.min.css',array(),true);

?> 

<div class="row-fluid">

    <div class="span12">

    <h2><?php echo JText::_('COM_PBBOOKING_SUB_MENU_TESTIMONIALS');?></h2>

        <form action="<?php echo JRoute::_('index.php?option=com_pbbooking'); ?>" method="post" name="adminForm" id="adminForm">
            <table class="table table-striped">
                <tr>
                    <th width="20"><?php echo JHtml::_('grid.checkall');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_TESTIMONIAL_DATE');?>
                    <th><?php echo JText::_('COM_PBBOOKING_TESTIMONIAL_SERVICE_DATE');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_TESTIMONIAL_SERVICE_CALENDAR');?></th>
                    <th></th>
                </tr>
                <tbody>
                    <?php foreach($this->items as $i => $item): ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td>
                                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td><?php echo JHtml::_('date',date_create($item->date_submitted,new DateTimeZone(PBBOOKING_TIMEZONE))->format(DATE_ATOM),JText::_('COM_PBBOOKING_TESTIMONIAL_DATE_FORMAT'));?></td>
                            <td><?php echo JHtml::_('date',date_create($item->dtstart,new DateTimeZone(PBBOOKING_TIMEZONE))->format(DATE_ATOM),JText::_('COM_PBBOOKING_TESTIMONIAL_DATE_FORMAT'));?></td>
                            <td><?php echo htmlspecialchars($item->cal_name);?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <input type="hidden" name="task"/>
            <input type="hidden" name="option" value="com_pbbooking"/>
            <input type="hidden" name="boxchecked" value="0"/>
            <?php echo JHtml::_('form.token');?>
        </form>

    </div>
</div>