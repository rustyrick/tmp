<?php

/**
* @package		PurpleBeanie.PBBooking
* @license		GNU General Public License version 2 or later; see LICENSE.txt
* @link		http://www.purplebeanie.com
*/
 
// No direct access
 
defined('_JEXEC') or die('Restricted access'); 
	
$doc = JFactory::getDocument();

if ($this->config->user_offset == 1) {
	$dtstart_user = \Pbbooking\Pbbookinghelper::pbbConvertTimezone($this->event->dtstart,$this->event->user_offset); 
}

?>

<h1><?php echo $doc->title;?></h1>

<h2><?php echo JTEXT::_('COM_PBBOOKING_SUCCESS_HEADING');?></h2>
<p><?php echo JTEXT::_('COM_PBBOOKING_VALIDATED_MESSAGE');?></p>

<p><em><?php echo JTEXT::_('COM_PBBOOKING_SUCCESS_SUB_HEADING');?></em></p>

<table>
	<tr>
		<th>
			<?php echo JTEXT::_('COM_PBBOOKING_SUCCESS_DATE');?>
		</th>
		<td>
			<?php if ($this->config->user_offset == 1) :?>
				<strong><?php echo JText::_('COM_PBBOOKING_YOUR_DATE');?></strong> <?php echo Jhtml::_('date',$dtstart_user->format(DATE_ATOM),JText::_('COM_PBBOOKING_SUCCESS_DATE_FORMAT'));?>
				<strong><?php echo JText::_('COM_PBBOOKING_OUR_DATE');?></strong> <?php echo Jhtml::_('date',$this->event->dtstart->format(DATE_ATOM),JText::_('COM_PBBOOKING_SUCCESS_DATE_FORMAT'));?>
			<?php else:?>
				<?php echo Jhtml::_('date',$this->event->dtstart->format(DATE_ATOM),JText::_('COM_PBBOOKING_SUCCESS_DATE_FORMAT'));?>
			<?php endif;?>
		</td>
	<tr>
		<th><?php echo JTEXT::_('COM_PBBOOKING_SUCCESS_TIME');?></th>
		<td>
			<?php if ($this->config->user_offset == 1) :?>
				<strong><?php echo JText::_('COM_PBBOOKING_YOUR_TIME');?></strong> <?php echo Jhtml::_('date',$dtstart_user->format(DATE_ATOM),JText::_('COM_PBBOOKING_SUCCESS_DATE_TIME_FORMAT'));?>
				<strong><?php echo JText::_('COM_PBBOOKING_OUR_TIME');?></strong> <?php echo Jhtml::_('date',$this->event->dtstart->format(DATE_ATOM),JText::_('COM_PBBOOKING_SUCCESS_DATE_TIME_FORMAT'));?>
			<?php else:?>
				<?php echo Jhtml::_('date',$this->event->dtstart->format(DATE_ATOM),JText::_('COM_PBBOOKING_SUCCESS_TIME_FORMAT'));?>
			<?php endif;?>
		</td>
	</tr>
	<tr>
		<th><?php echo JTEXT::_('COM_PBBOOKING_BOOKINGTYPE');?></th>
		<td><?php echo \Pbbooking\Pbbookinghelper::print_multilang_name($this->service,'service');?></td>
	</tr>
	<?php if (isset($this->calendar)) :?>
		<tr><th><?php echo JTEXT::_('COM_PBBOOKING_SUCCESS_CALENDAR');?></th><td><?php echo \Pbbooking\Pbbookinghelper::print_multilang_name($this->calendar,'calendar');?></td></tr>
	<?php endif;?>
</table>
