<?php

namespace GDForm\Controllers\Admin;

use GDForm\Models\Fields\Field;
use GDForm\Models\Fields\FieldOption;
use GDForm\Models\Form;
use GDForm\Models\Submission;

/**
 * Class AjaxController
 * @package GDForm\Controllers\Admin
 */
class AjaxController
{

    public static function init()
    {
        add_action('wp_ajax_gdfrm_save_form', array(__CLASS__, 'saveForm'));

        add_action('wp_ajax_gdfrm_remove_form', array(__CLASS__, 'removeForm'));

        add_action('wp_ajax_gdfrm_save_form_settings', array(__CLASS__, 'saveFormSettings'));

        add_action('wp_ajax_gdfrm_save_field', array(__CLASS__, 'saveField'));

        add_action('wp_ajax_gdfrm_remove_field', array(__CLASS__, 'removeField'));

        add_action('wp_ajax_gdfrm_add_field_option', array(__CLASS__, 'addFieldOption'));

        add_action('wp_ajax_gdfrm_remove_field_option', array(__CLASS__, 'removeFieldOption'));

        add_action('wp_ajax_gdfrm_import_options', array(__CLASS__, 'importOptions'));

        add_action('wp_ajax_gdfrm_duplicate_field', array(__CLASS__, 'duplicateField'));

        add_action('wp_ajax_gdfrm_save_settings', array(__CLASS__, 'savePluginSettings'));

        add_action('wp_ajax_gdfrm_remove_submission', array(__CLASS__, 'removeSubmission'));

        add_action('wp_ajax_gdfrm_read_submission', array(__CLASS__, 'readSubmission'));
    }


    public static function saveForm()
    {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'gdfrm_save_form')) {
            die('security check failed');
        }

        $form_name = $_REQUEST['form_name'];
        $form_id = absint($_REQUEST['form_id']);

        $form_data = $_REQUEST['formData'];

        $fields_settings = array();

        foreach ($form_data as $input) {
            $name = $input['name'];
            $value = $input['value'];

            if (isset($fields_settings[$name])) {
                $fields_settings[$name] .= ',' . $value;
            } else {
                $fields_settings[$name] = $value;
            }
        }


        $form = new Form(array('Id' => $form_id));

        $form_fields = $form->getFields();

        foreach ($form_fields as $field) {

            if($field){
                $field_id = $field->getId();

                $field->setProperties($fields_settings, $field_id);

                $field->save();
            }
        }

        $form->setName($form_name);

        $saved = $form->save();

        if ($saved) {
            echo json_encode(array("success" => 1));
            die();
        } else {
            die('something went wrong');
        }

    }

    public static function removeForm()
    {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'gdfrm_remove_form')) {
            die('security check failed');
        }

        $id = $_REQUEST['id'];

        if (absint($id) != $id) {
            die('Trying to delete  a wrong form');
        }

        $form_removed = Form::delete($id);

        if ($form_removed) {
            echo json_encode(array("success" => 1));
            die();
        } else {
            die('something went wrong');
        }
    }

    /* update form settings */
    public static function saveFormSettings()
    {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'gdfrm_save_form_settings')) {
            die('security check failed');
        }
        $form_id = $_REQUEST['form_id'];

        $form_settings_data = $_REQUEST['formSettingsData'];

        $settings_array = array();

        foreach ($form_settings_data as $setting) {
            $name = $setting['name'];
            $value = $setting['value'];
            $settings_array[$name] = $value;
        }

        $form = new Form(array('Id' => $form_id));

        $form -> setDisplayTitle($settings_array['display-title'])
              -> setAdminEmail($settings_array['admin-email'])
              -> setAdminSubject($settings_array['admin-subject'])
              -> setAdminMessage($settings_array['admin-message'])
              -> setUserSubject($settings_array['user-subject'])
              -> setUserMessage($settings_array['user-message'])
              -> setEmailUsers($settings_array['email-users'])
              -> setEmailAdmin($settings_array['email-admin'])
              -> setSaveSubmissions($settings_array['save-submissions'])
              -> setTheme($settings_array['theme'])
              -> setFromName($settings_array['email-from-name'])
              -> setFromEmail($settings_array['email-from-address'])
              -> setActionOnsubmit($settings_array['action-onsubmit'])
              -> setSuccessMessage($settings_array['success-message'])
              -> setHideFormOnsubmit($settings_array['hide-form'])
              -> setRedirectUrl($settings_array['redirect-url'])
              -> setLabelsPosition($settings_array['labels-position'])
              -> setEmailFormatError($settings_array['email-format-error'])
              -> setRequiredEmptyError($settings_array['required-field-error'])
              -> setUploadSizeError($settings_array['upload-size-error'])
              -> setUploadFormatError($settings_array['upload-format-error']);


        $saved = $form->save();

        if ($saved) {
            echo json_encode(array("success" => 1));
            die();
        } else {
            die('something went wrong');
        }

    }

    /**
     * Save field
     */
    public static function saveField()
    {

        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'gdfrm_save_field')) {
            wp_die(__('Security check failed', GDFRM_TEXT_DOMAIN));
        }

        if (!isset($_REQUEST['form'])) {
            wp_die(__('missing "form" parameter', GDFRM_TEXT_DOMAIN));
        }

        $form = $_REQUEST['form'];

        $type = $_REQUEST['type'];

        $order = $_REQUEST['order'];

        $type_name = $_REQUEST['type_name'];

        $field_class = 'GDForm\Models\Fields\\' . ucfirst($type_name);

        /** @var Field $field */
        $field = new $field_class();

        try {
            $field ->setTypeId($type)
                   ->setOrdering($order)
                   ->setForm($form);
        } catch (\Exception $e) {
            die($e->getMessage());
        }

        if($field->getType()->getIsFree()){
            $saved = $field->save();

            if($field->getType()->getName()=='submit'){
                $form = $field->getFormObject();
                $form->setSubmitNoticeShown(1);
                $form->save();
            }
        }

        if (isset($saved) && $saved) {
            echo json_encode(array(
                "success" => 1,
                'last_id' => $saved,
                'settingsBlock' => $field->settingsBlock(),
                'fieldBlock' => $field->fieldBlock(),
            ));
            die();

        } else {
            wp_die('something went wrong');
        }

    }

    public static function removeField()
    {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'gdfrm_remove_field')) {
            wp_die(__('Security check failed', GDFRM_TEXT_DOMAIN));
        }

        if (!isset($_REQUEST['id'])) {
            wp_die(__('missing "id" parameter', GDFRM_TEXT_DOMAIN));
        }

        $id = $_REQUEST['id'];

        if (absint($id) != $id) {
            wp_die(__('"id" parameter must be non negative integer', GDFRM_TEXT_DOMAIN));
        }

        $field_removed = Field::delete($id);

        if ($field_removed) {

            echo json_encode(array(
                "success" => 1,
            ));
            die();

        } else {
            wp_die('something went wrong');
        }

    }

    public static function addFieldOption()
    {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'gdfrm_add_field_option')) {
            wp_die(__('Security check failed', GDFRM_TEXT_DOMAIN));
        }

        if (!isset($_REQUEST['field'])) {
            wp_die(__('missing "field" parameter', GDFRM_TEXT_DOMAIN));
        }

        $field = $_REQUEST['field'];

        if (absint($field) != $field) {
            wp_die(__('"field" parameter must be non negative integer', GDFRM_TEXT_DOMAIN));
        }

        $option = new FieldOption();

        $new_option = $option->setField($field);

        if (isset($_REQUEST['attachment'])) {

            $attachment = $_REQUEST['attachment'];
            $new_option->setName($attachment['name'])
                ->setValue($attachment['name'])
                ->setImage($attachment['url']);

        }

        $new_option_id = $new_option->save();

        if ($new_option_id) {

            echo json_encode(array(
                "success" => 1,
                "option" => $new_option_id,
                'option_row' => $new_option->optionRow()
            ));
            die();

        } else {
            wp_die('something went wrong');
        }

    }

    public static function removeFieldOption()
    {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'gdfrm_remove_field_option')) {
            wp_die(__('Security check failed', GDFRM_TEXT_DOMAIN));
        }

        if (!isset($_REQUEST['option'])) {
            wp_die(__('missing "option" parameter', GDFRM_TEXT_DOMAIN));
        }

        $option = $_REQUEST['option'];

        if (absint($option) != $option) {
            wp_die(__('"option" parameter must be non negative integer', GDFRM_TEXT_DOMAIN));
        }

        $option_removed = FieldOption::delete($option);

        if ($option_removed) {
            echo json_encode(array(
                "success" => 1,
            ));
            die();

        } else {
            wp_die('something went wrong');
        }


    }

    public static function importOptions()
    {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'gdfrm_import_options')) {
            wp_die(__('Security check failed', GDFRM_TEXT_DOMAIN));
        }

        if (!isset($_REQUEST['field'])) {
            wp_die(__('missing "field" parameter', GDFRM_TEXT_DOMAIN));
        }

        $field = absint($_REQUEST['field']);

        if (absint($field) != $field) {
            wp_die(__('"field" parameter must be non negative integer', GDFRM_TEXT_DOMAIN));
        }

        $options = explode(',', $_REQUEST['options']);

        $options_html = '';

        foreach ($options as $option) {
            $option_array = explode('#', $option);
            $name = str_replace('{', '', $option_array[0]);
            $value = str_replace('}', '', $option_array[1]);

            $option_object = new FieldOption();

            $option_object
                ->setField($field)
                ->setName($name)
                ->setValue($value);
            $new_option_id = $option_object->save();

            if ($new_option_id) {

                $options_html .= $option_object->optionRow();

            } else {
                wp_die('something went wrong during import, check the syntax');
            }

        }

        echo json_encode(array(
            "success" => 1,
            "options_rows" => $options_html,
        ));
        die();
    }

    public function duplicateField()
    {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'gdfrm_duplicate_field')) {
            wp_die(__('Security check failed', GDFRM_TEXT_DOMAIN));
        }

        if (!isset($_REQUEST['id'])) {
            wp_die(__('missing "id" parameter', GDFRM_TEXT_DOMAIN));
        }

        $id = $_REQUEST['id'];

        if (absint($id) != $id) {
            wp_die(__('"id" parameter must be non negative integer', GDFRM_TEXT_DOMAIN));
        }

        $field = Field::get(array('Id'=>$id));

        $form = $_REQUEST['form'];

        if (absint($form) != $form) {
            wp_die(__('You are trying to edit a wrong form', GDFRM_TEXT_DOMAIN));
        }


        $field
            ->unsetId()
            ->setForm($form);

        $new_field_id = $field->save();

        if ($new_field_id) {

            echo json_encode(array(
                "success" => 1,
                "field" => $new_field_id,
                'settingsBlock' => $field->settingsBlock(),
                'fieldBlock' => $field->fieldBlock(),
            ));
            die();

        } else {
            wp_die('something went wrong');
        }

    }

    public static function savePluginSettings()
    {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'gdfrm_save_settings')) {
            die('security check failed');
        }

        $settings_data = $_REQUEST['formData'];

        $saved = array();

        foreach ($settings_data as $input) {
            if(\GDForm()->Settings->get($input['name']) !== $input['value']){
                $saved[] = \GDForm()->Settings->set($input['name'], $input['value']);
            }else{
                $saved[] = true;
            }
        }

        $filteredSaved = array_filter($saved);

        if (!empty($filteredSaved)) {
            echo json_encode(array("success" => 1));
            die();
        } else {
            die('something went wrong');
        }

    }

    public static function removeSubmission()
    {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'gdfrm_remove_submission')) {
            wp_die(__('Security check failed', GDFRM_TEXT_DOMAIN));
        }

        if (!isset($_REQUEST['id'])) {
            wp_die(__('missing "id" parameter', GDFRM_TEXT_DOMAIN));
        }

        $id = $_REQUEST['id'];

        if (absint($id) != $id) {
            wp_die(__('"id" parameter must be non negative integer', GDFRM_TEXT_DOMAIN));
        }

        $submission_removed = Submission::delete($id);

        if ($submission_removed) {

            echo json_encode(array(
                "success" => 1,
            ));
            die();

        } else {
            wp_die('something went wrong');
        }

    }

    /* mark submission as read */
    public static function readSubmission()
    {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'gdfrm_read_submission')) {
            wp_die(__('Security check failed', GDFRM_TEXT_DOMAIN));
        }

        if (!isset($_REQUEST['id'])) {
            wp_die(__('missing "id" parameter', GDFRM_TEXT_DOMAIN));
        }

        $id = $_REQUEST['id'];

        if (absint($id) != $id) {
            wp_die(__('"id" parameter must be non negative integer', GDFRM_TEXT_DOMAIN));
        }

        $submission = new Submission(array('Id'=>$id));
        $submission->setViewed(1);
        $submission_read = $submission->save();

        if ($submission_read) {

            echo json_encode(array(
                "success" => 1,
            ));
            die();

        } else {
            wp_die('something went wrong');
        }

    }

}