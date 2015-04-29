<?php 

/**
* @package		PurpleBeanie.PBBooking
* @license		GNU General Public License version 2 or later; see LICENSE.txt
* @link		http://www.purplebeanie.com
*/


?>

<script>var event = <?php echo json_encode($this->event);?>;</script>


<?php include(JPATH_BASE.DS.'components'.DS.'com_pbbooking'.DS.'assets'.DS.'pbsupportfunctions.php');?>





<form class="adminForm form-horizontal pbbooking-edit-event" action="index.php" method="post" name="adminForm" id="adminForm">


<div class="row-fluid">



	<div class="span12">

			
			<div class="row-fluid">
				<div class="span6">
					<?php foreach ($this->customfields as $field): ?>
						<div class="control-group">
							<label class="control-label span3"><?php echo $field->fieldname;?></label>
							<div class="controls">
								<?php if ($field->fieldtype == 'text') :?>
									<input type="text" name="<?php echo $field->varname;?>" value="<?php echo htmlspecialchars($this->customfields_data[$field->varname]);?>" class="span9">
								<?php endif;?>
								<?php if ($field->fieldtype == 'radio') :?>
									<?php foreach (explode('|',$field->values) as $value) :?>
										<label class="<?php echo $field->varname;?>-label"><?php echo $value;?></label> 
										<input type="radio" name="<?php echo $field->varname;?>" value="<?php echo $value;?>" <?php if($value == $this->customfields_data[$field->varname]) echo 'checked="checked"';?> />
									<?php endforeach;?>
								<?php endif;?>
								<?php if ($field->fieldtype == 'select'): ?>
									<select name="<?php echo $field->varname;?>">
										<?php foreach (explode('|',$field->values) as $value) :?>
											<option value="<?php echo $value;?>" <?php if($value == $this->customfields_data[$field->varname]) echo 'selected="true"';?> ><?php echo $value;?></option>
										<?php endforeach;?>
									</select>
								<?php endif;?>
								<?php if ($field->fieldtype == 'checkbox'):?>
									<?php foreach (explode('|',$field->values) as $value) :?>
										<label class="<?php echo $field->varname;?>-label"><?php echo $value;?></label>
										<input type="checkbox" name="<?php echo $field->varname;?>[]" value="<?php echo $value;?>" <?php if($value == $this->customfields_data[$field->varname]) echo 'checked="checked"';?> />
									<?php endforeach;?>
								<?php endif;?>
								<?php if ($field->fieldtype=='textarea') :?>
									<textarea name="<?php echo htmlspecialchars($field->varname);?>" class="span9"></textarea>
								<?php endif;?>
							</div>
						</div>
					<?php endforeach;?>		
				</div>
				<div class="span6">
					<div class="control-group">
						<label class="control-label span3"><?php echo JText::_('COM_PBBOOKING_BOOKING_DATE');?></label>
							<?php //echo JHTML::_('calendar',date_create($this->event->dtstart,new DateTimeZone(PBBOOKING_TIMEZONE))->format('Y-m-d'),'date','date');?>
						<div class="controls"><input type="text" name="date" id="date" value="<?php echo date_create($this->event->dtstart,new DateTimeZone(PBBOOKING_TIMEZONE))->format('Y-m-d');?>" class="span9"/></div>
					</div>
					<div class="control-group">
						<label class="control-label span3"><?php echo JText::_('COM_PBBOOKING_EVENT_DTSTART');?></label>
						<div class="controls"><input type="text" name="dtstart" value="<?php echo date_create($this->event->dtstart, new DateTimeZone(PBBOOKING_TIMEZONE))->format('H:i:s');?>" class="span9"/></div>
					</div>
					<div class="control-group">
						<label class="control-label span3"><?php echo JText::_('COM_PBBOOKING_EVENT_DTEND');?></label>
						<div class="controls"><input type="text" name="dtend" value="<?php echo date_create($this->event->dtend, new DateTimeZone(PBBOOKING_TIMEZONE))->format('H:i:s');?>" class="span9"/></div>
					</div>
					<div class="control-group">
						<label class="control-label span3"><?php echo JText::_('COM_PBBOOKING_SERVICE_LABEL');?></label>
						<div class="controls">
							<select name="treatment_id">
								<option value="0"><?php echo Jtext::_('COM_PBBOOKING_SERVICE_DROPDOWN');?></option>
								<?php foreach ($this->services as $service) :?>
									<option value="<?php echo $service->id;?>" <?php echo ($service->id == $this->event->service_id) ? 'selected' : null;?> >
										<?php echo $service->name;?>
									</option>
								<?php endforeach;?>
							</select>
						</div>
					</div>

					<div class="control-group">
						<label class="control-label span3"><?php echo JText::_('COM_PBBOOKING_CAL_LABEL');?></label>
						<div class="controls">
							<select name="cal_id" class="span9">
								<option value="0"><?php echo Jtext::_('COM_PBBOOKING_CAL_DROPDOWN');?></option>
								<?php foreach ($this->cals as $cal) :?>
									<option value="<?php echo $cal->id;?>"
									<?php if ($this->event->cal_id == $cal->id) :?>
										selected
									<?php endif;?>
									><?php echo $cal->name;?></option>
								<?php endforeach;?>
							</select>
						</div>
					</div>
				</div>
			</div>

			<div class="row-fluid">
				<div class="span6">
					<h3><?php echo Jtext::_('COM_PBBOOKING_EVENT_RECUR');?></h3>
					<div class="span12">
						<?php if (isset($this->event->r_int)) :?>
							<p><?php echo JText::_('COM_PBBOOKING_EVENT_RECUR_MSG_TOP');?></p>
							<table style="border:0px;width:100%;">
								<tr>
									<td><?php echo JText::_('COM_PBBOOKING_EVENT_RECUR_INT');?></td>
									<td><?php echo $this->event->r_int;?></td>
								</tr>
								<tr>
									<td><?php echo JText::_('COM_PBBOOKING_EVENT_RECUR_FREQ');?></td>
									<td><?php echo $this->event->r_freq;?></td>
								</tr>
								<tr>
									<td><?php echo JText::_('COM_PBBOOKING_EVENT_RECUR_END');?></td>
									<td><?php echo $this->event->r_end;?></td>
								</tr>
							</table>
						<?php else:?>
							<p><?php echo JText::_('COM_PBBOOKING_EVENT_RECUR_NONE_TOP');?></p>
						<?php endif;?>
					</div>
				</div>
				<div class="span6">
					<h3><?php echo JText::_('COM_PBBOOKING_DELETION_SETTINGS');?></h3>
					<p><?php echo JText::_('COM_PBBOOKING_DELETE_CONFIRM_MESSAGE');?></p>
					<div class-"control-group">
						<label class="control-label"><?php echo JText::_('COM_PBBOOKING_DELETE_ALL_FUTURE_CHILDREN');?></label>
						<div class="controls">
							<input type="hidden" name="delete_children" value="0"/>
							<input type="checkbox" name="delete_children" value="1"/>
						</div>
					</div>
				</div>
			</div>

			<div class="row-fluid">
				<div class="span8"></div>
				<div class="span2"><a href="#" id="update-event" class="btn btn-primary"><?php echo JText::_('COM_PBBOOKING_SAVE_EVENT');?></a></div>
				<div class="span2"><a href="#" id="delete-event" class="btn btn-warning"><?php echo JText::_('COM_PBBOOKING_DELETE_EVENT');?></a></div>
			</div>

			<input type="hidden" name="id" value="<?php echo $this->event->id;?>"/>
			<input type="hidden" name="option" value="com_pbbooking"/>
			<input type="hidden" name="format" value="raw"/>
	</div>
</div>

</form>


<script type="text/javascript">
	times_array = <?php echo json_encode($this->shift_times);?>;
	time_increment = <?php echo $this->config->time_increment;?>;
	services = <?php echo json_encode($this->services);?>;
	time_prompt = "<?php echo JText::_('COM_PBBOOKING_SELECT_TIME');?>";


	jQuery('input[name="date"]').datepicker({
			dateFormat: 'yy-mm-dd',
			dayNamesMin:<?php echo json_encode(get_day_names_min());?>
		});
	jQuery('input[name="recur_end"]').datepicker({
			dateFormat: 'yy-mm-dd',
			dayNamesMin:<?php echo json_encode(get_day_names_min());?>
		});
	jQuery('input[name="dtend"]').timepicker({timeFormat:'HH:mm:s'});
	jQuery('input[name="dtstart"]').timepicker({timeFormat:'HH:mm:s',onClose:recalc_end_time});




	jQuery('select[name="treatment_id"]').on('change',recalc_end_time);
	jQuery('input[name="dtstart"]').on('blur',recalc_end_time);





	/*
	* recaclculates the end time for treatments
	*/
	function recalc_end_time()
	{
		var treatment_id = jQuery('select[name="treatment_id"]').val();
		for (var i=0;i<services.length;i++) {
			if (services[i]['id'] == treatment_id) {
				var time_elem = jQuery('input[name="dtstart"]').val().split(':');
				var dtstart = new Date();
				dtstart.setHours(parseInt(time_elem[0]));
				dtstart.setMinutes(parseInt(time_elem[1]));
				dtstart.setSeconds(0);
				var dtend = dtstart;
				dtend.setMinutes(dtstart.getMinutes()+parseInt(services[i]['duration']));
				jQuery('input[name="dtend"]').val(dtend.getHours().toString().replace(/^(\d)$/,'0$1')+':'+dtend.getMinutes().toString().replace(/^(\d)$/,'0$1')+':00');
			}
		}
	}

</script>

