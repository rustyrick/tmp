jQuery(document).ready(function(){

	checkDependencies();

	jQuery('#service_id').on('change',function(){
		//loop through all the treatments and see if payment is required...
		var service_id = jQuery(this).val();
		require_payment = false;
		is_variable = false;
		jQuery($treatments).each(function(idx,obj){
			
			if (service_id == obj.id) {
				
				var dtend = dtstart;
				dtend.setTime(dtend.getTime() + (obj.duration * 60 * 1000));
				jQuery('input[name="dtend"]').val(jQuery.format.date(dtend,'yyyy-MM-dd HH:mm:00'));

				if (obj.require_payment==1) {
					require_payment = true;
				}
				if (obj.is_variable == 1)
					is_variable = true;
			}
		});
		if (require_payment) {
			jQuery('#pbbooking-notifications').html(Joomla.JText._('COM_PBBOOKING_SERVICE_REQUIRES_PAYMENT')).addClass('pbbooking-notifications-active');
		} else {
			jQuery('#pbbooking-notifications').html('').removeClass('pbbooking-notifications-active');
		}

		if (is_variable) {
			if (document.getElementById('pbbooking-reservation-form').is_variable.value != 1) {
				//create the variable rows and push them into the booking data table.
				var table = document.getElementById('pbbooking-booking-time-table');
				var s = '<td>'+Joomla.JText._('COM_PBBOOKING_SERVICE_DURATION')+'</td><td><input type="text" name="duration" value=""/> '+Joomla.JText._('COM_PBBOOKING_MAX_SERVICE_DURATION')+' '+document.getElementById('pbbooking-reservation-form').longest_time.value+' '+Joomla.JText._('COM_PBBOOKING_MINUTES')+'</td>';
				var tr = document.createElement('tr');
				tr.innerHTML = s;
				tr.setAttribute('id','duration-row');
				table.appendChild(tr);
				document.getElementById('pbbooking-reservation-form').is_variable.value = 1
			}
		} else {
			//we may need to destroy the existing duration row
			var tr = document.getElementById('duration-row');
			if (tr)
				tr.destroy();
			document.getElementById('pbbooking-reservation-form').is_variable.value = 0
		}
	});


		//override the submit button
		jQuery('#pbbooking-submit').on('click',function(){	
			
			

			var error = validate_input();
			if (!error) {
				document.getElementById('pbbooking-reservation-form').submit();
			} else {
				return false;
			}
		});

});




/*
validate input - revised method for validating the input just to do validation and return true or false
*/

function validate_input()
{
	error = false;
	//alert('validate input');

    if (typeof(document.formvalidator) !== 'undefined') {
        var f = document.getElementById('pbbooking-reservation-form');
        if (!document.formvalidator.isValid(f)) {
            error = true;
        }
    }
	
	//do i have a service type?
	if (jQuery('select[name="service_id"]').val() == 0) {
		error = true;
		jQuery('#service-error-msg').html($error_msg_treatment);
		jQuery('#service-error-msg').addClass('error-message');
	}
	
	//have I selected a time slot
	var treatment_time = jQuery('input[name="treatment-time"]').val();
	if (treatment_time == -1) {
		jQuery('#timeslot-error-msg').html($error_msg_timeslot);
		jQuery('#timeslot-error-msg').addClass('error-message');
		error = true;
	}

	//is it a variable appointment???
	if (document.getElementById('pbbooking-reservation-form').is_variable.value == 1) {
		var duration = document.getElementById('pbbooking-reservation-form').duration.value;
		if (duration == '' || parseInt(duration) > parseInt(document.getElementById('pbbooking-reservation-form').longest_time.value)) {
			error = true;
			document.getElementById('pbbooking-reservation-form').duration.className = document.getElementById('pbbooking-reservation-form').duration.className+' error-field';
		} else {
			//let's also update the dtend
			var dtend = dtstart;
			dtend.setTime(dtend.getTime() + (duration * 60 * 1000));
			jQuery('input[name="dtend"]').val(jQuery.format.date(dtend,'yyyy-MM-dd HH:mm:00'));
		}
	}

	return error;

}

