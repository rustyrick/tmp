<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('stylesheet','com_pbbooking/font-awesome/font-awesome.min.css',array(),true);
$saveOrderingUrl = 'index.php?option=com_pbbooking&task=customfields.saveOrderAjax&tmpl=component';
JHtml::_('sortablelist.sortable', 'customfieldList', 'adminForm', 'ASC', $saveOrderingUrl);

?> 

<div class="row-fluid">

    <div class="span3">
        <h3><?php echo JText::_('COM_PBBOOKING_RESOURCES_HINT');?></h3>
        <?php echo JText::_('COM_PBBOOKING_CUSTOMFIELDS_HINT_TEXT');?>
    </div>

    <div class="span9">

    <h2><?php echo JText::_('COM_PBBOOKIONG_CUSTOMFIELDS');?></h2>

        <form action="<?php echo JRoute::_('index.php?option=com_pbbooking'); ?>" method="post" name="adminForm" id="adminForm">
            <table class="table table-striped" id="customfieldList">
                <tr>
                    <th width="20"><?php echo JHtml::_('grid.checkall');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_FIELDS_NAME');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_FIELDS_VARNAME');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_IS_FIRST_NAME');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_IS_LAST_NAME');?></th>
                    <th><?php echo JText::_('COM_PBBOOKING_IS_EMAIL');?></th>
                </tr>
                <tbody>
                    <?php foreach($this->items as $i => $item): ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td>
                                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                                <input type="text" style="display:none" name="order[]" size="5"
                                    value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
                            </td>
                            <td>
                                <?php echo htmlspecialchars($item->fieldname);?>
                            </td>
                            <td><?php echo htmlspecialchars($item->varname);?>

                            <td align="center"><?php if (isset($item->is_first_name) && $item->is_first_name ==1) :?>
                                    <span class="label label-success"><?php echo JText::_('JYES');?></span>
                                <?php else:?>
                                    <span class="label label-info"><?php echo JText::_('JNO');?></span>
                                <?php endif;?>
                            </td>
                            <td align="center"><?php if (isset($item->is_last_name) && $item->is_last_name ==1) :?>
                                    <span class="label label-success"><?php echo JText::_('JYES');?></span>
                                <?php else:?>
                                    <span class="label label-info"><?php echo JText::_('JNO');?></span>
                                <?php endif;?>
                            </td>
                            <td align="center"><?php if (isset($item->is_email) && $item->is_email ==1) :?>
                                    <span class="label label-success"><?php echo JText::_('JYES');?></span>
                                <?php else:?>
                                    <span class="label label-info"><?php echo JText::_('JNO');?></span>
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