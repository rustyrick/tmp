<?php

/**
* @package		PurpleBeanie.PBBooking
* @license		GNU General Public License version 2 or la<ter; see LICENSE.txt
* @link		http://www.purplebeanie.com
*/

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

$doc = JFactory::getDocument();
Jhtml::_('stylesheet','com_pbbooking/user_view.css',false,true);


?>

<h1><?php echo JText::_('COM_PBBOOKING_APPOINTMENTS_SELFSERVICE_VIEW');?></h1>

<?php echo JText::_('COM_PBBOOKING_APPOINTMENTS_SELFSERVICE_DESC');?>


<table class="pbbooking-appointment-table">
	<tr>
		<th><?php echo JText::_('COM_PBBOOKING_APPOINTMENTS_SELFSERVICE_APPT_DATE');?></th>
		<th><?php echo JText::_('COM_PBBOOKING_APPOINTMENTS_SELFSERVICE_OCCURS_IN');?></th>
		<th><?php echo JText::_('COM_PBBOOKING_APPOINTMENTS_SELFSERVICE_APPT_SERVICE');?></th>
		<th><?php echo JText::_('COM_PBBOOKING_APPOINTMENTS_SELFSERVICE_APPT_CALENDAR');?></th>
		<th><?php echo JText::_('COM_PBBOOKING_APPOINTMENTS_SELFSERVICE_ACTIONS');?></th>
	</tr>
	<?php if (count($this->events) == 0) :?>
		<tr>
			<td colspan="5"><?php echo JText::_('COM_PBBOOKING_APPOINTMENTS_SELFSERVICE_NO_EVENTS');?></td>
		</tr>
	<?php else:?>
		<?php foreach ($this->events as $event) :?>
			<tr>
				<td><?php echo JHTML::_('date',$event->dtstart->format(DATE_ATOM),JText::_('COM_PBBOOKING_SUCCESS_DATE_FORMAT').' @ '.JText::_('COM_PBBOOKING_SUCCESS_TIME_FORMAT'));?></td>
				<td>
					<?php 
						$dt_diff = (int)$event->dtstart->format('U') - (int)date_create("now",new DateTimeZone(PBBOOKING_TIMEZONE))->format('U');
						echo sprintf('%0.0f',(($dt_diff/60)/60)).' '.JText::_('COM_PBBOOKING_APPOINTMENTS_SELFSERVICE_HOURS');
					?>
				</td>
				<td><?php echo $event->getService()->name;?></td>
				<td><?php echo $event->getCalendar()->name;?></td>
				<td>
					<?php $user = JFactory::getUser();?>
					<?php if (((($dt_diff/60)/60) <= $this->config->self_service_change_notice) || !$user->authorise('pbbooking.deleteown','com_pbbooking')) : ?>
						<?php echo JText::_('COM_PBBOOKING_APPOINTMENTS_SELFSERVICE_NO_CHANGES');?>
					<?php else: ?>
						<a href="<?php echo JRoute::_('index.php?option=com_pbbooking&view=appointments&task=delete_appt&id='.$event->id);?>"><img src="<?php echo JURI::root(false);?>components/com_pbbooking/images/date_delete.png"/></a>
					<?php endif;?>
				</td>
			</tr>
		<?php endforeach;?>
	<?php endif;?>
	
</table>



