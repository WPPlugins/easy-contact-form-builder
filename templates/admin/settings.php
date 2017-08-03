<?php
/**
 * Template for GrandWP Forms Settings Page
 */
global $wpdb;
?>

<div class="wrap" id="gdfrm-settings">
    <div class="gdfrm_header">
        <i class="gdicon gdicon-logo"></i>
        <span><?php _e('Plugin Settings',GDFRM_TEXT_DOMAIN);?></span>

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
            <span id="save-form-button"><?php _e('Save');?></span>
        </div>

        <form id="grand-form">
            <div class="one-third">
                <div class="help-block" >
                    ReCaptcha is a Free Google Service, protecting your website from spam and other abuse. Set up ReCaptcha keys <a target="_blank" href="https://www.google.com/recaptcha/intro/index.html">here</a> in order to use it in your forms.
                </div>
                <div class="setting-block">
                    <div class="setting-block-title">
                        <img src="<?php echo GDFRM_IMAGES_URL.'icons/recaptcha-logo.png';?>">
                        <?php _e('Regular Recaptcha',GDFRM_TEXT_DOMAIN);?>
                    </div>
                    <div class="setting-row">
                        <label id="recaptcha-public-key"><?php _e('Site Key',GDFRM_TEXT_DOMAIN);?></label>
                        <input type="text" value="<?php echo \GDForm()->Settings->get('RecaptchaPublicKey'); ?>" name="RecaptchaPublicKey" id="recaptcha-public-key">
                    </div>

                    <div class="setting-row">
                        <label for="recaptcha-secret-key"><?php _e('Secret Key',GDFRM_TEXT_DOMAIN);?></label>
                        <input type="text" value="<?php echo \GDForm()->Settings->get('RecaptchaSecretKey'); ?>" name="RecaptchaSecretKey" id="recaptcha-secret-key">
                    </div>
                </div>

                <div class="setting-block">
                    <div class="setting-block-title">
                        <img src="<?php echo GDFRM_IMAGES_URL.'icons/recaptcha-logo.png';?>">
                        <?php _e('Invisible ReCaptcha',GDFRM_TEXT_DOMAIN);?>
                    </div>
                    <div class="setting-row">
                        <label for="hidden-recaptcha-public-key"><?php _e('Site Key',GDFRM_TEXT_DOMAIN);?></label>
                        <input type="text" value="<?php echo \GDForm()->Settings->get('HiddenRecaptchaPublicKey'); ?>" name="HiddenRecaptchaPublicKey" id="hidden-recaptcha-public-key">
                    </div>

                    <div class="setting-row">
                        <label for="hidden-recaptcha-secret-key"><?php _e('Secret Key',GDFRM_TEXT_DOMAIN);?></label>
                        <input type="text" value="<?php echo \GDForm()->Settings->get('HiddenRecaptchaSecretKey'); ?>" name="HiddenRecaptchaSecretKey" id="hidden-recaptcha-secret-key">
                    </div>
                </div>

            </div>

            <div class="one-third">
                <div class="setting-block">
                    <div class="setting-block-title">
                        <img src="<?php echo GDFRM_IMAGES_URL.'icons/checkbox.png';?>">
                        <?php _e('Forms Per Page',GDFRM_TEXT_DOMAIN);?>
                    </div>

                    <div class="setting-row">
                        <input type="number" min="2" max="100" name="PostsPerPage" placeholder="default 25" id="posts-per-page" value="<?php echo \GDForm()->Settings->get('PostsPerPage');?>">
                    </div>
                </div>

            </div>

            <div class="one-third">
                <div class="setting-block">
                    <div class="setting-block-title">
                        <img src="<?php echo GDFRM_IMAGES_URL.'icons/uninstall.png';?>">
                        <?php _e('Uninstall',GDFRM_TEXT_DOMAIN);?>
                    </div>

                    <div class="setting-row">
                        <label class="switcher switch-checkbox" for="remove-tables-uninstall"><?php _e('Remove all data on plugin uninstall',GDFRM_TEXT_DOMAIN);?><input type="hidden" name="RemoveTablesUninstall" value="off" /><input type="checkbox"  class="switch-checkbox" <?php checked('on',\GDForm()->Settings->get('RemoveTablesUninstall'))?> name="RemoveTablesUninstall"  id="remove-tables-uninstall"><span class="switch" ></span></label>
                    </div>
                </div>

            </div>
        </form>
    </div>

</div>
<?php
?>