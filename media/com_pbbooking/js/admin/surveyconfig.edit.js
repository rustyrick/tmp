var surveyQuestions;


jQuery(document).ready(function(){

   
    surveyQuestions = new SurveyModel();


    ko.applyBindings(surveyQuestions);
});

function SurveyModel()
{
    var self = this;

    var surveyQuestions;

    if ( jQuery('#jform_testimonial_questions').val() != '' )
        surveyQuestions = JSON.parse( jQuery('#jform_testimonial_questions').val() );
    else
        surveyQuestions = [{testimonial_field_values:'',testimonial_field_type:'',testimonial_field_varname:'',testimonial_field_label:''}];

    self.questions = ko.observableArray(surveyQuestions);

    self.addQuestion = function(){
        self.questions.push({testimonial_field_values:'',testimonial_field_type:'',testimonial_field_varname:'',testimonial_field_label:''});
    }

    self.removeQuestion = function(){
        self.questions.remove(this);
    }
}

Joomla.submitbutton = function(task)
{
    

    jQuery('#jform_testimonial_questions').val(JSON.stringify(surveyQuestions.questions()));
    
    //now just submit form.
    Joomla.submitform(task, document.getElementById('adminForm'));



    return false;

}