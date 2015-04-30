jQuery(document).ready(function($){


    if (jQuery.timepicker != undefined) jQuery.timepicker.setDefaults(dtPickerLocalisation);
    if (jQuery.datepicker != undefined) jQuery.datepicker.setDefaults(dtLocalisation);

    //check for fullCalendarLocalisation & sent the event handlers
    if (fullCalendarLocalisation) {
        fullCalendarLocalisation.dayClick = processDayClick;
        fullCalendarLocalisation.eventClick = processEventClick;
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

});


var processDayClick = function(date,jsEvent,view) {
    var loading_string = "<p style='text-align:center;'><?php echo JText::_('COM_PBBOOKING_MODAL_LOADING');?></p>";
    jQuery('#modal').html(loading_string);
    jQuery('#modal').dialog({width:'900px'});
    //console.log($.fullCalendar.formatDate(date,'yyyy-MM-dd H:mm:ss'));
    jQuery.get('?option=com_ajax&module=pbbmanage&method=createEvent&format=raw&date='+date.format('YYYY-MM-DD HH:mm:ss'),function(data){
        jQuery('#modal').html(data);
        jQuery('#modal').dialog({position:{my:"center",at:"center",of:window}});

        //bind the dtpickers
        jQuery('#modal').find('.dtpicker').datetimepicker({timeFormat:'HH:mm:ss', dateFormat: 'yy-mm-dd',onClose:recalc_end_time});



        //listen for the create form to be saved.
        jQuery('#modal').find('#buttonSaveEvent').click(function(){

            if (jQuery('select[name="cal_id"]').val() == 0) {
                alert(Joomla.JText._('COM_PBBOOKING_CREATE_ERROR_CHOOSE_CALENDAR'));
                return false;
            }

            if (jQuery('#buttonSaveEvent').hasClass('disabled'))
                return false;

            jQuery('#buttonSaveEvent').addClass('disabled');

            jQuery.ajax({
                url: '?option=com_ajax&module=pbbmanage&method=saveEvent&format=raw',
                type: 'POST',
                data: jQuery('#mod_pbbmanage_form').serialize(),
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
            });
            return false;
        })
    });
}

var processEventClick = function(event,jsEvent,view) {
    if (event.externalevent == 1 || event.editable == false) {
        alert(Joomla.JText._('COM_PBBOOKING_EXTERNAL_EVENT_WARNING'));
        return false;
    }
    var loading_string = "<p style='text-align:center;'><?php echo JText::_('COM_PBBOOKING_MODAL_LOADING');?></p>"
    jQuery('#modal').html(loading_string);
    jQuery('#modal').dialog({width:'900px'});
    jQuery.get('?option=com_ajax&module=pbbmanage&method=displayEvent&format=raw&event_id='+event.id,function(data){
        jQuery('#modal').html(data);
        jQuery('#modal').dialog({position:{my:"center",at:"center",of:window}});

        //bind the dtpickers
        jQuery('#modal').find('.dtpicker').datetimepicker({timeFormat:'HH:mm:ss', dateFormat: 'yy-mm-dd',onClose:recalc_end_time});


        //listen for the update form to saved.
        jQuery('#modal').find('button#buttonSaveEvent').click(function(){

            if (jQuery('#buttonSaveEvent').hasClass('disabled'))
                return false;

            jQuery('#buttonSaveEvent').addClass('disabled');

            jQuery.ajax({
                url:'?option=com_ajax&module=pbbmanage&method=updateEvent&format=raw',
                type:'POST',
                data: jQuery('#mod_pbbmanage_form').serialize(),
                dataType: 'json',
                success: function(data,textStatus,jqXHR) {
                    if (data.status == 'success') {
                        jQuery('#modal').dialog('close');
                        jQuery('#calendar').fullCalendar('refetchEvents');
                    } else
                        alert(data.message);
                        jQuery('#buttonSaveEvent').removeClass('disabled');
                }
            });

            return false;
        });

        //listen for the deleve event to be clicked
        jQuery('#modal').find('button#buttonDeleteEvent').on('click',processDeleteEvent);


    })

}


var processEventDrop = function(event,delta,revertFunc) {
    jQuery.get('?option=com_ajax&module=pbbmanage&method=dropEvent&format=raw&event='+event.id+'&delta='+delta.asSeconds());  
}

var processEventResize = function(event, delta, revertFunc) {
    jQuery.get('?option=com_ajax&module=pbbmanage&method=dropEvent&format=raw&event='+event.id+'&delta='+delta.asSeconds()+"&action=resize"); 
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
    if (jQuery('#buttonDeleteEvent').hasClass('disabled'))
        return false;

    jQuery('#buttonSaveEvent').addClass('disabled');
    jQuery('#buttonDeleteEvent').addClass('disabled');

    jQuery.ajax({
        url: '?option=com_ajax&module=pbbmanage&method=deleteEvent&format=raw',
        type: 'POST',
        data: jQuery('#mod_pbbmanage_form').serialize(),
        dataType: 'json',
        success: function(data,textStatus,jqXHR) {
            if (data.status == 'success') {
                jQuery('#modal').dialog('close');
                jQuery('#calendar').fullCalendar('refetchEvents');
            } else 
                alert(data.message);
        }
    });

    return false;
}


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