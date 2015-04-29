<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('stylesheet','com_pbbooking/font-awesome/font-awesome.min.css',array(),true);

?> 

<div class="row-fluid">

    <div class="span3">
        <h3><?php echo JText::_('COM_PBBOOKING_RESOURCES_HINT');?></h3>
        <?php echo JText::_('COM_PBBOOKING_MULTILANG_HINT_TEXT');?>
    </div>

    <div class="span9">

    <h2><?php echo JText::_('COM_PBBOOKING_ENABLE_MULTILANGUAGE');?></h2>

        <form action="<?php echo JRoute::_('index.php?option=com_pbbooking'); ?>" method="post" name="adminForm" id="adminForm">
            <table class="table table-striped">
                <tr>
                    <th width="20"><?php echo JHtml::_('grid.checkall');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_MULTILANG_OVERRIDE_TYPE');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_MULTILANG_OVERRIDE_PRIMARY_VALUE');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_MULTILANG_OVERRIDE_DATA');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_MULTILANG_OVERRIDE_LANGUAGE_TAG');?></th>
                </tr>
                <tbody>
                    <?php foreach($this->items as $i => $item): ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td>
                                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td><?php echo $item->type;?></td>
                            <td><?php echo $item->primaryitem->originalvalue;?></td>
                            <td><?php echo htmlspecialchars($item->data);?></td>
                            <td><?php echo htmlspecialchars($item->langtag);?></td>
                         
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