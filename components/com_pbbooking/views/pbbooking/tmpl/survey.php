<?php

/**
* @package		PurpleBeanie.PBBooking
* @license		GNU General Public License version 2 or la<ter; see LICENSE.txt
* @link		http://www.purplebeanie.com
*/

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

$doc = JFactory::getDocument();
Jhtml::_('script','com_pbbooking/bowser/bowser.min.js',true,true);
JHtml::_('script','com_pbbooking/com_pbbooking.general.js',true,true);
Jhtml::_('stylesheet','com_pbbooking/user_view.css',false,true);

	


?>

<h1><?php echo JText::_('COM_PBBOOKING_SURVEY_HEADING');?></h1>

<p><?php echo JTexT::_('COM_PBBOOKING_SURVEY_INTRODUCTION');?></p>

<form method="POST" action="<?php echo JRoute::_('index.php?option=com_pbbooking&task=survey');?>" class="form form-horizontal">
	<?php foreach ($this->form->getGroup('') as $field) :?>
		<?php echo $field->getControlGroup();?>
	<?php endforeach;?>
	<div class="control-group">
		<div class="controls">
			<input type="submit" value="<?php echo JText::_('COM_PBBOOKING_SURVEY_SUBMIT');?>"/>
		</div>
	</div>
	<input type="hidden" name="id" value="<?php echo $this->event->id;?>"/>
	<input type="hidden" name="email" value="<?php echo $this->event->email;?>"/>
	
</form>