<?php 

	/**
	* @package		PurpleBeanie.PBBooking
	* @license		GNU General Public License version 2 or later; see LICENSE.txt
	* @link		http://www.purplebeanie.com
	* @since 	2.3
	*/

?>


<div class="row-fluid">


	<div class="span12">

		<form action="index.php" method="POST" name="adminForm" id="adminForm">

			<fieldset class="adminform">
				<legend>Set Date Range</legend>
				<table>
					<tr>
						<td><label>Date From</label></td>
						<td><?php echo JHtml::_('calendar',(isset($this->date_from)) ? $this->date_from->format('Y-m-d') : date_create("now",new DateTimeZone(PBBOOKING_TIMEZONE))->format('Y-m-d'), 'date-from', 'date-from', $format = '%Y-%m-%d');?></td>
					</tr>
					<tr>
						<td><label>Date To</label></td>
						<td><?php echo JHtml::_('calendar',(isset($this->date_to)) ? $this->date_to->format('Y-m-d'): date_create("now",new DateTimeZone(PBBOOKING_TIMEZONE))->format('Y-m-d'), 'date-to', 'date-to', $format = '%Y-%m-%d');?></td>
					</tr>
				</table>
			</fieldset>

			<div style="clear:both;"></div>

			<fieldset class="adminform">
				<legend>Include Fields</legend>
				<table>
					<tr>
						<td><label>Include Fields</label></td>
						<td>
							<table>
								<?php foreach (array('cal_id','summary','dtend','dtstart','description','service_id') as $field) :?>
									<tr>
										<td><label><?php echo $field;?></label></td>
										<td><input type="checkbox" name="include_fields[]" value="<?php echo $field;?>"
											<?php echo (isset($this->include_fields) && in_array($field, $this->include_fields)) ? 'checked'  : null;?>
										/></td>
									</tr>
								<?php endforeach;?>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan=2><input type="submit" value="submit"></td>
					</tr>
				</table>
			</fieldset>

			<?php if (isset($this->events)) :?>
				<fieldset class="adminform">
						<legend>Booked Events</legend>
						<table>
								<tr>
									<?php foreach ($this->include_fields as $field):?>
										<th><?php echo $field;?></th>
									<?php endforeach;?>
								</tr>
								<?php foreach ($this->events as $event):?>
								<tr>
									<?php foreach ($this->include_fields as $field): ?>
										<td>
											<?php 
											switch ($field):
												case ('cal_id'):
													echo $event->cal_name;
													break;
												case ('service_id'):
													echo $event->service_name;
													break;
												default:
													echo $event->$field;
													break; 	
											endswitch;?>
										</td>
									<?php endforeach;?>
								</tr>
								<?php endforeach;?>
						</table>
				</fieldset>
			<?php endif;?>




		<input type="hidden" name="option" value="com_pbbooking"/>
		<input type="hidden" name="controller" value="manage"/>
		<input type="hidden" name="task" value="print_view"/>
		</form>

		<div style="clear:both;"></div>

		</div>
</div>