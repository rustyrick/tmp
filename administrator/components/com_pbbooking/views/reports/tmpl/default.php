<?php 

/**
* @package		PurpleBeanie.PBBooking
* @license		GNU General Public License version 2 or later; see LICENSE.txt
* @link		http://www.purplebeanie.com
*/

defined('_JEXEC') or die('Restricted access'); 



JHtml::_('jquery.framework');
JHtml::_('behavior.modal');
JHtml::_('script','com_pbbooking/Chart.js/Chart.min.js',true,true);
JHtml::_('stylesheet','com_pbbooking/pbbooking-bootstrap-fix.css',array(),true);
JHtml::_('stylesheet','com_pbbooking/font-awesome/font-awesome.min.css',array(),true);
Jhtml::_('stylesheet','com_pbbooking/purplebeaniefont/purplebeaniefont.css',array(),true);

?>




<form action="<?php echo JRoute::_('index.php?option=com_pbbooking&view=reports');?>" method="post" name="adminForm" id="adminForm">

	<div class="row-fluid">
		<h2><?php echo JFactory::getConfig()->get('sitename');?> <?php echo JText::_('COM_PBBOOKING_DASHBOARD_REPORTS');?></h2>
		<div class="span12">
			<canvas id="calendarutilization" width="1000px" height="300px" style="align:center;margin:0px auto;"></canvas>
		</div>
	</div>

	<div class="row-fluid">
		<div class="span12">
			<h3><?php echo JText::_('COM_PBBOOKING_DASHBOARD_REPORTS_LAST_10_VALIDATED_BOOKINGS');?>&nbsp;&nbsp;<a href="?option=com_pbbooking&task=report.download&id=1&target=_blank&format=raw"><span class="fa fa-download"></span></a></h3>
			<div class="row-striped">
				<?php foreach ($this->items['last_10_validated'] as $booking) :?>
				<div class="row-fluid">
					<div class="span4"><?php echo $booking->getSummary();?></div>
					<div class="span4"><?php echo $booking->dtstart->format(DATE_ATOM);?></div>
					<div class="span4"><?php echo $booking->getService()->name;?></div>

				</div>
				<?php endforeach;?>
			</div>
		</div>
	</div>

	<div class="row-fluid" style="margin-top:2em;">
		<div class="span12">
			<h3><?php echo JText::_('COM_PBBOOKING_DASHBOARD_REPORTS_LAST_10_NEW_CLIENTS');?>&nbsp;&nbsp;<a href="?option=com_pbbooking&task=report.download&id=2&target=_blank&format=raw"><span class="fa fa-download"></span></a></h3>
			<div class="row-striped">
				<?php foreach ($this->items['last_10_new'] as $new) :?>
					<div class="row-fluid">
						<div class="span4"><?php echo htmlspecialchars($new->email);?></div>
						<div class="span4"><?php echo htmlspecialchars($new->getFirstName());?></div>
						<div class="span4"><?php echo htmlspecialchars($new->getLastName());?></div>
					</div>
				<?php endforeach;?>
				
			</div>
		</div>
	</div>
	<h3><?php echo JText::_('COM_PBBOOKING_DASHBOARD_REPORTS_MISCALANEOUS_REPORTS');?></h3>
	<div class="row-fluid">
		<div class="span12 cpanel">
			<div class="icon">
				<a href="?option=com_users&view=users&layout=modal&tmpl=component&field=selected_user" class="modal">
					<div class="fa fa-user fa-5x"></div>
					<div><?php echo JText::_('COM_PBBOOKING_DASHBOARD_REPORTS_BOOKINGS_FOR_USER');?></div>
				</a>
			</div>
		</div>
	</div>



	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>


<script>

	<?php 
		$bom = date_create('first day of',new DateTimeZone(PBBOOKING_TIMEZONE));
		$eom = date_create('last day of',new DateTimeZone(PBBOOKING_TIMEZONE));
		$labels = array();
		while ($bom<=$eom) {
			$labels[] = $bom->format(JText::_('COM_PBBOOKING_DATE_FORMAT'));
			$bom->modify('+1 day');
		}

		$datasets = array();
		$i=0;
		foreach ($GLOBALS['com_pbbooking_data']['calendars'] as $cal)
		{
			$bom = date_create("first day of",new DateTimeZone(PBBOOKING_TIMEZONE));
			list($r,$g,$b) = array_map('hexdec',str_split(ltrim($cal->color,'#'),2));
			$datasets[$i]['fillColor'] = "rgba($r,$g,$b,0.5)";
			$datasets[$i]['strokeColor'] = "rgba($r,$g,$b,1)";
			$datasets[$i]['pointColor'] = "rgba($r,$g,$b,1)";
			$datasets[$i]['fillStrokeColor'] = "#fff";
			$datasets[$i]['data'] = array();
			while ($bom <= $eom) {
				$datasets[$i]['data'][] = (isset($cal->dayEventMap[$bom->format('dmY')])) ? count($cal->dayEventMap[$bom->format('dmY')]) : 0;
				$bom->modify('+1 day');
			}
			$i++;
		}


	?>

	var lineChartData = {
		labels : <?php echo json_encode($labels);?>,
		datasets : <?php echo json_encode($datasets);?>
		
	}

	var myLine = new Chart(document.getElementById("calendarutilization").getContext("2d")).Line(lineChartData);

	/**
	 * This is called by the com_users model to push the user_id back into the view
	 */
	function jSelectUser_selected_user(id,username)
	{
		SqueezeBox.close();
		window.open('?option=com_pbbooking&task=report.download&&format=raw&id=3&uid='+id,'_blank');
	}

</script>

