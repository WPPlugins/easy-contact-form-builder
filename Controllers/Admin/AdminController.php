<?php

namespace GDForm\Controllers\Admin;

use GDForm\Core\Admin\Listener;
use GDForm\Helpers\View;
use GDForm\Models\Form;
use GDForm\Models\Submission;

class AdminController
{
    use Listener;
    /**
     * Array of pages in admin
     *
     * @var array
     */
    public $Pages;

    public function __construct()
    {
        add_action( 'admin_footer', array( 'GDForm\Controllers\Admin\ShortcodeController', 'showInlinePopup' ) );

        add_action( 'media_buttons_context', array( 'GDForm\Controllers\Admin\ShortcodeController', 'showEditorMediaButton' ) );
        
        add_action( 'admin_menu', array( $this, 'adminMenu' ) , 1);

        add_action( 'admin_init', array( __CLASS__, 'deleteForm' ) , 1);

        add_action( 'admin_init', array( __CLASS__, 'duplicateForm' ) , 1);

        add_action( 'admin_init', array( __CLASS__, 'createForm' ) , 1);
        
    }

    

    /**
     * Add admin menu pages
     */
    public function adminMenu()
    {
        $this->Pages['main_page'] = add_menu_page( __( 'GrandWP Forms', GDFRM_TEXT_DOMAIN ), __( 'GrandWP Forms', GDFRM_TEXT_DOMAIN ), 'manage_options', 'gdfrm', array(
            $this,
            'mainPage'
        ),  \GDForm()->pluginUrl() . '/assets/images/forms_logo.png' );

        $this->Pages['forms'] = add_submenu_page( 'gdfrm', __( 'All Forms', GDFRM_TEXT_DOMAIN ), __( 'All Forms', GDFRM_TEXT_DOMAIN ), 'manage_options', 'gdfrm', array(
            $this,
            'mainPage'
        ) );

        $this->Pages['submissions'] = add_submenu_page( 'gdfrm', __( 'Submissions', GDFRM_TEXT_DOMAIN ), __( 'Submissions', GDFRM_TEXT_DOMAIN ).' <i style="color:red">(pro)</i>', 'manage_options', 'gdfrm_submissions', array(
            $this,
            'submissionsPage'
        ) );

        $this->Pages['settings'] = add_submenu_page( 'gdfrm', __( 'Settings', GDFRM_TEXT_DOMAIN ), __( 'Settings', GDFRM_TEXT_DOMAIN ), 'manage_options', 'gdfrm_settings', array(
            $this,
            'settingsPage'
        ) );
    }

    /**
     * Initialize main page
     */
    public function mainPage()
    {

        View::render('admin/header-banner.php');

        if ( ! isset( $_GET['task'] ) ) {

            View::render( 'admin/forms-list.php' );

        } else {

            $task = $_GET['task'];

            switch ( $task ) {
                case 'edit_form':

                    if ( ! isset( $_GET['id'] ) ) {

                        \GDForm()->Admin->printError( __( 'Missing "id" parameter.', GDFRM_TEXT_DOMAIN ) );

                    }

                    $id = absint( $_GET['id'] );

                    if ( ! $id ) {

                        \GDForm()->Admin->printError( __( '"id" parameter must be not negative integer.', GDFRM_TEXT_DOMAIN ) );

                    }
                    
                    $form = new Form( array('Id'=>$id) );

                    View::render( 'admin/edit-form.php', array( 'form' => $form  ) );

                    break;
                case 'edit_form_settings':
                    $id = $_GET['id'];

                    if( absint($id)!=$id){

                        \GDForm()->Admin->printError( __( 'Id parameter must be non negative integer.', GDFRM_TEXT_DOMAIN ) );

                    }
                    
                    $form = new Form( array('Id'=>$id) );

                    View::render( 'admin/form-settings.php', array( 'form' => $form ) );

                    break;

            }

        }

    }

   

    public function submissionsPage()
    {
        View::render('admin/submissions.php');
    }
    

    public function settingsPage()
    {

        View::render( 'admin/settings.php' );

    }


    public function printError( $error_message, $die = true )
    {

        $str = sprintf( '<div class="error"><p>%s&nbsp;<a href="#" onclick="window.history.back()">%s</a></p></div>', $error_message, __( 'Go back', GDFRM_TEXT_DOMAIN ) );

        if ( $die ) {

            wp_die( $str );

        } else {
            echo $str;
        }

    }

    public static function deleteForm()
    {
        if(!self::isRequest('gdfrm','remove_form','GET')){
            return;
        }

        if ( ! isset( $_GET['id'] ) ) {
            wp_die( __( '"id" parameter is required', GDFRM_TEXT_DOMAIN ) );
        }

        $id = $_GET['id'];

        if ( absint( $id ) != $id ) {
            wp_die( __( '"id" parameter must be non negative integer', GDFRM_TEXT_DOMAIN ) );
        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'gdfrm_remove_form_' . $id ) ) {
            wp_die( __( 'Security check failed', GDFRM_TEXT_DOMAIN ) );
        }

        Form::delete( $id );

        $location = admin_url( 'admin.php?page=gdfrm' );


        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header("Location: $location");

        exit;

    }


    public static function DuplicateForm()
    {
        if(!self::isRequest('gdfrm','duplicate_form','GET')){
            return;
        }

        if ( ! isset( $_GET['id'] ) ) {

            \GDForm()->Admin->printError( __( 'Missing "id" parameter.', GDFRM_TEXT_DOMAIN ) );

        }

        $id = absint( $_GET['id'] );

        if ( ! $id ) {

            \GDForm()->Admin->printError( __( '"id" parameter must be not negative integer.', GDFRM_TEXT_DOMAIN ) );

        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'gdfrm_duplicate_form_'.$id  ) ) {

            \GDForm()->Admin->printError( __( 'Security check failed.', GDFRM_TEXT_DOMAIN ) );

        }

        $form = new Form( array('Id'=>$id) );

        $fields = $form -> getFields();

        $form->unsetId();

        $form->setName('Copy of '.$form->getName());

        $form = $form->save();

        /**
         * after the form is created we need to redirect user to the edit page
         */
        if ( $form && is_int( $form ) ) {
            /* copy form fields to the new form */
            if( ! empty($fields) ) {
                foreach ($fields as $field){
                    $newfield = clone $field;

                    $newfield -> setForm( $form );

                    $newfield -> save();
                }
            }

            $location = admin_url( 'admin.php?page=gdfrm&task=edit_form&id=' . $form );

            $location = wp_nonce_url( $location, 'gdfrm_edit_form_' . $form );

            $location = html_entity_decode( $location );

            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            header("Location: $location");

            exit;

        } else {

            wp_die( __( 'Problems occured while creating new form.', GDFRM_TEXT_DOMAIN ) );

        }

    }

    public static function createForm()
    {
        if(!self::isRequest('gdfrm','create_new_form','GET')){
            return;
        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'gdfrm_create_new_form'  ) ) {

            \GDForm()->Admin->printError( __( 'Security check failed.', GDFRM_TEXT_DOMAIN ) );

        }

        $form = new Form( );

        $form = $form->setName('New Form')->save();

        /**
         * after the form is created we need to redirect user to the edit page
         */
        if ( $form && is_int( $form ) ) {

            $location = admin_url( 'admin.php?page=gdfrm&task=edit_form&id=' . $form );

            $location = wp_nonce_url( $location, 'gdfrm_edit_form_' . $form );

            $location = html_entity_decode( $location );

            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            header("Location: $location");

            exit;

        } else {

            wp_die( __( 'Problems occured while creating new form.', GDFRM_TEXT_DOMAIN ) );

        }

    }


}