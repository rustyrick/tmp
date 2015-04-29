<?php

/**
* @package		PurpleBeanie.PBBooking
* @license		GNU General Public License version 2 or later; see LICENSE.txt
* @link		http://www.purplebeanie.com
*/

$version = new JVersion();



?>

<?php $config =JFactory::getConfig();?>
<?php date_default_timezone_set($config->get('offset'));?>
<?php $now = date_create();?>

<?php if ($this->config->user_offset == 1) :?>
	<?php echo JText::_('COM_PBBOOKING_TIMES_CONVERTED');?>
	<?php
		$dtstart_user = \Pbbooking\Pbbookinghelper::pbbConvertTimezone($this->date_start,$this->user_offset); 
	?>
<?php endif;?>

<table style="width:100%;">
<?php if($this->config->user_offset == 1) :?>
	<tr><td><?php echo JText::_('COM_PBBOOKING_YOUR_TIME');?></td><td><?php echo JText::_('COM_PBBOOKING_OUR_TIME');?></td></tr>
<?php endif;?>
<?php $avail_appts = 0;?>
<?php while ($this->date_start < $this->date_end) :?>
	<?php $slot_end = clone $this->date_start;?>
	<?php $slot_end->setTimezone(new DateTimeZone($config->get('offset')));?>
	<?php $slot_end->modify('+'.$this->time_increment.' minutes');?>
	<?php $found_avail = false;?>
	<?php foreach ($this->cals as $cal):?>
		<?php if (!$found_avail && !$cal->is_free_from_to($this->date_start,$slot_end) && $cal->can_book_treatment_at_time($this->treatment->id,$this->date_start,$this->date_end) && $this->date_start >= $now) :?>
			<?php $found_avail = true;?>
			<?php $avail_appts++;?>
			<tr>
				<?php if ($this->config->user_offset==1) :?>			
					<td style="width:35%;"><?php echo JHTML::_('date',$dtstart_user->format(DATE_ATOM),Jtext::_('COM_PBBOOKING_SUCCESS_DATE_TIME_FORMAT'));?></td>
					<td style="width:35%;"><?php echo JHTML::_('date',$this->date_start->format(DATE_ATOM),JText::_('COM_PBBOOKING_SUCCESS_DATE_TIME_FORMAT'));?></td>
				<?php else:?>
					<td style="width:30%;"><?php echo JHTML::_('date',$this->date_start->format(DATE_ATOM),JText::_('COM_PBBOOKING_SUCCESS_TIME_FORMAT'));?></td>
				<?php endif;?>
				<td style="width:50%;">
					<input type="radio" name="treatment-time" class="cal_id-<?php echo $cal->cal_id;?>" value="<?php echo $this->date_start->format('Hi');?>"/>
				</td>
			</tr>	
		<?php else:?>
			<?php Purplebeanie\Util\Pbdebug::log_msg('individual_freeflow_view_calendar.php busy at datetime = '.$this->date_start->format(DATE_ATOM).' in cal id = '.$cal->cal_id,'com_pbbooking');?>
		<?php endif;?>		
	<?php endforeach;?>
	<?php $this->date_start->modify('+'.$this->time_increment.' minutes');?>
	<?php if(isset($dtstart_user)) $dtstart_user->modify('+ '.$this->time_increment.' minutes');?>
<?php endwhile;?>
</table>

<?php if ($avail_appts == 0) :?>
	<h3><?php echo JText::_('COM_PBBOOKING_NO_AVAIL_SLOTS');?></h3>
<?php endif;?>