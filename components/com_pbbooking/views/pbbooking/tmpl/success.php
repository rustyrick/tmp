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

<h2><?php echo ($this->config->validation == 'admin') ? Jtext::_('COM_PBBOOKING_SUCCESS_PENDING_ADMIN_VALIDATION') : JTEXT::_('COM_PBBOOKING_SUCCESS_HEADING');?></h2>
<p><?php echo ($this->config->validation == 'admin') ? JText::_('COM_PBBOOKING_SUCCESS_PENDNG_ADMIN_VALIDATION_MSG') : JTEXT::_('COM_PBBOOKING_SUCCESS_MSG');?></p>

<p><em><?php echo JTEXT::_('COM_PBBOOKING_SUCCESS_SUB_HEADING');?></em></p>

<table>
	<tr>
		<th>
			<?php echo JTEXT::_('COM_PBBOOKING_SUCCESS_DATE');?>
		</th>
		<td>
			<?php if ($this->config->user_offset == 1) :?>
				<strong><?php echo JText::_('COM_PBBOOKING_YOUR_DATE');?></strong> <?php echo Jhtml::_('date',$dtstart_user->format(DATE_ATOM),JText::_('COM_PBBOOKING_SUCCESS_DATE_FORMAT'));?>
				<strong><?php echo JText::_('COM_PBBOOKING_OUR_DATE');?></strong> <?php echo Jhtml::_('date',date_create($this->event->dtstart->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE))->format(DATE_ATOM),JText::_('COM_PBBOOKING_SUCCESS_DATE_FORMAT'));?>
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
		<?php if (isset($this->calendar)) {?>
		<tr>
			<th><?php echo JTEXT::_('COM_PBBOOKING_SUCCESS_CALENDAR');?></th>
			<td><?php echo $this->calendar->name;?></td>
		</tr>
	<?php
	}?>
</table>

<p><?php echo ($this->config->validation != 'admin') ? JTEXT::_('COM_PBBOOKING_SUCCESS_VALIDATION_MESSAGE') : JText::_('COM_PBBOOKING_SUCCESS_PENDING_ADMIN_VALIDATION_NOTICE');?></p>

<?php if ($this->config->enable_logging == 1) :?>
	<input type="text" name="eventid" value="<?php echo $this->event->id;?>"/>
<?php endif;?>