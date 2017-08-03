<?php
namespace GDForm;

use GDForm\Models\Settings;
use GDForm\Models\Fields\Captcha;
use GDForm\Controllers\Admin\AdminController;
use GDForm\Controllers\Frontend\FrontendController;
use GDForm\Controllers\Admin\AdminAssetsController;
use GDForm\Controllers\Admin\AjaxController as AdminAjax;
use GDForm\Controllers\Frontend\AjaxController as FrontAjax;

if(!defined('ABSPATH')){
    exit();
}

if( !class_exists('GDForm') ):
    class GDForm
    {

        /**
         * Version of plugin
         * @var string
         */
        public $Version = '1.0.0';

        /**
         * Instance of AdminController to manage admin
         * @var AdminController instance
         */
        public $Admin;

        /**
         * Classnames of migration classes
         *
         * @var array
         */
        private $MigrationClasses;

        /**
         * @var Settings
         */
        public $Settings;

        /**
         * The single instance of the class.
         *
         * @var GDForm
         */
        protected static $_instance = null;

        /**
         * Main gdfrm Instance.
         *
         * Ensures only one instance of gdfrm is loaded or can be loaded.
         *
         * @static
         * @see gdfrm()
         * @return GDForm - Main instance.
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * gdfrm Constructor.
         */
        private function __construct() {
            $this->constants();

            $this->MigrationClasses = array(
                'GDForm\Database\Migrations\CreateFieldTypesTable',
                'GDForm\Database\Migrations\CreateThemesTable',
                'GDForm\Database\Migrations\CreateLabelPositionsTable',
                'GDForm\Database\Migrations\CreateOnsubmitActionsTable',
                'GDForm\Database\Migrations\CreateFormsTable',
                'GDForm\Database\Migrations\CreateFieldsTable',
                'GDForm\Database\Migrations\CreateFieldOptionsTable',
                'GDForm\Database\Migrations\CreateSettingsTable',
                'GDForm\Database\Migrations\CreateSubmissionsTable',
                'GDForm\Database\Migrations\CreateSubmissionFieldsTable',
                'GDForm\Database\Migrations\CreateCaptchasTable',
                'GDForm\Database\Migrations\CreateAddressFieldOptionsTable',
            );

            add_action('init', array($this, 'init'), 0);
            add_action( 'widgets_init', array( 'GDForm\Controllers\Widgets\WidgetsController', 'init' ) );
            register_uninstall_hook( __FILE__, array('GDForm\Database\Uninstall','init') );
        }

        public function constants()
        {
            define( 'GDFRM_PLUGIN_FILE', __FILE__ );
            define( 'GDFRM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
            define( 'GDFRM_VERSION', $this->Version );
            define( 'GDFRM_IMAGES_PATH', $this->pluginPath() . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR );
            define( 'GDFRM_IMAGES_URL', untrailingslashit($this->pluginUrl() ) . '/assets/images/');
            define( 'GDFRM_FONTS_URL', untrailingslashit($this->pluginUrl() ) . '/assets/fonts/');
            define( 'GDFRM_FONTS_PATH', $this->pluginPath() . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR );
            define( 'GDFRM_TEMPLATES_PATH', $this->pluginPath() . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR);
            define( 'GDFRM_TEMPLATES_URL', untrailingslashit($this->pluginUrl()) . '/templates/');
            define( 'GDFRM_TEXT_DOMAIN', 'gdfrm');
        }

        /**
         * Initialize the plugin
         */
        public function init()
        {
            $this->checkVersion();

            $this->Settings = new Settings();

            if(defined( 'DOING_AJAX' )){
                AdminAjax::init();
                FrontAjax::init();
            }

            if( is_admin() ){
                $this->Admin = new AdminController();
                AdminAssetsController::init();

            }else{
                new FrontendController();

            }

            add_action('init',array($this,'scheduleTracking'),0);

            add_action('daily_cleanup', array($this,'pluginOldDataCleanup'));

            add_filter('cron_schedules',array($this,'customCronJobRecurrence'));


        }

        private function checkVersion(){
            if (get_option('gdform_version') !== $this->Version) {
                $this->runMigrations();
                update_option('gdform_version', $this->Version);
            }
        }

        private function runMigrations(){
            if (empty($this->MigrationClasses)) {
                return;
            }

            foreach ($this->MigrationClasses as $className) {
                if (method_exists($className, 'run')) {
                    call_user_func(array($className, 'run'));
                } else {
                    throw new \Exception('Specified migration class ' . $className . ' does not have "run" method');
                }
            }
        }


        /* cron job custom schedule */
        public function scheduleTracking()
        {
            if ( ! wp_next_scheduled( 'gdform_daily' ) ) {
                wp_schedule_event( current_time( 'timestamp' ), 'gdform_daily', 'daily_cleanup' );
            }
        }

        /* cleanup old data */
        public function pluginOldDataCleanup()
        {
            Captcha::cleanOldCaptchas();
        }

        /* add daily cron schedule name */
        public function customCronJobRecurrence($schedules)
        {
            $schedules['gdform_daily'] = array(
                'display' => __( 'Daily', GDFRM_TEXT_DOMAIN ),
                'interval' => 86400,
            );
            return $schedules;
        }


        /**
         * @return string
         */
        public function viewPath()
        {
            return apply_filters('gdform_view_path', 'GDForm/');
        }

        /**
         * @return string
         */
        public function pluginPath()
        {
            return plugin_dir_path(__FILE__);
        }

        /**
         * @return string
         */
        public function pluginUrl()
        {
            return plugins_url('', __FILE__);
        }

    }
endif;
