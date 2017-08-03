<?php
/**
 * Template for edit form page
 */
global $wpdb;

if( !isset( $theme ) ){
    throw new Exception( '"theme" variable is not reachable in edit-theme template.' );
}

if( !( $theme instanceof GDForm_Theme ) ){
    throw new Exception( '"theme" variable must be instance of GDForm_Theme class.' );
}
?>
<div class="wrap gdfrm_edit_form_container">
    <div class="gdfrm_header">
        Edit Theme
        <span id="full-width-button">
            <i class="fa fa-arrows-alt" aria-hidden="true"></i>
        </span>
    </div>
    <h1>
        <input type="text" id="theme_name" value="<?php echo $theme->get_name(); ?>">

        <span id="save-form-button"><?php _e('Save');?></span>
    </h1>

    <div class="gdfrm_content">

    </div>

</div>
