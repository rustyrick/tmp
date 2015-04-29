<?php 

/**
* @package		PurpleBeanie.PBBooking
* @license		GNU General Public License version 2 or later; see LICENSE.txt
* @link		http://www.purplebeanie.com
*/


?>


<?php include(JPATH_BASE.DS.'components'.DS.'com_pbbooking'.DS.'assets'.DS.'pbsupportfunctions.php');?>




<style>
	label {display:inline;}
</style>


<form class="adminForm form-horizontal" action="index.php" method="post" name="adminForm" id="create-event-form">

<div class="row-fluid">




		<div class="span6">


			<h2><?php echo JText::_('COM_PBBOOKIONG_CUSTOMFIELDS');?></h2>
			<?php foreach ($this->customfields as $field): ?>
				<div class="control-group">
					<label class="control-label"><?php echo $field->fieldname;?></label>
					<div class="controls">
						<?php if ($field->fieldtype == 'text') :?>
							<input type="text" name="<?php echo $field->varname;?>" value="">
						<?php endif;?>
						<?php if ($field->fieldtype == 'radio') :?>
							<?php foreach (explode('|',$field->values) as $value) :?>
								<label class="<?php echo $field->varname;?>-label"><?php echo $value;?></label> <input type="radio" name="<?php echo $field->varname;?>" value="<?php echo $value;?>"/>
							<?php endforeach;?>
						<?php endif;?>
						<?php if ($field->fieldtype == 'select'): ?>
							<select name="<?php echo $field->varname;?>">
								<?php foreach (explode('|',$field->values) as $value) :?>
									<option value="<?php echo $value;?>"><?php echo $value;?></option>
								<?php endforeach;?>
							</select>
						<?php endif;?>
						<?php if ($field->fieldtype == 'checkbox'):?>
							<?php foreach (explode('|',$field->values) as $value) :?>
								<label class="<?php echo $field->varname;?>-label"><?php echo $value;?></label>
								<input type="checkbox" name="<?php echo $field->varname;?>[]" value="<?php echo $value;?>"/>
							<?php endforeach;?>
						<?php endif;?>
						<?php if ($field->fieldtype=='textarea') :?>
							<textarea name="<?php echo $field->varname;?>"></textarea>
						<?php endif;?>
					</div>
				</div>
			<?php endforeach;?>		

		</div>

		<div class="span6">
			<h2><?php echo JText::_('COM_PBBOOKING_CREATE_SUBHEADING');?></h2>
			<div class="control-group">
				<label class="control-label"><?php echo JText::_('COM_PBBOOKING_SERVICE_LABEL');?></label>
				<div class="controls">
					<select name="service_id">
						<option value="0"><?php echo Jtext::_('COM_PBBOOKING_SERVICE_DROPDOWN');?></option>
						<?php foreach ($this->services as $service) :?>
							<option value="<?php echo $service->id;?>"><?php echo $service->name;?></option>
						<?php endforeach;?>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo JText::_('COM_PBBOOKING_EVENT_DTSTART');?></label>
				<div class="controls">
					<input type="text" name="dtstart" value="<?php echo JHTML::_('date',$this->date->format(DATE_ATOM),JText::_('COM_PBBOOKING_CREATE_TIME_FORMAT'));?>" class="date"/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo JText::_('COM_PBBOOKING_EVENT_DTEND');?></label>
				<div class="controls">
					<input type="text" name="dtend" value="" class="date"/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo JText::_('COM_PBBOOKING_CAL_LABEL');?></label>
				<div class="controls">
					<select name="cal_id">
						<option value="0"><?php echo Jtext::_('COM_PBBOOKING_CAL_DROPDOWN');?></option>
						<?php foreach ($this->cals as $cal) :?>
							<option value="<?php echo $cal->id;?>"><?php echo $cal->name;?></option>
						<?php endforeach;?>
					</select>
				</div>	
			</div>


		</div>

	</div>
	<div class="row-fluid">
		<div class="span12">
			<h2><?php echo Jtext::_('COM_PBBOOKING_EVENT_RECUR');?></h2>
			<div class="control-group">
				<label class="control-label"><?php echo JText::_('COM_PBBOOKING_EVENT_MAKE_RECUR');?></label>
				<div class="controls"><input type="checkbox" name="reccur" value="1"/></div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo JText::_('COM_PBBOOKING_EVENT_RECUR_INT');?></label>
				<div class="controls">
					<input type="text" name="interval" value="1"/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo JText::_('COM_PBBOOKING_EVENT_RECUR_FREQ');?></label>
				<div class="controls">
					<select name="frequency">
						<?php foreach (array('days','weeks','months','years') as $freq) :?>
							<option value="<?php echo $freq;?>"><?php echo JText::_('COM_PBBOOKING_EVENT_RECUR_'.strtoupper($freq));?></option>
						<?php endforeach;?>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo JText::_('COM_PBBOOKING_EVENT_RECUR_END');?></label>
				<div class="controls">
					<input type="text" name="recur_end" value=""/>
				</div>
			</div>
		</div>
	</div>


		<input type="hidden" name="option" value="com_pbbooking"/>
		<input type="hidden" name="task" value="manage.ajax_create"/>
		<input type="hidden" name="date" value="<?php echo $this->date->format('Y-m-d');?>"/>



	<div class="row-fluid">
		<div class="span8">
		</div>
		<div class="span4">
			<input type="button" class="btn btn-primary" value="<?php echo JText::_('COM_PBBOOKING_SAVE_EVENT');?>" id="buttonSaveEvent"/>
		</div>
	</div>

</div>

	</form>

<script type="text/javascript">

	times_array = <?php echo json_encode($this->shift_times);?>;
	time_increment = <?php echo $this->config->time_increment;?>;
	time_prompt = "<?php echo JText::_('COM_PBBOOKING_SELECT_TIME');?>";
	services_array = <?php echo json_encode($this->services);?>;
	customfields = <?php echo json_encode($GLOBALS['com_pbbooking_data']['customfields']);?>;



	jQuery('input[name="dtstart"]').datetimepicker({timeFormat:'HH:mm:ss', dateFormat: 'yy-mm-dd',onClose:recalc_end_time});

	jQuery('select[name="service_id"]').on('change',recalc_end_time);

	jQuery('input[name="recur_end"]').datepicker({dateFormat: 'yy-mm-dd'});



	function recalc_end_time(){
		if (jQuery('select[name="service_id"]').val() == 0)
			return false;
		jQuery(services_array).each(function(idx,elem){
			if (elem.id == jQuery('select[name="service_id"]').val()) {
				//console.log($('input[name="dtstart"]').val());
				var re = /(\d+)-(\d+)-(\d+) (\d+):(\d+)/;
				var date_array = jQuery('input[name="dtstart"]').val().match(re);
				var year = date_array[1];
				var month = date_array[2].replace(/^0(\d+)/,'$1')-1;
				var day = date_array[3].replace(/^0(\d+)/,'$1');
				var hour = date_array[4].replace(/^0(\d+)/,'$1');
				var minute = date_array[5].replace(/^0(\d+)/,'$1');
				dtend = new Date(year,month,day,hour,minute,0);
				dtend.setMinutes(dtend.getMinutes()+parseInt(elem.duration));
			}
		});
		jQuery('input[name="dtend"]').val(dtend.getFullYear()+'-'+(dtend.getMonth()+1).toString().replace(/^(\d)$/,'0$1')+'-'+dtend.getDate().toString().replace(/^(\d)$/,'0$1')+' '+dtend.getHours().toString().replace(/^(\d)$/,'0$1')+':'+dtend.getMinutes().toString().replace(/^(\d)$/,'0$1')+':00');
	}
</script>