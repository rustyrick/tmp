<?php

	/**
	* @package		PurpleBeanie.PBBooking
	* @license		GNU General Public License version 2 or later; see LICENSE.txt
	* @link		http://www.purplebeanie.com
	*/


	defined( '_JEXEC' ) or die( 'Restricted access' );

	$doc = JFactory::getDocument();
	Jhtml::_('stylesheet','com_pbbooking/user_view_multi.css',false,true);
	
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

	//echo $this->latest->format(DATE_ATOM);
?>

<h1><?php echo $doc->title;?></h1>

<?php
    $modules = JModuleHelper::getModules('pbbookingpagetop');
    foreach ($modules as $module) {
        echo JModuleHelper::renderModule($module);
    }
?>

<div class="calendar-window">

	<div id="calendar">
		<table id="pbbooking">
			<tr>
		
				<th colspan=7 align="center">
					<a href="<?php echo JRoute::_('index.php?option=com_pbbooking&task=view&dateparam='.$last_month->format("Ymd"));?>"><<</a>
					<span class="month-heading">
						<?php echo Jhtml::_('date',$this->dateparam->format(DATE_ATOM),'F');?>
					</span>
					<a href="<?php echo JRoute::_('index.php?option=com_pbbooking&task=view&dateparam='.$next_month->format("Ymd"));?>">>></a>
				</th>
		
		
			</tr>
			
			<tr>
		
			<!-- begin header row-->
			<?php $bow = date_create('last Sunday',new DateTimeZone(PBBOOKING_TIMEZONE));?>
			
			<?php for ($i=0;$i<$this->config->calendar_start_day;$i++) :?>
				<?php $bow->modify('+1 day');?>
			<?php endfor;?>
			
			<?php $eow = clone $bow;?>
			<?php $eow->modify('+6 days');?>
			
			<?php for ($i=0;$i<=6;$i++) :?>
				
				<th><?php echo Jhtml::_('date',$bow->format(DATE_ATOM),'D');?></th>
				<?php $bow->modify("+1 day");?>
		
			<?php endfor;?>
			
			<!-- end header row-->
		
			<?php
				$curr_day = $bom;
				$curr_day->setTime(23,59,59);
			?>
		
			</tr>
		
			<tr>
			
			<!-- calc cal padding -->
			<?php if ($curr_day->format('w') < $this->config->calendar_start_day) :?>
				<?php for ($i=0;$i< 7 - ($this->config->calendar_start_day-$curr_day->format('w'));$i++) :?>
					<td></td>
				<?php endfor;?>
			<?php endif;?>
			
			<?php if ($curr_day->format('w') > $this->config->calendar_start_day) :?>
				<?php for ($i=0;$i<($curr_day->format('w') - $this->config->calendar_start_day);$i++) :?>
					<td></td>
				<?php endfor;?>
			<?php endif;?>
			
			<!-- end cal padding -->
		
			<?php for ($i=0;$i<=$num_days;$i++) :?>
				
		
				<?php if (   ($curr_day > date_create() ) && ( $this->config->allow_booking_max_days_in_advance == 0 || ( $this->config->allow_booking_max_days_in_advance > 0 && $curr_day < $this->latest ) )  ) :?>
					<td
						<?php $class = "";?>
						<?php if ($curr_day->format("z") == $this->dateparam->format("z")) :?>
							<?php $class .= "selected_day";?>
						<?php endif;?>
						<?php $free = \Pbbooking\Pbbookinghelper::free_appointments_for_day($curr_day,$this->cals);?>
						<?php $class .= ($free) ? '' : ' fully-booked ';?>
						<?php echo ($class !="") ? 'class = "'.$class.'"' : "";?>>	
						<?php if ($free) :?>
							<a href='<?php echo JRoute::_('index.php?option=com_pbbooking&task=dayview&dateparam='.$curr_day->format("Ymd"));?>'>
						<?php endif;?>
						<?php echo $curr_day->format("d");?>
						<?php if ($free) :?>
							</a>
						<?php endif;?>
					</td>
				<?php else:?>
					<td><?php echo JHtml::_('date',$curr_day->format(DATE_ATOM),'j');?></td>
				<?php endif;?>
				
				<!-- break if needed -->
				<?php if (($curr_day->format('w') == $eow->format('w'))) :?>
					</tr><tr>
				<?php endif;?>
				<!-- finish line break -->
				
				<?php $curr_day->modify("+1 day");?>
			<?php endfor;?>
			</tr>
		</table>
	</div>

</div>


<?php
    $modules = JModuleHelper::getModules('pbbookingpagebottom');
    foreach ($modules as $module) {
        echo JModuleHelper::renderModule($module);
    }
?>

<div style="clear:both;"></div>
