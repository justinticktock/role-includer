<?php
/*
Plugin Name: Role Includer
Plugin URI: http://justinandco.com/plugins/role-includer
Description: Allows users to be allocated multiple roles.
Version: 1.4
Author: Justin Fletcher
Author URI: http://justinandco.com
Text Domain: role-includer
Domain Path: /languages/
License: GPLv2 or later
*/


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * role-includer class.
 */
class ROLE_INCLUDER {

	// Refers to a single instance of this class.
    private static $instance = null;

    public	 $plugin_full_path;
	public   $plugin_file = 'role-includer/role-includer.php';

	// Settings page slug
    public	 $menu = 'role-includer-settings';

	// Settings Admin Menu Title
    public	 $menu_title = 'Role Includer';

	// Settings Page Title
    public	 $page_title = 'Role Includer';

    /**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Load the textdomain.
		add_action( 'plugins_loaded', array( $this, 'i18n' ), 1 );

		// Set the constants needed by the plugin.
		add_action( 'plugins_loaded', array( $this, 'constants' ), 2 );

		// Load the functions files.
		add_action( 'plugins_loaded', array( $this, 'includes' ), 3 );

		// Attached to after_setup_theme. Loads the plugin installer CLASS after themes are set-up to stop duplication of the CLASS.
		// this should remain the hook until TGM-Plugin-Activation version 2.4.0 has had time to roll out to the majority of themes and plugins.
		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ));

		// register admin side - upgrade routine and menu item.
		add_action( 'admin_init', array( $this, 'admin_init' ));

		// Load admin error messages
		add_action( 'admin_init', array( $this, 'deactivation_notice' ));
		add_action( 'admin_notices', array( $this, 'action_admin_notices' ));

	}

	/**
	 * Defines constants used by the plugin.
	 *
	 * @return void
	 */
	public function constants() {

		// Define constants
		define( 'ROLE_INCLUDER_MYPLUGINNAME_PATH', plugin_dir_path( __FILE__ ) );
		define( 'ROLE_INCLUDER_MYPLUGINNAME_FULL_PATH', ROLE_INCLUDER_MYPLUGINNAME_PATH . 'role-includer.php' );
		define( 'ROLE_INCLUDER_PLUGIN_DIR', trailingslashit( plugin_dir_path( ROLE_INCLUDER_MYPLUGINNAME_PATH )));
		define( 'ROLE_INCLUDER_PLUGIN_URI', plugins_url('', __FILE__) );

		// admin prompt constants
		define( 'ROLE_INCLUDER_PROMPT_DELAY_IN_DAYS', 30);
		define( 'ROLE_INCLUDER_PROMPT_ARGUMENT', 'ROLE_INCLUDER_hide_notice');

	}

	/**
	 * Loads the initial files needed by the plugin.
	 *
	 * @return void
	 */
	public function includes() {

		// settings
		require_once( ROLE_INCLUDER_MYPLUGINNAME_PATH . 'includes/settings.php' );

		// include the role
		require_once( ROLE_INCLUDER_MYPLUGINNAME_PATH . 'includes/class-includer.php' );
	}

	/**
	 * Initialise the plugin installs
	 *
	 * @return void
	 */
	public function after_setup_theme() {

		// install the plugins and force activation if they are selected within the plugin settings
		require_once( ROLE_INCLUDER_MYPLUGINNAME_PATH . 'includes/plugin-install.php' );

	}


        /**
	 * Initialise the plugin menu.
	 *
	 * @return void
	 */
	public function admin_menu() {

	}

	/**
	 * sub_menu_page:
	 *
	 * @return void
	 */
	public function sub_menu_page() {
		//
	}

	/**
	 * Initialise the plugin by handling upgrades and loading the text domain.
	 *
	 * @return void
	 */
	public function admin_init() {

		//Registers user installation date/time on first use
		$this->action_init_store_user_meta();

		$plugin_current_version = get_option( 'role_includer_plugin_version' );
		$plugin_new_version =  self::plugin_get_version();

		// Admin notice hide prompt notice catch
		$this->catch_hide_notice();

		//if ( empty($plugin_current_version) || $plugin_current_version < $plugin_new_version ) {
		if ( version_compare( $plugin_current_version, $plugin_new_version, '<' ) ) {

			$plugin_current_version = isset( $plugin_current_version ) ? $plugin_current_version : 0;

			$this->role_includer_upgrade( $plugin_current_version );

			// set default options if not already set..
			$this->do_on_activation();

			// create the plugin_version store option if not already present.
			$plugin_version = self::plugin_get_version();
			update_option('role_includer_plugin_version', $plugin_version );

			// Update the option again after role_includer_upgrade() changes and set the current plugin revision
			update_option('role_includer_plugin_version', $plugin_new_version );
		}
	}

	/**
	 * Loads the text domain.
	 *
	 * @return void
	 */
	public function i18n( ) {
		$ok = load_plugin_textdomain( 'role-includer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
	}

	/**
	 * Provides an upgrade path for older versions of the plugin
	 *
	 * @param float $current_plugin_version the local plugin version prior to an update
	 * @return void
	 */
	public function role_includer_upgrade( $current_plugin_version ) {

		/*
		// upgrade code when required.
		if ( $current_plugin_version < '1.0' ) {

			delete_option('XXXXXX');

		}
		*/
	}

	/**
	 * Flush your rewrite rules for plugin activation and initial install date.
	 *
	 * @access public
	 * @return $settings
	 */
	static function do_on_activation() {

		// Record plugin activation date.
		add_option('role_includer_install_date',  time() );

	}

	/**
	 * remove the reference site option setting for safety when re-activating the plugin
	 *
	 * @access public
	 * @return $settings
	 */
	static function do_on_deactivation() {

		//delete_option('role_includer_reference_site' );
	}

	/**
	 * Returns current plugin version.
	 *
	 * @access public
	 * @return $plugin_version
	 */
	static function plugin_get_version() {

		$plugin_data = get_plugin_data( ROLE_INCLUDER_MYPLUGINNAME_FULL_PATH, false, false );

		$plugin_version = $plugin_data['Version'];
		return filter_var($plugin_version, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	}

	/**
	 * Register Plugin Deactivation Hooks for all the currently
	 * enforced active extension plugins.
	 *
	 * @access public
	 * @return null
	 */
	public function deactivation_notice() {

		// loop plugins forced active.
		$plugins = role_includer_Settings::get_instance()->selected_plugins( 'role_includer_plugin_extension' );
		$plugins = array_filter( $plugins );
		if ( ! empty( $plugins ) ) {

			foreach ( $plugins as $plugin ) {
				$plugin_file = ROLE_INCLUDER_PLUGIN_DIR . $plugin["slug"] . '\\' . $plugin['slug'] . '.php' ;
				register_deactivation_hook( $plugin_file, array( 'role-includer', 'on_deactivation' ) );
			}
		}
	}

	/**
	 * This function is hooked into plugin deactivation for
	 * enforced active extension plugins.
	 *
	 * @access public
	 * @return null
	 */
	public static function on_deactivation()
    {
        if ( ! current_user_can( 'activate_plugins' ) )
            return;
        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        check_admin_referer( "deactivate-plugin_{$plugin}" );

		$plugin_slug = explode( "/", $plugin);
		$plugin_slug = $plugin_slug[0];
		update_option( "role_includer_deactivate_{$plugin_slug}", true );
    }

	/**
	 * Display the admin warnings.
	 *
	 * @access public
	 * @return null
	 */
	public function action_admin_notices() {

		// loop plugins forced active.
		$plugins = role_includer_Settings::get_instance()->selected_plugins( 'role_includer_plugin_extension' );

		// for each extension plugin enabled (forced active) add a error message for deactivation.
		foreach ( $plugins as $plugin ) {
			$this->action_admin_plugin_forced_active_notices( $plugin["slug"] );
		}

		// Prompt for rating
		$this->action_admin_rating_prompt_notices();
	}

	/**
	 * Display the admin error message for plugin forced active.
	 *
	 * @access public
	 * @return null
	 */
	public function action_admin_plugin_forced_active_notices( $plugin ) {

		$plugin_message = get_option("role_includer_deactivate_{$plugin}");
		if ( ! empty( $plugin_message ) ) {
			?>
			<div class="error">
				  <p><?php esc_html_e(sprintf( __( 'Error the %1$s plugin is forced active with ', 'role-includer'), $plugin)); ?>
				  <a href="users.php?page=<?php echo $this->menu ; ?>&tab=role_includer_plugin_extension"> <?php echo esc_html(__( 'Role Includer Settings!', 'role-includer')); ?> </a></p>
			</div>
			<?php
			update_option("role_includer_deactivate_{$plugin}", false);
		}
	}


	/**
	 * Store the current users start date
	 *
	 * @access public
	 * @return null
	 */
	public function action_init_store_user_meta() {

		// store the initial starting meta for a user
		add_user_meta( get_current_user_id(), 'role_includer_start_date', time(), true );
		add_user_meta( get_current_user_id(), 'role_includer_prompt_timeout', time() + 60*60*24*  ROLE_INCLUDER_PROMPT_DELAY_IN_DAYS, true );

	}

	/**
	 * Display the admin message for plugin rating prompt.
	 *
	 * @access public
	 * @return null
	 */
	public function action_admin_rating_prompt_notices( ) {

		$user_responses =  array_filter( (array)get_user_meta( get_current_user_id(), ROLE_INCLUDER_PROMPT_ARGUMENT, true ));
		if ( in_array(  "done_now", $user_responses ) )
			return;

		if ( current_user_can( 'install_plugins' ) ) {

			$next_prompt_time = get_user_meta( get_current_user_id(), 'role_includer_prompt_timeout', true );
			if ( ( time() > $next_prompt_time )) {
				$plugin_user_start_date = get_user_meta( get_current_user_id(), 'role_includer_start_date', true );
				?>
				<div class="update-nag">

					<p><?php esc_html(printf( __("You've been using <b>Role Includer</b> for more than %s.  How about giving it a review by logging in at wordpress.org ?", 'role-includer'), human_time_diff( $plugin_user_start_date) )); ?>

					</p>
					<p>

						<?php echo '<a href="' .  esc_url(add_query_arg( array( ROLE_INCLUDER_PROMPT_ARGUMENT => 'doing_now' )))  . '">' .  esc_html__( 'Yes, please take me there.', 'role-includer' ) . '</a> '; ?>

						| <?php echo ' <a href="' .  esc_url(add_query_arg( array( ROLE_INCLUDER_PROMPT_ARGUMENT => 'not_now' )))  . '">' .  esc_html__( 'Not right now thanks.', 'role-includer' ) . '</a> ';?>

						<?php
						if ( in_array(  "not_now", $user_responses ) || in_array(  "doing_now", $user_responses )) {
							echo '| <a href="' .  esc_url(add_query_arg( array( ROLE_INCLUDER_PROMPT_ARGUMENT => 'done_now' )))  . '">' .  esc_html__( "I've already done this !", 'role-includer' ) . '</a> ';
						}?>

					</p>
				</div>
				<?php
			}
		}
	}

	/**
	 * Store the user selection from the rate the plugin prompt.
	 *
	 * @access public
	 * @return null
	 */
	public function catch_hide_notice() {

		if ( isset($_GET[ROLE_INCLUDER_PROMPT_ARGUMENT]) && $_GET[ROLE_INCLUDER_PROMPT_ARGUMENT] && current_user_can( 'install_plugins' )) {

			$user_user_hide_message = array( sanitize_key( $_GET[ROLE_INCLUDER_PROMPT_ARGUMENT] )) ;
			$user_responses =  array_filter( (array)get_user_meta( get_current_user_id(), ROLE_INCLUDER_PROMPT_ARGUMENT, true ));

			if ( ! empty( $user_responses )) {
				$response = array_unique( array_merge( $user_user_hide_message, $user_responses ));
			} else {
				$response =  $user_user_hide_message;
			}

			check_admin_referer();
			update_user_meta( get_current_user_id(), ROLE_INCLUDER_PROMPT_ARGUMENT, $response );

			if ( in_array( "doing_now", (array_values((array)$user_user_hide_message ))))  {
				$next_prompt_time = time() + ( 60*60*24*  ROLE_INCLUDER_PROMPT_DELAY_IN_DAYS ) ;
				update_user_meta( get_current_user_id(), 'role_includer_prompt_timeout' , $next_prompt_time );
				wp_redirect( 'http://wordpress.org/support/view/plugin-reviews/user-upgrade-capability' );
				exit;
			}

			if ( in_array( "not_now", (array_values((array)$user_user_hide_message ))))  {
				$next_prompt_time = time() + ( 60*60*24*  ROLE_INCLUDER_PROMPT_DELAY_IN_DAYS ) ;
				update_user_meta( get_current_user_id(), 'role_includer_prompt_timeout' , $next_prompt_time );
			}


			wp_redirect( remove_query_arg( ROLE_INCLUDER_PROMPT_ARGUMENT ) );
			exit;
		}
	}


	/**
     * Creates or returns an instance of this class.
     *
     * @return   A single instance of this class.
     */
    public static function get_instance() {

        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;

    }
}

/**
 * Init ROLE_INCLUDER class
 */

ROLE_INCLUDER::get_instance();

register_deactivation_hook( __FILE__, array( 'role-includer', 'do_on_deactivation' ) );

?>