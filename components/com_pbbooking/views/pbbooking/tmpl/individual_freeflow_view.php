<?php

	/**
	* @package		PurpleBeanie.PBBooking
	* @license		GNU General Public License version 2 or later; see LICENSE.txt
	* @link		http://www.purplebeanie.com
	*/
	
	$doc = JFactory::getDocument();
	
	$doc->addScriptDeclaration('var base_url = "'.JURI::root(false).'";');
	$doc->addScriptDeclaration('var $error_msg_timeslot = "'.JText::_('COM_ERROR_MESSAGE_TIMESLOT').'";');
	$doc->addScriptDeclaration('var $error_msg_treatment = "'.JText::_('COM_ERROR_MESSAGE_TREATMENT').'";');
	$doc->addScriptDeclaration('var $customfields = '.json_encode($this->customfields).';');
	$doc->addScriptDeclaration('var $treatments = '.json_encode($this->treatments).';');
	$doc->addScriptDeclaration('var enable_shifts ='.$this->config->enable_shifts.';');
	$doc->addScriptDeclaration('var load_paypal_form_url = "'.JRoute::_('index.php?option=com_pbbooking&task=load_paypal_form').'";');
	$doc->addScriptDeclaration('var select_calendar_individual = '.$this->config->select_calendar_individual.';');
	JText::script('COM_PBBOOKING_SERVICE_REQUIRES_PAYMENT');
	JText::script('COM_PBBOOKING_SELECT_DEFAULT');
	JText::script('COM_PBBOOKING_BOOKINGTYPE');
	Jhtml::_('script','com_pbbooking/bowser/bowser.min.js',true,true);
	JHtml::_('script','com_pbbooking/com_pbbooking.general.js',true,true);
	JHTML::_('behavior.formvalidation');

	//draw current month - get some relevant dates for drawing
	$this->dateparamArr = date_parse(date_format($this->dateparam,"Y-m-d"));
	
	$bom = date_create(sprintf("%s-%s-%s 00:00",$this->dateparamArr["year"],$this->dateparamArr['month'],"1"),new DateTimeZone(PBBOOKING_TIMEZONE));
	$eom = date_create($bom->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
	$curr_day = date_create($bom->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
	$eom->modify("+1 month");
	$eom->modify("-1 day");
	$num_days = $eom->format("j")-1;
		
	$next_month = date_create($bom->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
	$next_month->modify("+1 month");
	
	$last_month = date_create($bom->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));
	$last_month->modify("-1 month");

	$close_row = false; //tracks whether i need to pad at the end.....

	$form = Pbbooking\Model\Customfields::buildFormForCustomfields();

	if ($GLOBALS['com_pbbooking_data']['config']->enable_recaptcha) {
	    JPluginHelper::importPlugin('captcha');
	    $dispatcher = JDispatcher::getInstance();
	    $dispatcher->trigger('onInit','dynamic_recaptcha_1');
	}

	$showhidden = (isset($GLOBALS['com_pbbooking_data']['config']->enable_logging) && $GLOBALS['com_pbbooking_data']['config']->enable_logging ==1 ) ? true : false;

?>
<style>
	#pbbooking tr,td,th {border:0px solid;padding:0px;}
</style>



<div class = "calendars_left">				<!-- ***************** begin left hand side ******************* -->


	<div class="calendar-window">
	
		<div id="calendar">
			<table id='pbbooking' class='main-calendar'>

				<!-- draw top of calendar -->
				<tr>
					<td class="pbbooking-cal-top-left" height="91px"></td>
					<td colspan=7 class="pbbooking-cal-top-rings" width="220px"></td>
					<td class="pbbooking-cal-top-right"></td>
				</tr>
				<!-- end draw top of calendar -->
				<tr>
					<td class="pbbooking-cal-left-header"></td>
					<th colspan=7 align="center" class="pbbooking-cal-center-header">
						<a href="<?php echo JRoute::_('index.php?option=com_pbbooking&task=view&dateparam='.$last_month->format("Ymd"));?>"><<</a>
						<span class="month-heading">
							<?php echo Jhtml::_('date',$this->dateparam->format(DATE_ATOM),JText::_('COM_PBBOOKING_DATE_HEADING_FORMAT'));?>
						</span>
						<a href="<?php echo JRoute::_('index.php?option=com_pbbooking&task=view&dateparam='.$next_month->format("Ymd"));?>">>></a>
					<td class="pbbooking-cal-right-header"></td>
					</th>
			
			
				</tr>
				
				<tr class="pbbooking-header-row">
				
				<!-- begin header row-->
				<td class="pbbooking-cal-left-header"></td>
				<?php $bow = date_create('last Sunday',new DateTimeZone(PBBOOKING_TIMEZONE));?>
				
				<?php for ($i=0;$i<$this->config->calendar_start_day;$i++) :?>
					<?php $bow->modify('+1 day');?>
				<?php endfor;?>
				
				<?php $eow = clone $bow;?>
				<?php $eow->modify('+6 days');?>
				
				<?php for ($i=0;$i<=6;$i++) :?>
					
					<th class="pbbooking-cal-center-header"><?php echo Jhtml::_('date',$bow->format(DATE_ATOM),"D");?></th>
					<?php $bow->modify("+1 day");?>
			
				<?php endfor;?>
				<td class="pbbooking-cal-right-header"></td>
				<!-- end header row-->
			
				<?php
					$curr_day = $bom;
					$curr_day->setTime(23,59,59);
				?>
			
				</tr>

				<!-- begin gap row -->
				<tr height="11px">
					<td class="pbbooking-gap-row-left gap-row"></td>
					<td colspan=7 class="pbbooking-gap-row-center gap-row"></td>
					<td class="pbbooking-gap-row-right gap-row"></td>
				</tr>
			
				<tr>
				<td class="pbbooking-cal-left-body"></td>
				<!-- calc cal padding -->
				<?php if ($curr_day->format('w') < $this->config->calendar_start_day) :?>
					<?php for ($i=0;$i< 7 - ($this->config->calendar_start_day-$curr_day->format('w'));$i++) :?>
						<td class="pbbooking-content"></td>
					<?php endfor;?>
				<?php endif;?>
				
				<?php if ($curr_day->format('w') > $this->config->calendar_start_day) :?>
					<?php for ($i=0;$i<($curr_day->format('w') - $this->config->calendar_start_day);$i++) :?>
						<td class="pbbooking-content"></td>
					<?php endfor;?>
				<?php endif;?>
				
				<!-- end cal padding -->
			
				<?php for ($i=0;$i<=$num_days;$i++) :?>
					
			
					<?php if ( ($curr_day > date_create() ) && ( $this->config->allow_booking_max_days_in_advance == 0 || ( $this->config->allow_booking_max_days_in_advance > 0 && $curr_day < $this->latest ) ) ) :?>
						<td
							<?php $class = "";?>
							<?php if ($curr_day->format("z") == $this->dateparam->format("z")) :?>
								<?php $class = "selected_day";?>
							<?php endif;?>
							<?php $class .= ($this->master_trading_hours[$curr_day->format('w')]['status'] == 'closed' && $this->config->single_page_block_days_master_trading_hours == 1)  ? ' fully-booked ' : '';?>
							<?php if (count($this->cals == 1) && !\Pbbooking\Pbbookinghelper::free_appointments_for_day($curr_day,$this->cals)) $class.= ' fully-booked ';?>
							<?php echo ($class !="") ? 'class = "'.$class.'"' : "class='pbbooking-content'";?>
							>
							<!-- check to see whether cell is a block day or not -->
							<?php if ($this->master_trading_hours[$curr_day->format('w')]['status'] == 'closed' && $this->config->single_page_block_days_master_trading_hours == 1) :?>
								<?php echo $curr_day->format("d");?>
							<?php else:?>
								<a href='<?php echo JRoute::_('index.php?option=com_pbbooking&task=view&dateparam='.$curr_day->format("Ymd"));?>'>
									<?php echo $curr_day->format("d");?>
								</a>
							<?php endif;?>
						</td>
					<?php else:?>
						<td class="pbbooking-content"><?php echo JHtml::_('date',$curr_day->format(DATE_ATOM),'j');?></td>
					<?php endif;?>
					
					<!-- break if needed -->
					<?php if (($curr_day->format('w') == $eow->format('w'))) :?>
						<td class="pbbooking-cal-right-body"></td>
						</tr>
						<!-- is a new row actually needed???? -->
						<?php if ($i != $num_days) :?>
							<?php $close_row = true;?>
							<tr><td class="pbbooking-cal-left-body"></td>
						<?php else :?>
							<?php $close_row = false;?>
						<?php endif;?>
					<?php endif;?>
					<!-- finish line break -->
					
					<?php $curr_day->modify("+1 day");?>
				<?php endfor;?>

				<!-- Fixes issue #67 -->
				<?php $last_drawn_day = date_create($curr_day->format(DATE_ATOM),new DateTimeZone(PBBOOKING_TIMEZONE));?>
				<?php $last_drawn_day->modify('-1 day');?>
				<?php if ($last_drawn_day->format('w') != $eow->format('w')) $close_row = true;?>

				<!-- at end of days but do we need to add some padding to the end??? -->
				<?php if ( ($last_drawn_day->format('w') != $eow->format('w')) && $close_row ):?>
					<?php while ($last_drawn_day->format('w') != $eow->format('w')) { ?>
						<td class="pbbooking-content"></td>
						<?php $last_drawn_day->modify("+1 day");?>
					<?php } ;?>
					<td class="pbbooking-cal-right-body"></td>
				<?php endif;?>
				
				</tr>

				<!-- draw bottom of table! -->
				<tr>
					<td class="pbbooking-cal-bottom-left"></td>
					<td class="pbbooking-cal-bottom-center" colspan="7"></td>
					<td class="pbbooking-cal-bottom-right"></td>
				</tr>
			</table>
		</div>
	
	</div>

</div>							<!-- ***************** end left hand side ******************-->

<div class = "calendars_right"> <!-- ***************** begin right hand side ******************* -->

	<form action="<?php echo JRoute::_('index.php?option=com_pbbooking&task=save');?>" method="POST" id = "pbbooking-reservation-form" class="form-validate">
	
		<?php
			echo '<input type="hidden" name="date" id="text-date" value='.$this->dateparam->format('Ymd').'>';
		?>
		
		<h2><?php echo JText::_('COM_PBBOOKING_YOURDETAILS');?></h2>
		
		
		<!-- begin render custom fields -->

		<?php foreach ($form->getGroup('') as $field) :?>
			<?php echo $field->getControlGroup();?>
		<?php endforeach;?>

		<!-- end render custom fields -->

		<!-- draw recaptcha if needed -->

		<?php if ($GLOBALS['com_pbbooking_data']['config']->enable_recaptcha) :?>
			<div id="dynamic_recaptcha_1"></div>
		<?php endif;?>

		<!-- end draw recaptcha -->
		
		<h2><?php echo JText::_('COM_PBBOOKING_BOOKINGDETAILS');?></h2>

		<?php if ($this->config->select_calendar_individual) :?>
			<!-- begin render calendars -->
			<div id="pbbooking-calendars">
				<h3><?php echo JText::_('COM_PBBOOKING_SELECT_CALENDAR');?></h3>
				<select name="calendars">
					<option value="0"><?php echo JText::_('COM_PBBOOKING_SELECT_DEFAULT');?></option>
					<?php foreach ($this->cals as $cal) :?>
						<option value="<?php echo $cal->cal_id;?>"><?php echo \Pbbooking\Pbbookinghelper::print_multilang_name($cal,'calendar');?></option>
					<?php endforeach;?>
				</select>
			</div>
			<!-- end render calendars-->
		<?php endif;?>
		
		<!-- begin render service types -->
		<div id="pbbooking-services">
			<?php if ($this->config->select_calendar_individual != 1) :?>
				<?php $i=0;?>
				<h3><?php echo JText::_('COM_PBBOOKING_BOOKINGTYPE');?></h3>
				<div id="service-error-msg"></div>
				<select name="massage">
					<option value="0"><?php echo JText::_('COM_PBBOOKING_SELECT_DEFAULT');?></option>
				<?php foreach($this->treatments as $treatment) : ?>
					<option value="<?php echo $treatment->id;?>">
						<?php echo \Pbbooking\Pbbookinghelper::print_multilang_name($treatment,'service');?>
						<?php if (isset($this->config->show_prices) && $this->config->show_prices == 1): ?> 
							<?php echo \Pbbooking\Pbbookinghelper::pbb_money_format($treatment->price);?>
						<?php endif;?>
					</option>
				<?php endforeach;?>
				</select>
			<?php endif;?>
		</div>
		
		<!-- end render service types -->

		<!-- begin render appt slots -->
		<div id="pbbooking-shift-select" style="display:none;">
			<h3><?php echo JText::_('COM_PBBOOKING_TIMEGROUPINGLABEL');?></h3>
			<select name="timegrouping" id="select-timegrouping">
				<option value="0">--- <?php echo JText::_('COM_PBBOOKING_MAKESELECTION');?> ---</option>
				<?php foreach ($this->shift_times as $shift=>$v) :?>
					<option value="<?php echo $shift;?>"><?php echo \Pbbooking\Pbbookinghelper::print_multilang_name(array('key'=>$shift,'value'=>$v),'shift');?></option>
				<?php endforeach;?>
			</select>
			<h3><?php echo JText::_('COM_PBBOOKING_TIMESLOT');?></h3>
		</div>
		<div id="pbbooking-timeslot-listing">
		</div>
		
		
		<div id="timeslot-error-msg"></div>
				
		<div style="text-align:center;">
			<p></p><input type="submit" value="<?php echo JText::_('COM_PBBOOKING_SUBMIT_BUTTON');?>" id="pbbooking-submit"></p>
		</div>
		
		
		
		<input type="<?php echo $showhidden ? 'text' : 'hidden';?>" name="cal_id" id="text-cal-id" value="-1"/> <!-- a field that holds the current cal_id changed when a different check box is selected....-->
		<input type="<?php echo $showhidden ? 'text' : 'hidden';?>" name="treatment_id" value="-1" id="treatment_id"/>
		<input type="<?php echo $showhidden ? 'text' : 'hidden';?>" name="user_offset" id="user_offset" value=""/>
		<input type="<?php echo $showhidden ? 'text' : 'hidden';?>" name="service_id" value=""/>
		<input type="<?php echo $showhidden ? 'text' : 'hidden';?>" name="dtstart" value="<?php echo $this->dateparam->format('Y-m-d 00:00:00');?>"/>
		<input type="<?php echo $showhidden ? 'text' : 'hidden';?>" name="dtend" value=""/> 
		<input type="<?php echo $showhidden ? 'text' : 'hidden';?>" name="date1" value="<?php echo $this->dateparam->format('Y-m-d');?>"/>
	</form>


	<div id="pbbooking-paypal-form"></div>

</div>							<!------ *************** end right hand side ***************** -->

<div style="clear:both;"></div>
