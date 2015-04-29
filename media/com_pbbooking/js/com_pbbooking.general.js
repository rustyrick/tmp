jQuery(document).ready(function(){
    //ie <= 9 doesn't like the validate-email class so remove.
    if (bowser.msie) {
        jQuery('.validate-email').removeClass('validate-email');
    }
});

function checkDependencies()
{
    if (typeof(jQuery) === 'undefined') {
        throw new Error("jQuery is not defined");
    }

    if (typeof(jQuery.format) === 'undefined') {

        //load sync to block anything further until loaded.
        jQuery.ajax({
            url: base_url+"media/com_pbbooking/js/jquery-dateFormat/jquery-dateFormat.min.js",
            dataType:"script",
            async: false
        });
    }



}