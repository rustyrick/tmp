var calendarhours;
var classes;

jQuery(document).ready(function(){

    bindHours();

    classes = new ClassesModel();

    ko.applyBindings(classes,document.getElementById('ko-classschedule'));

});

function HoursModel()
{
    var self = this;
    
    self.hours = ko.observableArray(jQuery.parseJSON(jQuery('#jform_hours').val()));

}

function ClassesModel()
{
    var self = this;

    var schedule;

    if (jQuery('#jform_calendar_schedule').val() == '') {
        schedule = [{'dayofweek':0,'time':'',endtime:''}];
    }
    else{
        schedule = jQuery.parseJSON(jQuery('#jform_calendar_schedule').val());
    }


    self.classes = ko.observableArray(schedule);

    self.addClassTime = function() {
        self.classes.push({dayofweek:0,time:'','endtime':''});
    }

    self.removeClass = function() {
        self.classes.remove(this);
        return false;
    }

}


function bindHours()
{
    calendarhours = new HoursModel();
    ko.applyBindings(calendarhours,document.getElementById('ko-tradinghours'));
}

Joomla.submitbutton = function(task)
{
    if (typeof(calendarhours) !== 'undefined') 
    {
        jQuery('#jform_hours').val(JSON.stringify(calendarhours.hours()));

        if (jQuery('#jform_groupbookings').val() == 1)
            jQuery('#jform_calendar_schedule').val(JSON.stringify(classes.classes()));
    }

    Joomla.submitform(task, document.getElementById('adminForm'));

}