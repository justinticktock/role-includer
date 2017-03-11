<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * RE_INCLUDER class.
 */
class RE_INCLUDER {

	// Refers to a single instance of this class.
    private static $instance = null;

	
    /**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

            // hook to save the role settings into the WP roles for user.
            add_action( 'admin_post_' . 'role_includer_enable_roles', array ( $this, 'field_editable_roles_custom_save' ) );		
            //action=role_includer_enable_roles (this is taken from the settings page ['name'] element.

	}


	//ref
	//http://codex.wordpress.org/Plugin_API/Action_Reference/admin_post_(action)
	public function field_editable_roles_custom_save( ) {
	
			
		// authenticate
		$_nonce = isset( $_POST['include_multiple_roles_nonce'] ) ? $_POST['include_multiple_roles_nonce'] : '';

		if ( ! wp_verify_nonce( $_nonce , 'include_multiple_roles' ) ) { 
		   wp_die( __( 'You do not have permission.', 'role-includer' ) );
		}
  
		$option_name = 'role_includer_enable_roles';

        if ( isset ( $_POST[ $option_name ] ) ) {
            update_option( $option_name, $_POST[ $option_name ] );
            $msg = 'updated';
        } else {
            delete_option( $option_name );
			//wp_die( $_POST[ $option_name ] );
            $msg = 'deleted';
        }

       // if ( ! isset ( $_POST['_wp_http_referer'] ) )
       //     die( 'Missing target justins xxx value.' );

        $url = add_query_arg( 'msg', $msg, urldecode( $_POST['_wp_http_referer'] ) );

        //if ( ! isset ( $_POST['_wp_http_referer'] ) )
        //    die( 'not found target _wp_http_referer' . $_POST['_wp_http_referer'] . ' value.' );
			

        //if ( ! isset ( $_POST['user_id'] ) )
        //    die( 'not found target user_id' . $_POST['user_id'] . ' value.' );


		if ( isset( $_POST['user_id'] ) ) {
			$user_id = $_POST['user_id'];
		} else {
			wp_die( __( 'user_id not found.', 'role-includer' ) );
		}
		
		if ( ! defined( 'get_editable_roles' ) ) {
			require_once( ABSPATH.'wp-admin/includes/user.php' );
		}
		
		$roles = get_editable_roles();
		$user = new WP_User( $user_id );


		$new_roles = get_option( 'role_includer_enable_roles' );

		// Get rid of any bogus roles
		$new_roles = array_intersect( $new_roles, array_keys( $roles ) );

		$roles_to_remove = array();
		$user_roles = array_intersect( array_values( $user->roles ), array_keys( $roles ) );

		if ( ! $new_roles ) {
			// If there are no roles, delete all of the user's roles
			$roles_to_remove = $user_roles;
		} else {	
			$roles_to_remove = array_diff( $user_roles, $new_roles );
		}

		foreach ( $roles_to_remove as $_role ) {
			$user->remove_role( $_role );
		}
		
		if ( $new_roles ) {
			// Make sure that we don't call $user->add_role() any more than it's necessary
			$_new_roles = array_diff( $new_roles, array_intersect( array_values( $user->roles ), array_keys( $roles ) ) );	
			foreach ( $_new_roles as $_role ) {
				$user->add_role( $_role );
			}
		
		}
	
        wp_safe_redirect( $url );
        exit;
		
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
 * Init URE_OVERRIDE class
 */
 
RE_INCLUDER::get_instance();


?>