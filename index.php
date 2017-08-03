<?php

/**
Plugin Name: Easy Contact Form - Form builder - Forms
Plugin URI: http://grandwp.com/wordpress-contact-form-builder
Description: Easy to use Form Plugin for creating simple to custom complex forms.
Version: 1.0.0
Author: GrandWP
Author URI: http://grandwp.com/
License: GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: gdfrm
Domain Path: /languages
 */

if(!defined('ABSPATH')){
    exit();
}

require 'autoload.php';

require 'GDForm.php';


/**
 * Main instance of GDForm.
 *
 * Returns the main instance of GDForm to prevent the need to use globals.
 *
 * @return \GDForm\GDForm
 */

function GDForm()
{
    return \GDForm\GDForm::instance();
}

$GLOBALS['GDForm'] = GDForm();