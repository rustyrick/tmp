<?php 

/**
* @package		PurpleBeanie.PBBooking
* @license		GNU General Public License version 2 or later; see LICENSE.txt
* @link		http://www.purplebeanie.com
*/

defined('_JEXEC') or die('Restricted access'); 

JHtml::_('stylesheet','com_pbbooking/pbbooking-bootstrap-fix.css',array(),true);
JHtml::_('stylesheet','com_pbbooking/font-awesome/font-awesome.min.css',array(),true);
Jhtml::_('stylesheet','com_pbbooking/purplebeaniefont/purplebeaniefont.css',array(),true);
JHtml::_('behavior.modal', 'a.modal');

?>




<form action="<?php echo JRoute::_('index.php?option=com_content&view=articles');?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<div class="span12 cpanel">
			<div class="icon">
				<a href="?option=com_pbbooking&view=calendars&list[limit]=0">
					<div class="fa fa-group fa-5x"></div>
					<div><?php echo JText::_('COM_PBBOOKING_RESOURCES_DISPLAY');?></div>
				</a>
			</div>
			<div class="icon">
				<a href="?option=com_pbbooking&view=services&list[limit]=0">
					<div class="fa fa-stethoscope fa-5x"></div>
					<div><?php echo JText::_('COM_PBBOOKING_TREATMENT_DISPLAY');?></div>
				</a>
			</div>

			<div class="icon">
				<a href="?option=com_pbbooking&task=tradinghours.edit">
					<div class="fa fa-clock-o fa-5x"></div>
					<div><?php echo JText::_('COM_PBBOOKING_OFFICE_TRADING_HOURS');?></div>
				</a>
			</div>

			<div class="icon">
				<a href="?option=com_pbbooking&view=customfields&list[limit]=0">
					<div class="fa fa-tags fa-5x"></div>
					<div><?php echo JText::_('COM_PBBOOKIONG_CUSTOMFIELDS');?></div>
				</a>
			</div>



			<div class="icon">
				<a href="?option=com_pbbooking&view=holidays&list[limit]=0">
					<div class="fa fa-gift fa-5x"></div>
					<div><?php echo JText::_('COM_PBBOOKING_HOLIDAYS');?></div>
				</a>
			</div>

			<div class="icon">
				<a href="?option=com_pbbooking&task=reminders.edit&id=<?php echo $GLOBALS['com_pbbooking_data']['config']->id;?>">
					<div class="pbfont pbfont-reminder fa-5x"></div>
					<div><?php echo JText::_('COM_PBBOOKING_SUB_MENU_CLIENT_REMINDERS');?></div>
				</a>
			</div>

			<div class="icon">
				<a href="?option=com_pbbooking&task=surveyconfig.edit&id=<?php echo $GLOBALS['com_pbbooking_data']['config']->id;?>">
					<div class="pbfont pbfont-trophy-configure fa-5x"></div>
					<div><?php echo JText::_('COM_PBBOOKING_SUB_MENU_TESTIMONIAL_CONFIG');?></div>
				</a>
			</div>

			<div class="icon">
				<a href="?option=com_pbbooking&task=configuration.edit&id=<?php echo $GLOBALS['com_pbbooking_data']['config']->id;?>">
					<div class="fa fa-cogs fa-5x"></div>
					<div><?php echo JText::_('COM_PBBOOKING_SUB_MENU_MANAGE_CONFIGURATION');?></div>
				</a>
			</div>

			<div class="icon">
				<a href="?option=com_pbbooking&view=multilangs&list[limit]=0">
					<div class="fa fa-comment fa-5x"></div>
					<div><?php echo JText::_('COM_PBBOOKING_ENABLE_MULTILANGUAGE');?></div>
				</a>
			</div>


			<div class="icon">
				<a href="?option=com_pbbooking&task=manage.display">
					<div class="fa fa-calendar fa-5x" style="color:green;"></div>
					<div><?php echo JText::_('COM_PBBOOKING_SUB_MENU_MANAGE_DIARIES');?></div>
				</a>
			</div>

			<div class="icon">
				<a href="?option=com_pbbooking&view=reports">
					<div class="fa fa-bar-chart-o fa-5x" style="color:green;"></div>
					<div><?php echo JText::_('COM_PBBOOKING_DASHBOARD_REPORTS');?></div>
				</a>
			</div>

			<div class="icon">
				<a href="?option=com_pbbooking&view=testimonials&list[limit]=0">
					<div class="fa fa-trophy fa-5x" style="color:green;"></div>
					<div><?php echo JText::_('COM_PBBOOKING_SUB_MENU_TESTIMONIALS');?></div>
				</a>
			</div>


		</div>
	</div>
	<div class="row-fluid">
		
		<!-- Begin Content -->
		<div class="span<?php echo ($this->config->disable_pending_bookings == 1) ? '12' : '6';?>">
			<div class="well well-small">
				<div class="module-title nav-header"><?php echo JText::_('COM_PBBOOKING_DASHBOARD_UPCOMING_BOOKINGS');?></div>
				<div class="row-striped">
					<?php if (count($this->upcoming_events)>0) :?>
						<?php foreach ($this->upcoming_events as $event) :?>
							<?php $ev_obj = new \Pbbooking\Model\Event($event->id);?>
							<div class="row-fluid">
								<div class="span9"><strong class="row-title"><a href="<?php echo JURI::root(false);?>administrator/index.php?option=com_pbbooking&task=manage.display&dateparam=<?php echo $ev_obj->dtstart->format('Y-m-d');?>"><?php echo htmlspecialchars($ev_obj->getSummary());?></a></strong></div>
								<div class="span3"><i class="icon-calendar"></i>&nbsp;<?php echo JHtml::_('date',$ev_obj->dtstart->format(DATE_ATOM),JText::_('COM_PBBOOKING_DASHBOARD_DTFORMAT'));?></div>
							</div>
						<?php endforeach;?>
					<?php else:?>
						<div class="row-fluid">
							<div class="span12"><strong class="row-title"><?php echo JText::_('COM_PBBOOKING_DASHBOARD_NOTHING_FOUND');?></strong></div>
						</div>	
					<?php endif;?>
				</div>
			</div>

			
		</div>

		<?php if ($this->config->disable_pending_bookings != 1) :?>

			<div class="span6">
				<div class="well well-small">
					<div class="module-title nav-header"><?php echo JText::_('COM_PBBOOKING_DASHBOARD_LATEST_PENDING_BOOKINGS');?></div>
					<div class="row-striped">
						<?php foreach ($this->pending_events as $event):?>
							<?php $ev_obj = new \Pbbooking\Model\Event($event->id);?>
							<div class="row-fluid">
								<div class="span3"><i class="icon-calendar"></i> <?php echo JHtml::_('date',$ev_obj->dtstart->format(DATE_ATOM),JText::_('COM_PBBOOKING_DASHBOARD_DTFORMAT'));?></div>
								<div class="span5"><?php echo htmlspecialchars($ev_obj->getSummary());?></div>
								<div class="span4">
									<a href="<?php echo JRoute::_('index.php?option=com_pbbooking&task=event.confirmdelete&id='.$event->id.'&format=raw');?>" class="fa fa-trash-o modal"></a>
								</div>
							</div>
						<?php endforeach;?>
					</div>
				</div>

				
			</div>
		<?php endif;?>

		<!-- End Content -->
	</div>



	<div class="row-fluid">
		<div class="span6">
			<div class="well well-small">
				<div class="module-title nav-header"><?php echo JText::_('COM_PBBOOKING_DASHBOARD_CALENDAR_UTILIZATION_CURRENT_WEEK');?></div>

				<div class="row-striped">
					<?php foreach ($this->cals as $cal) :?>
						<div class="row-fluid">
							<div class="span9"><?php echo $cal->name;?></div>
							<div class="span3"><?php echo sprintf('%0.2f',$cal->get_calendar_utilization($this->dtstart,$this->dtend));?>%
</div>
						</div>
					<?php endforeach;?>
				</div>
			</div>
		</div>

		<div class="span6">
			<div class="well well-small">
				<div class="module-title nav-header"><?php echo JText::_('COM_PBBOOKING_DASHBOARD_LATEST_SYNCS');?></div>
				<div class="row-striped">
					<?php foreach ($this->last_syncs as $sync) :?>
						<div class="row-fluid">
							<?php $event = json_decode($sync->data,true);?>
							<div class="span6"><?php echo htmlspecialchars($event['summary']);?></div>
							<div class="span4"><?php echo htmlspecialchars($event['dtstart']['date']);?></div>
							<div class="span1"><?php echo $sync->action;?></div>
							<div class="span1">
								<span class="label label-<?php echo $sync->status;?>">
									<?php echo $sync->status;?>
								</span>
							</div>
						</div>
					<?php endforeach;?>
				</div>
			</div>
		</div>
	</div>

	<div class="row-fluid">
		<div class="span12 cpanel">
			<div class="well well-small">
				<div class="module-title nav-header"><?php echo JText::_('COM_PBBOOKING_DASHBOARD_TOOLS');?></div>
				<div class="icon">
					<a href="<?php echo JURI::root(false);?>administrator/index.php?option=com_pbbooking&task=tools.migrate">
						<img src="components/com_pbbooking/images/UI_111.png" width="36" height="36"/>
						<span><?php echo JText::_('COM_PBBOOKING_MIGRATE_DBASE');?></span>
					</a>
				</div>

				<div class="icon">
					<a href="?option=com_pbbooking&task=tools.unlink">
						<div class="fa fa-unlink fa-5x" style="color:red;"></div>
						<span><?php echo JText::_('COM_PBBOOKING_GOOGLE_UNLINK');?></span>
					</a>
				</div>


				<div class="icon">
					<a href="?option=com_pbbooking&task=tools.purgesync">
						<div class="fa fa-trash-o fa-5x"></div>
						<div><?php echo JText::_('COM_PBBOOKING_DASHBOARD_PURGE_SYNC');?></div>
					</a>
				</div>
			</div>
		</div>
	</div>


	<?php if ($this->config->disable_announcements != 1) :?>

		<div class="row-fluid">
			<div class="span12">
				<div class="well well-small">
					<div class="module-title nav-header"><?php echo JText::_('COM_PBBOOKING_DASHBOARD_ANNOUNCEMENTS');?></div>
						<div class="row-striped">
						<?php foreach ($this->announcements as $announcement) :?>
						<div class="row-fluid">
							<div class="span12">
								<strong><?php echo $announcement->get_title();?></strong>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12">
								<?php echo $announcement->get_content();?>
								<p>
									<hr style="width:90%;margin:0px auto;color:#F0F0EE;"/>
								</p>
								<i><?php echo JText::_('COM_PBBOOKING_DASHBOARD_ANNOUNCEMENT_POSTED');?> <?php echo @$announcement->get_date();?></i>
							</div>
						</div>

						<?php endforeach;?>
					</div>
				</div>
			</div>
		</div>

	<?php endif;?>




	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php //echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php //echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>

