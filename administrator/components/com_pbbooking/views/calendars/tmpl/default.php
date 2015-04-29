<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('stylesheet','com_pbbooking/font-awesome/font-awesome.min.css',array(),true);

?> 

<div class="row-fluid">

    <div class="span3">
        <h3><?php echo JText::_('COM_PBBOOKING_RESOURCES_HINT');?></h3>
        <?php echo JText::_('COM_PBBOOKING_RESOURCES_HINT_TEXT');?>
    </div>

    <div class="span9">

    <h2><?php echo JText::_('COM_PBBOOKING_RESOURCES_DISPLAY');?></h2>

        <form action="<?php echo JRoute::_('index.php?option=com_pbbooking'); ?>" method="post" name="adminForm" id="adminForm">
            <table class="table table-striped">
                <tr>
                    <th width="20"><?php echo JHtml::_('grid.checkall');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_CAL_NAME');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_CAL_OVERRIDE_EMAIL');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_CAL_IS_GCAL_BACKEND');?></th>
                </tr>
                <tbody>
                    <?php foreach($this->items as $i => $item): ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td>
                                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td>
                                <a href="?option=com_pbbooking&view=calendar&layout=edit&id=<?php echo $item->id;?>"><?php echo $item->name;?></a>
                            </td>
                            <td align="center"><?php if (isset($item->email) && $item->email !='') :?>
                                    <span class="label label-success"><?php echo $item->email;?></span>
                                <?php else:?>
                                    <span class="label label-info"><?php echo JText::_('COM_PBBOOKING_UNLINKED');?>
                                <?php endif;?>
                            </td>
                            <td align="center">
                                <?php if (isset($item->enable_google_cal) && $item->enable_google_cal == 1 && isset($item->gcal_id)) :?>
                                    <span class="label label-success"><?php echo $item->gcal_id;?></span>
                                <?php else:?>
                                    <span class="label label-info"><?php echo JText::_('COM_PBBOOKING_UNLINKED');?></span>
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