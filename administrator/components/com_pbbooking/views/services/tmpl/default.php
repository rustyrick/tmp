<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('stylesheet','com_pbbooking/font-awesome/font-awesome.min.css',array(),true);

?> 

<div class="row-fluid">

    <div class="span3">
        <h3><?php echo JText::_('COM_PBBOOKING_SERVICES_HINT');?></h3>
        <?php echo JText::_('COM_PBBOOKING_SERVICES_HINT_TEXT');?>
    </div>

    <div class="span9">

    <h2><?php echo JText::_('COM_PBBOOKING_SUB_MENU_SERVICES');?></h2>

        <form action="<?php echo JRoute::_('index.php?option=com_pbbooking'); ?>" method="post" name="adminForm" id="adminForm">
            <table class="table table-striped">
                <tr>
                    <th width="20"><?php echo JHtml::_('grid.checkall');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_SERVICE_NAME');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_SERVICE_DURATION');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_SERVICE_PRICE');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_SERVICE_LINKED_CALENDAR');?></th>
                </tr>
                <tbody>
                    <?php foreach($this->items as $i => $item): ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td>
                                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                            </td>
    
                            <td align="center"><?php echo htmlspecialchars($item->name);?></td>
                            <td align="center"><?php echo htmlspecialchars($item->duration);?></td>
                            <td align="center"><?php echo htmlspecialchars($item->price);?></td>
                            <td>
                                <?php if (count($item->cals)>0) :?>
                                    <?php foreach ($item->cals as $cal) :?>
                                        <?php echo (isset($cal->name)) ? '<span class="label label-success">'.$cal->name.'</span>' : null;?>
                                    <?php endforeach;?>
                                <?php else:?>   
                                    <span class="label label-warning"><?php echo JText::_('COM_PBBOOKING_UNLINKED');?></span>
                                <?php endif;?>
                            </td>
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