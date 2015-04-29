<?php 
	
	JHtml::_('behavior.framework');             //the only reason this is still needed is to allow the Joomla.Jtext functions.
	Jhtml::_('script','com_pbbooking/jquery-cookie/jquery.cookie.js',false,true);
	JHtml::_('script','com_pbbooking/moment/min/moment.min.js', false, true);
	JHtml::_('script','com_pbbooking/jquery-dateFormat/jquery-dateFormat.min.js',false,true);
	JHtml::_('script','com_pbbooking/com_pbbooking.individual.freeflow.multipage.js',false,true);

	$doc = JFactory::getDocument();
	Jhtml::_('stylesheet','com_pbbooking/user_view_multi.css',false,true);
	$doc->addScriptDeclaration('var $treatments = '.json_encode($this->treatments).';');
	$doc->addScriptDeclaration('var load_paypal_form_url = "'.JRoute::_('index.php?option=com_pbbooking&task=load_paypal_form').'";');
	$doc->addScriptDeclaration('var $customfields = '.json_encode($this->customfields).';');
	$doc->addScriptDeclaration('var base_url = "'.JURI::root(false).'";');
	$doc->addScriptDeclaration('var $error_msg_treatment = "'.JText::_('COM_ERROR_MESSAGE_TREATMENT').'";');
	JText::script('COM_PBBOOKING_SERVICE_REQUIRES_PAYMENT');
	JText::script('COM_PBBOOKING_SERVICE_DURATION');
	JText::script('COM_PBBOOKING_MAX_SERVICE_DURATION');
	JText::script('COM_PBBOOKING_MINUTES');
	Jhtml::_('script','com_pbbooking/bowser/bowser.min.js',true,true);
	JHtml::_('script','com_pbbooking/com_pbbooking.general.js',true,true);
	JHTML::_('behavior.formvalidation');
	$form = \Pbbooking\Model\Customfields::buildFormForCustomfields();

	if ($GLOBALS['com_pbbooking_data']['config']->enable_recaptcha) {
	    JPluginHelper::importPlugin('captcha');
	    $dispatcher = JDispatcher::getInstance();
	    $dispatcher->trigger('onInit','dynamic_recaptcha_1');
	}

?>

<script>
	var dtstart = new Date("<?php echo $this->dateparam->format('Y-m-d');?>");
	dtstart.setFullYear(<?php echo $this->dateparam->format('Y');?>,<?php echo ($this->dateparam->format('n')-1);?>,<?php echo $this->dateparam->format('j');?>);
	dtstart.setHours(parseInt(<?php echo $this->dateparam->format('G');?>));
	dtstart.setMinutes(parseInt(<?php echo $this->dateparam->format('i');?>));
</script>

<h1><?php echo $doc->title;?></h1>

<?php
    $modules = JModuleHelper::getModules('pbbookingpagetop');
    foreach ($modules as $module) {
        echo JModuleHelper::renderModule($module);
    }
?>

<link rel="stylesheet" href="<?php echo JURI::root(false);?>components/com_pbbooking/user_view_multi.css"/>
<div id="pbbooking-notifications"></div>

<form action="<?php echo JRoute::_('index.php?option=com_pbbooking&task=save');?>" method="POST" id = "pbbooking-reservation-form" class="form-validate">

	<?php
		echo '<input type="hidden" name="date" id="text-date" value='.$this->dateparam->format('Ymd').'>';
	?>
	
	<h2><?php echo JText::_('COM_PBBOOKING_YOURDETAILS');?></h2>

	
	<!-- begin render custom fields -->
	<table style="width:80%;" class="pbbooking-data-table">
		<?php foreach ($form->getGroup('') as $field) :?>
			<tr>
				<td><?php echo $field->label;?></td>
				<td><?php echo $field->input;?></td>
			</tr>
		<?php endforeach;?>
	</table>
	<!-- end render custom fields -->
	
	<h2><?php echo JText::_('COM_PBBOOKING_BOOKINGDETAILS');?></h2>
	<table id="pbbooking-booking-time-table" class="pbbooking-data-table">
		<tr>
			<td style="width:200px;"><?php echo JText::_('COM_PBBOOKING_SUCCESS_DATE');?></td>
			<td><?php echo JHtml::_('date',$this->dateparam->format(DATE_ATOM),JText::_('COM_PBBOOKING_SUCCESS_DATE_FORMAT'));?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('COM_PBBOOKING_SUCCESS_TIME');?></td>
			<td><?php echo JHtml::_('date',$this->dateparam->format(DATE_ATOM),JText::_('COM_PBBOOKING_SUCCESS_TIME_FORMAT'));?></td>
		</tr>
		<!--<tr>
			<td><?php echo JText::_('COM_PBBOOKING_SUCCESS_CALENDAR');?></td>
			<td><?php echo $this->cal->name;?></td>
		</tr>-->
		<tr>
			<td><?php echo JText::_('COM_PBBOOKING_BOOKINGTYPE');?></td>
			<td>
				<select name="service_id" id="service_id">
					<option value="0"><?php echo JText::_('COM_PBBOOKING_SELECT_DEFAULT');?></option>
					<?php foreach($this->treatments as $treatment) : ?>
						<option value="<?php echo $treatment->id;?>" 
							<?php echo ($this->cal->can_book_treatment_at_time($treatment->id,$this->dateparam,$this->closing_time)) ? null : 'disabled';?>
							>
							<?php echo \Pbbooking\Pbbookinghelper::print_multilang_name($treatment,'service');?>
							<?php if ($this->config->show_prices) :?>
								<?php echo \Pbbooking\Pbbookinghelper::pbb_money_format($treatment->price);?>
							<?php endif;?>
						</option>
					<?php endforeach;?>
				</select>
			</td>
		</tr>
	</table>

	<!-- draw recaptcha if needed -->

	<?php if ($GLOBALS['com_pbbooking_data']['config']->enable_recaptcha) :?>
		<div id="dynamic_recaptcha_1"></div>
	<?php endif;?>

	<!-- end draw recaptcha -->

	
	<!-- begin render service types -->
	<?php $i=0;?>
	<h3></h3>
	<div id="service-error-msg"></div>

	<input type="hidden" name="cal_id" id="text-cal-id" value="<?php echo $this->cal->cal_id;?>"/> 
	<input type="hidden" name="date" value="<?php echo $this->dateparam->format('Ymd');?>"/>
	<input type="hidden" name="date1" value="<?php echo $this->dateparam->format('Y-m-d');?>"/>
	<input type="hidden" name="treatment-time" value="<?php echo $this->dateparam->format('Hi');?>"/>
	<input type="hidden" name="longest_time" value="<?php echo $this->longest_time;?>"/>
	<input type="hidden" name="dtstart" value="<?php echo $this->dateparam->format('Y-m-d H:i:00');?>"/>
	<input type="hidden" name="dtend" value=""/>
	<input type="hidden" name="is_variable" value="0"/>
	
	<!-- end render service types -->
	<div style="text-align:center;">
		<p></p><input type="submit" value="<?php echo JText::_('COM_PBBOOKING_SUBMIT_BUTTON');?>" id="pbbooking-submit"></p>
	</div>
</form>


<div id="pbbooking-paypal-form"></div>
	

<?php
    $modules = JModuleHelper::getModules('pbbookingpagebottom');
    foreach ($modules as $module) {
        echo JModuleHelper::renderModule($module);
    }
?>
