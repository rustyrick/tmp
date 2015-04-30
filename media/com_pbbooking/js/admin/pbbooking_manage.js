jQuery(document).ready(function($){


	if (jQuery.timepicker != undefined) jQuery.timepicker.setDefaults(dtPickerLocalisation);
	if (jQuery.datepicker != undefined) jQuery.datepicker.setDefaults(dtLocalisation);

	//check for fullCalendarLocalisation & sent the event handlers
	if (fullCalendarLocalisation) {
		fullCalendarLocalisation.dayClick = processDayClick;
		fullCalendarLocalisation.eventClick = processEventClick;
		fullCalendarLocalisation.eventMouseOver = processEventMouseOver;
		fullCalendarLocalisation.eventDrop = processEventDrop;
		fullCalendarLocalisation.eventResize = processEventResize;
		fullCalendarLocalisation.eventDragStart = shouldEventMove;
	}
	else
		alert('fullCalendarLocalisation is not set');

	//modify the fullCalendarLocalisation for version 2 of jQuery FullCalendar (http://arshaw.com/fullcalendar/docs/utilities/Duration/)
	fullCalendarLocalisation.minTime = moment.duration(fullCalendarLocalisation.minTime.toString()+":00");
	fullCalendarLocalisation.maxTime = moment.duration(fullCalendarLocalisation.maxTime.toString()+":00");
	
	//now load up the calendar.
	//main_cal = $('#calendar').fullCalendar(fullCalendarLocalisation);
	main_cal = $('#calendar').fullCalendar(fullCalendarLocalisation);
	
	//jump to a specific date if it's set
	if (typeof dateparam != 'undefined')
		$('#calendar').fullCalendar('gotoDate',dateparam);




	$('#pbbooking-search').click(function(){

		jQuery.ajax({
			url: base_url+'administrator/index.php?option=com_pbbooking&task=manage.ajax_search&format=raw',
			type: 'POST',
			data: jQuery('#pbboking-search-query').serialize(),
			dataType: 'json',
			success: function(data,textStatus,jqXHR) {
				//update the dom with search results.
				var html = '<div id="results-close-button"></div><ul>';
				for (var i=0;i<data.length;i++) {
					html+='<li data-date="'+data[i].dtend+'">'+data[i].summary+'</li>';
				}
				html+='</ul>';
				jQuery('#pbbooking-search-results').html(html);

				//also apply the styling to this.
				jQuery('#pbbooking-search-results').addClass('has-results');

				//attach event handlers
				jQuery('#pbbooking-search-results li').on('click',jump_to_date);
				jQuery('#results-close-button').on('click',remove_search_results);

			}
		});

	});
});

function jump_to_date(event)
{
	//console.log(jQuery(event.target).data('date'));
	//2013-10-03 11:00:00
	var dtstring = jQuery(event.target).data('date');
	var year = dtstring.replace(/(\d+)-.*/,'$1');
	var month = dtstring.replace(/\d+-0?(\d+).*/,'$1');
	var day = dtstring.replace(/\d+-\d+-0?(\d+).*/,'$1');

	jQuery('#calendar').fullCalendar('gotoDate',dtstring);

	//now let's just clean out the search results.
	remove_search_results();
}


function remove_search_results()
{
	document.getElementById('pbbooking-search-results').innerHTML ='';
	jQuery('#pbbooking-search-results').removeClass('has-results');
}

var processDayClick = function(date,jsEvent,view) {
	var loading_string = "<p style='text-align:center;'><?php echo JText::_('COM_PBBOOKING_MODAL_LOADING');?></p>";
	jQuery('#modal').html(loading_string);
	jQuery('#modal').dialog({width:'900px'});
	//console.log($.fullCalendar.formatDate(date,'yyyy-MM-dd H:mm:ss'));
	jQuery.get('index.php?option=com_pbbooking&task=manage.ajax_create&format=raw&date='+date.format('YYYY-MM-DD HH:mm:ss'),function(data){
		jQuery('#modal').html(data);
		jQuery('#modal').dialog({position:{my:"center",at:"center",of:window}});

		//listen for the create form to be saved.
		jQuery('#modal').find('#buttonSaveEvent').click(function(){

			if (jQuery('select[name="cal_id"]').val() == 0) {
				alert(Joomla.JText._('COM_PBBOOKING_CREATE_ERROR_CHOOSE_CALENDAR'));
				return false;
			}

			if (isValidEvent(jQuery('#create-event-form').serializeArray()) == false)
			{
				alert(Joomla.JText._('COM_PBBOOKING_CREATE_ERROR'));
				return false;
			}

			if (jQuery('#buttonSaveEvent').hasClass('disabled'))
				return false;

			jQuery('#buttonSaveEvent').addClass('disabled');

			jQuery.ajax({
				url: 'index.php?option=com_pbbooking&task=manage.ajax_create&format=raw',
				type: 'POST',
				data: jQuery('#create-event-form').serialize(),
				dataType: 'json',
				success: function(data,textStatus,jqXHR) {
					if (data.status == 'success') {
						jQuery('#modal').dialog('close');
						jQuery('#calendar').fullCalendar('refetchEvents');
						//$('#modal').remove();
					} else 
						alert(data.message);
						jQuery('#buttonSaveEvent').removeClass('disabled');
				}
			})
		})
	});
}

var processEventClick = function(event,jsEvent,view) {
	if (event.externalevent == 1) {
		alert(Joomla.Jtext._('COM_PBBOOKING_EXTERNAL_EVENT_WARNING'));
		return false;
	}
	var loading_string = "<p style='text-align:center;'><?php echo JText::_('COM_PBBOOKING_MODAL_LOADING');?></p>"
	jQuery('#modal').html(loading_string);
	jQuery('#modal').dialog({width:'900px'});
	jQuery.get('index.php?option=com_pbbooking&task=manage.ajax_edit&format=raw&event_id='+event.id,function(data){
		jQuery('#modal').html(data);
		jQuery('#modal').dialog({position:{my:"center",at:"center",of:window}});

		//listen for the update form to saved.
		jQuery('#modal').find('a#update-event').click(function(){

			if (jQuery('#update-event').hasClass('disabled'))
				return false;

			jQuery('#update-event').addClass('disabled');

			jQuery.ajax({
				url:'index.php?option=com_pbbooking&task=manage.ajax_save&format=raw',
				type:'POST',
				data: jQuery('.pbbooking-edit-event').serialize(),
				dataType: 'json',
				success: function(data,textStatus,jqXHR) {
					if (data.status == 'success') {
						jQuery('#modal').dialog('close');
						jQuery('#calendar').fullCalendar('refetchEvents');
					} else
						alert(data.message);
						jQuery('#update-event').removeClass('disabled');
				}
			});
		});

		//listen for the deleve event to be clicked
		jQuery('#modal').find('a#delete-event').on('click',processDeleteEvent);
	})

}

var processEventMouseOver = function(event,jsEvent,view) {
	jQuery(jsEvent.target).attr('title',event.title);
}

var processEventDrop = function(event,delta,revertFunc) {
	jQuery.get('index.php?option=com_pbbooking&task=manage.update_calendar_event&format=raw&event='+event.id+'&delta='+delta.asSeconds());	
}

var processEventResize = function(event, delta, revertFunc) {
	jQuery.get('index.php?option=com_pbbooking&task=manage.update_calendar_event&format=raw&event='+event.id+'&delta='+delta.asSeconds()+"&action=resize");	
}

var shouldEventMove = function(event,jsEvent,ui,view){
	if (event.externalevent == 1)
	{
		alert(Joomla.JText._('COM_PBBOOKING_EXTERNAL_EVENT_WARNING'));
		return false;
	}
}

var processDeleteEvent = function()
{
	if (jQuery('#delete-event').hasClass('disabled'))
		return false;

	jQuery('#update-event').addClass('disabled');
	jQuery('#delete-event').addClass('disabled');

	jQuery('.pbbooking-edit-event').find('input[name="task"]').val('ajax_delete');

	jQuery.ajax({
		url: 'index.php?option=com_pbbooking&task=manage.ajax_delete&format=raw',
		type: 'POST',
		data: jQuery('.pbbooking-edit-event').serialize(),
		dataType: 'json',
		success: function(data,textStatus,jqXHR) {
			if (data.status == 'success') {
				jQuery('#modal').dialog('close');
				jQuery('#calendar').fullCalendar('refetchEvents');
			} else 
				alert(data.message);
		}
	});
}

function isValidEvent(dataArray)
{
	var dataObj = {};
 
	jQuery.each(dataArray, function () {
		dataObj[this.name] = this.value;
	});

	// Check that dtend & dtstart are valid
	var dtstart = moment(dataObj.dtstart);
	var dtend = moment(dataObj.dtend);
	var now = moment();

	if (dtend <= dtstart)
	{
		return false;
	}

	if (dtstart <= now)
	{
		return false;
	}

	// Check that required customfields have been populated
	var error = false;

	jQuery.each(customfields,function(idx,el)
	{
		console.log('checking custom fields');
		if (el.is_required == 1 && ( typeof(dataObj[el.varname]) == 'undefined' || dataObj[el.varname] == '' ))
		{
			console.log('checking ',el.varname);
			error = true;
		}
	});

	if (error == true)
	{
		return false;
	}

	return true;
 
}