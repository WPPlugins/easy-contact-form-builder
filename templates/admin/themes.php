<?php
/**
 * Template for themes list
 */
global $wpdb;

$new_theme_link = admin_url( 'admin.php?page=gdfrm_themes&task=create_new_theme' );

$new_theme_link = wp_nonce_url( $new_theme_link, 'gdfrm_create_new_theme' );

?>
<div class="wrap gdfrm_list_container ">
    <div class="gdfrm_header">
        <i class="gdicon gdicon-logo"></i>
        <span><?php _e('Grand Themes',GDFRM_TEXT_DOMAIN);?></span>

        <ul>
            <li>
                <a href="http://grandwp.com/wordpress-contact-form-builder" target="_blank"><?php _e('Go Pro',GDFRM_TEXT_DOMAIN);?></a>
            </li>
            <li>
                <a href="http://grandwp.com/grandwp-forms-user-manual" target="_blank"><?php _e('Help',GDFRM_TEXT_DOMAIN);?></a>
            </li>
        </ul>
    </div>

    <div class="gdfrm_content">

        <div class="gdfrm-list-header">
            <a href="<?php echo $new_theme_link;?>" id="gdfrm-new-form"><?php _e('New Theme',GDFRM_TEXT_DOMAIN);?></a>
            <input type="search" class="search" placeholder="Search Theme">
        </div>

    <table class="widefat striped fixed forms_table">
        <thead>
        <tr>
            <th scope="col" id="header-id" style="width:30px"><span><?php _e( 'ID', GDFRM_TEXT_DOMAIN ); ?></span></span></th>
            <th scope="col" id="header-name" style="width:85px"><span><?php _e( 'Name', GDFRM_TEXT_DOMAIN ); ?></span></th>
            <th style="width:40px"><?php _e( 'Actions', GDFRM_TEXT_DOMAIN ); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php

        $themes = \GDForm\Models\Theme::get();
        if ( !empty( $themes ) ) {
            foreach ( $themes as $theme ) {
                \GDForm\Helpers\View::render( 'admin/themes-list-single-item.php', array( 'theme'=>$theme ) );

            }
        } else {

            \GDForm\Helpers\View::render( 'admin/themes-list-no-items.php' );

        }

        ?>
        </tbody>
        <tfoot>
        <tr>
            <th scope="col" class="footer-id" style="width:30px"><span><?php _e( 'ID', GDFRM_TEXT_DOMAIN ); ?></th>
            <th scope="col" class="footer-name" style="width:85px"><span><?php _e( 'Name', GDFRM_TEXT_DOMAIN ); ?></span></th>
            <th style="width:40px"><?php _e( 'Actions', GDFRM_TEXT_DOMAIN ); ?></th>
        </tr>
        </tfoot>
    </table>
    </div>
</div>