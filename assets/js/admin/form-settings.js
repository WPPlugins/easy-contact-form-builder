jQuery(document).ready(function () {

    var formSubmitting = true;

    tinyMCE.init({
        mode : "specific_textareas",
        editor_selector : "setting-row"
    });

    /* save form settings with ajax */
    jQuery('.gdfrm_edit_form_settings_container').on("click","#save-form-button", function () {
        var name = jQuery("#form_name").val();
        var id = jQuery("#form_id").val();
        var grandFormSettings = jQuery('#grand-form-settings');

        var editors = jQuery('.wp-editor-wrap');

        var names = [];

        editors.each(function () {
            names.push(jQuery(this).find('textarea.setting-row').attr('name'));
        })

        formSettingsData = grandFormSettings.serializeArray();

        finalFormSettingsData = [];

        formSettingsData.forEach(function (entry) {
            if(jQuery.inArray(entry.name,names) != '-1') {
                entry.value = tinyMCE.editors[entry.name].getContent();
            }
            finalFormSettingsData.push(entry);
        })

        var general_data = {
            action: "gdfrm_save_form_settings",
            nonce: gdform.saveSettingsNonce,
            form_id: id,
            form_name: name,
            formSettingsData:finalFormSettingsData
        };

        jQuery(this).prepend('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');

        jQuery.post(ajaxurl, general_data, function (response) {
            if (response.success) {
                jQuery('#save-form-button').find('.fa-spinner').remove();
            } else {
                alert('not done');
            }
        }, "json");

        formSubmitting = true;

        return false;
    });

    /* open right col */
    jQuery('.gdfrm_edit_form_settings_container').on("click",".gdicon-setting", function () {
         var settingDivID = jQuery(this).attr('rel');
             jQuery('.right-col>div').hide();
             jQuery('#'+settingDivID).toggle();

        jQuery('.left-col').animate({
            left: '0',
            } , 200 ,function () {
                jQuery('.right-col').animate({
                    right: '0',
                }, 200 );
        } );
    });

    /* close rightcol */
    jQuery('.gdfrm_edit_form_settings_container').on("click",".hide-rightcol", function () {
        jQuery('.right-col').animate({
            right: '-765px',
        } , 200 ,function () {
            jQuery('.left-col').animate({
                left: '25%',
            }, 200 );
        } );
    });


    jQuery('select[name=action-onsubmit]').on('change',function () {
        jQuery('#action-onsubmit-settings .action-onsubmit').hide();
        jQuery('#action-onsubmit-settings div[rel=action-'+this.value+']').show();
    })

    jQuery(document).on('change','input,select,textarea',function () {
        formSubmitting = false;
    })


    window.onload = function() {
        window.addEventListener("beforeunload", function (e) {
            if (formSubmitting) {
                return undefined;
            }

            var confirmationMessage = 'It looks like you have been editing something. '
                + 'If you leave before saving, your changes will be lost.';

            (e || window.event).returnValue = confirmationMessage; //Gecko + IE
            return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
        });
    };

})
