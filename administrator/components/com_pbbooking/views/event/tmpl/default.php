<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

?> 

<form name="adminForm" id="adminForm" class="form form-horizontal">
    <div class="row-fluid">
        <div class="span3">
        </div>
        <div class="span9">
            <h1><?php echo JText::_('COM_PBBOOKING_EVENT_DISPLAY_EVENT');?></h1>


            <div class="well well-small">
                <h2 class="module-title nav-header"><?php echo JText::_('COM_PBBOOKING_CREATE_SUBHEADING');?></h2>
                <div class="clr"></div>
                <table style="width:100%;">
                    <tr>
                    </tr>
                </table>
            </div>
            




        </div>

    </div>

    <input type="hidden" name="task"/>
    <input type="hidden" name="id" value="<?php echo $this->item->id;?>"/>
</form>
