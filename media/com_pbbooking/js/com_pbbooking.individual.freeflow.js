var treatment_time = -1;
var dtstart = null;

jQuery(document).ready(function(){

	checkDependencies();
	load_previous_data();
	
		
	//new for 2.2 bind to the select box to load timeslots if it changes
	jQuery('#select-timegrouping').on('change',function(){
		load_slots(jQuery('#text-date').val(),jQuery(this).val(),jQuery('#treatment_id').val());
	});
	
	//override the submit button
	jQuery('#pbbooking-submit').on('click',function(e){			
		validateInput();
		return false;
	})
	
	//keep tracking the selected treatment - modified in version 2.2 to reflect select box.
	//modified again in 2.4.5.11a3 to migrate to jQuery
	jQuery('select[name="massage"]').on('change',function(){
		change_service_selection(jQuery(this).val());		
	});
	
	//bind the blur on the custom fields if is_required == 1
	for(i=0;i<$customfields.length;i++) {
		if ($customfields[i].is_required == 1) {
			
			if ($customfields[i].fieldtype=='text') {		
				jQuery('input[name="'+$customfields[i].varname+'"]').on('blur',function(){
					
					if(jQuery(this).val() == "") {
						jQuery(this).addClass('error-field');
					} else {
						jQuery(this).removeClass('error-field');
					}
					
				})
			}
			
			if ($customfields[i].fieldtype=='radio') {
				jQuery('input[name="'+$customfields[i].varname+'"]').on('click',function(){
					name = jQuery(this).attr('name');
					jQuery('.'+name+'-label').removeClass('error-label');
				})
			}			
			
			if ($customfields[i].fieldtype=='checkbox') {
				jQuery('input[name="'+$customfields[i].varname+'[]"]').on('click',function(){
					name = jQuery(this).attr('name');
					jQuery('.'+name.replace(/(.*)\[\]/,'$1')+'-label').removeClass('error-label');
				})
			}			
		}
	}

	//listen for document unload to save user data and save the need for re-entry
	jQuery(window).unload(function(){
		save_entered_data();
	});

	//listen for changes in the calendars select box and load changes as needed.
	jQuery('select[name=calendars]').on('change',function(){
		load_calendar_services(jQuery(this).val());
	});

	document.getElementById('user_offset').value = new Date().getTimezoneOffset();

});


function validateInput()
{
	error = validate_input();

	if (!error) {
		//update the date first of all....
		var dtend = dtstart;
		for (var i=0;i<$treatments.length;i++) {
			if ($treatments[i]['id'] == jQuery('input[name="service_id"]').val()) {
				dtend.setTime(dtend.getTime() + ($treatments[i]['duration'] * 60 * 1000));
				jQuery('input[name="dtend"]').val(jQuery.format.date(dtend,'yyyy-MM-dd HH:mm:00'))
			}
		}
		jQuery('#pbbooking-reservation-form').submit();	
	} 
}

/**
* load_slots(date,grouping,treatment) - called when the time grouping selector is changed to get timeslots back and draw 			
* build the calendar output in the pbbooking-timeslot-listing div
* @param string date - the currently selected date as string
* @param string grouping - the selected grouping as a string
*/	
function load_slots(date,grouping,treatment)
{
	var request_data = {'dateparam':date,'option':'com_pbbooking','task':'load_slots_for_day','format':'raw','grouping':grouping,'treatment':treatment,'user_offset':new Date().getTimezoneOffset()};
	if (jQuery('select[name="calendars"]').val())
		request_data.calendar = jQuery('select[name="calendars"]').val();
	//console.log(request_data);

	jQuery.get(base_url,request_data,function(data,textStatus,jqXHR){
		jQuery('#pbbooking-timeslot-listing').html(data);
		jQuery('input[name="treatment-time"]').on('click',function(){
			treatment_time = jQuery(this).val();
			var time_arr = treatment_time.match(/(\d\d)(\d\d)/);
			var hours = time_arr[1];
			var minutes = time_arr[2];
			
			hours.replace(/^0/,'');
			minutes.replace(/^0/,'');
			var date_arr = jQuery('input[name="date1"]').val().match(/(\d\d\d\d)-(\d\d)-(\d\d)/);
			dtstart = new Date();
			var year = parseInt(date_arr[1]);
			var month = date_arr[2];
			month =  parseInt((month.replace(/^0(\d*)/,"$1")-1));
			var day = date_arr[3];
			day = parseInt(day.replace(/^0(\d*)/,"$1"));
			dtstart.setFullYear(year,month,day);
			dtstart.setHours(parseInt(hours));
			dtstart.setMinutes(parseInt(minutes));
			jQuery('input[name="dtstart"]').val(jQuery.format.date(dtstart,'yyyy-MM-dd HH:mm:00'));
			jQuery('#text-cal-id').val( jQuery(this).attr('class').replace(/cal_id\-(\d+)/,'$1'));
		});
	});

}

/*
loops through all the custom fields and user data to see if values have been entered and rights the whole lot to cookie
*/

function save_entered_data()
{
	if (typeof(jQuery.cookie) === 'undefined')
		return false;
	
	var fielddata = '';
	$customfields.forEach(function(el,idx){
		var data = jQuery('#'+el.varname).val();
		if (data>'') {
			if (fielddata != '')
				fielddata += '|';
			var d = el.varname+'='+data;
			fielddata+= d;
		}
	});
	jQuery.cookie('pbbooking',fielddata);
}


function load_previous_data()
{
	if (typeof(jQuery.cookie) === 'undefined')
		return true;

	var fielddata = jQuery.cookie('pbbooking');
	if (fielddata) {
		//alert('have data to load');
		var fields = fielddata.split('|');
		fields.forEach(function(el,idx){
			var data = el.split('=');
			var domel = document.getElementById(data[0]);
			if (domel)
				domel.value = data[1];
		});
	}

}








/*
validate input - revised method for validating the input just to do validation and return true or false
*/

function validate_input()
{
	error = false;
	
	//validate most of the form using the Joomla! client side validation (http://docs.joomla.org/Client-side_form_validation)
	var f = document.getElementById('pbbooking-reservation-form');

    // Only run form validator if it is installed in the DOM And working.
    if (typeof(document.formvalidator) !== 'undefined') {
        if (!document.formvalidator.isValid(f)) {
            error = true;
        }
    }
	
	//do i have a service type?
	if (jQuery('input[name="treatment_id"]').val() == -1) {
		error = true;
		jQuery('#service-error-msg').html($error_msg_treatment);
		jQuery('#service-error-msg').addClass('error-message');
	}
	
	//have I selected a time slot
	if (treatment_time == -1) {
		//$('timeslot-error-msg').setProperty('text',$error_msg_timeslot);
		jQuery('#timeslot-error-msg').html($error_msg_timeslot);
		jQuery('#timeslot-error-msg').addClass('error-message');
		error = true;
	}

	return error;

}

//loads services for a calendar.
function load_calendar_services(calendar)
{

	jQuery.ajax({
		url:base_url+'index.php?option=com_pbbooking&task=load_calendar_services&format=raw&cal_id='+calendar,
		type:'GET',
		dataType:'json',
		success: function(data,textStatus,jqXHR) {
			var select_string = '<h3>'+Joomla.JText._('COM_PBBOOKING_BOOKINGTYPE')+'</h3>';
			select_string += '<div id="service-error-msg"></div>';
			select_string += '<select name="massage"><option value="0">'+Joomla.JText._('COM_PBBOOKING_SELECT_DEFAULT')+'</option>';
			//console.log(data);
			for (var i=0;i<data.length;i++) {
				service = data[i];
				select_string += '<option value="'+service.id+'">'+service.name+'</option>';
			}
			select_string += '</select>';
			jQuery('#pbbooking-services').html(select_string);
			jQuery('select[name=massage]').on('change',function(){
				change_service_selection(jQuery(this).val());
			});
		}
	});
}

function change_service_selection(service_id)
{
	jQuery('input[name=treatment_id]').val(service_id);
	jQuery('input[name="service_id"]').val(service_id);

	if (jQuery('#select-timegrouping').val() != 0 || enable_shifts == 0) {
		load_slots(jQuery('#text-date').val(),jQuery('#select-timegrouping').val(),jQuery('#treatment_id').val());
	}

	if (enable_shifts == 1) {
		jQuery('#pbbooking-shift-select').css('display','block');
	}

	//loop through all the treatments and see if payment is required...
	require_payment = false;
	jQuery($treatments).each(function(idx,el){
		//console.log('idx '+idx+' is object '+obj.name);
		if (service_id == el.id) {
			if (el.require_payment==1) {
				require_payment = true;
			}
		}
	});
	if (require_payment) {
		jQuery('#pbbooking-notifications').html(Joomla.JText._('COM_PBBOOKING_SERVICE_REQUIRES_PAYMENT')).addClass('pbbooking-notifications-active');
	} else {
		jQuery('#pbbooking-notifications').html('').removeClass('pbbooking-notifications-active');
		document.getElementById('pbbooking-paypal-form').innerHTML = '';
		document.getElementById('pbbooking-submit').style.display = 'block';
	}

}