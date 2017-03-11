<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Append new links to the Plugin admin side
add_filter( 'plugin_action_links_' . ROLE_INCLUDER::get_instance()->plugin_file , 'role_includer_plugin_action_links');

function role_includer_plugin_action_links( $links ) {

	if ( current_user_can( 'promote_users') ) {
		$role_includer = ROLE_INCLUDER::get_instance();
		
		//$settings_link = '<a href="' . admin_url( 'users.php' ) . '">' . __( 'Add User Roles', 'role-includer' ) . "</a>";
		$settings_link = '<a href="users.php?page=' . $role_includer->menu . '">' . __( 'Settings', 'role-includer' ) . "</a>";
		
		array_push( $links, $settings_link );
	}
	return $links;	
}


// Add link to the user row for multiple roles assignment
add_filter( 'user_row_actions', 'user_row' , 10, 2 );

function user_row( $actions, $user ) {

	global $pagenow, $current_user;
	
	$role_includer = ROLE_INCLUDER::get_instance();
	$re_plugin_file = pathinfo( $role_includer->plugin_file );
	$menu = $role_includer->menu;


	if ( $pagenow == 'users.php' ) {
		if ( current_user_can( 'promote_users') ) {
			$actions['role_includer'] = '<a href="' . 
										"users.php?page=" . $menu . "&object=user&amp;user_id={$user->ID}" .
										'">' . __( 'Roles', 'role-includer' ) . '</a>';
		}
	}
	
	return $actions;
}
	

// add action after the settings save hook.
add_action( 'tabbed_settings_after_update', 'role_includer_after_settings_update' );

function role_includer_after_settings_update( ) {

	flush_rewrite_rules();	
	
}


/**
 * ROLE_INCLUDER_Settings class.
 *
 * Main Class which inits the CPTs and plugin
 */
class ROLE_INCLUDER_Settings {

	// Refers to a single instance of this class.
    private static $instance = null;
	
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	private function __construct() {
	}

		
	
	/**
     * Creates or returns an instance of this class.
     *
     * @return   A single instance of this class.
     */
    public static function get_instance() {
		
		$role_includer = ROLE_INCLUDER::get_instance();
		
		$config = array(
				'default_tab_key' => 'include_multiple_roles',					// Default settings tab, opened on first settings page open.
				'menu_parent' => 'users.php',    								// menu options page slug name.( 'Null' to remove from the menu )
				'menu_access_capability' => 'promote_users',    				// menu options page access required capability
				'menu' => $role_includer->menu,    								// menu options page slug name.
				'menu_title' => $role_includer->menu_title,    					// menu options page slug name.
				'page_title' => __( 'Role Include', 'role-includer' ), 			// $role_includer->page_title,    		// menu options page title.
				);

		$settings = 	apply_filters( 'role_includer_settings', 
										array(
											'include_multiple_roles' => array(
												'access_capability' => 'promote_users',
												'title' 		=> __( 'Roles :', 'role_includer' ),
												//'description' 	=> __( 'Enable the roles for users.', 'role_includer' ),
												'form_action'   => admin_url( 'admin-post.php' ),
												'settings' 		=> array(														

																		array(
																			'name' 		=> 'role_includer_enable_roles',
																			'std' 		=> false,
																			'label' 	=> __( 'Add User Role(s)', 'role_includer' ),
																			'desc'		=> __( 'Enable Roles to give more access to the site.', 'role_includer' ),
																			'type'      => 'field_editable_roles_checkbox',
																			),					
																		),			
											),
											'role_includer_plugin_extension' => array(
													'access_capability' => 'install_plugins',
													'title' 		=> __( 'Plugin Suggestions', 'role_excluder' ),
													'description' 	=> __( 'Any of the following plugins will allow you to define new roles and capabilties for the site, only use one of these.  Selection of a plugin will prompt you through the installation and the plugin will be forced active while this is selected; deselecting will not remove the plugin, you will need to manually deactivate and un-install from the site/network.', 'role_excluder' ),					
													'settings' 		=> array(
																			array(
																				'access_capability' => 'install_plugins',
																				'name' 		=> 'role_includer_user_role_editor_plugin',
																				'std' 		=> false,
																				'label' 	=> 'User Role Editor',
																				'desc'		=> __( "This plugin gives the ability to edit users capabilities.  Once installed go to menu [users]..[User Role Editor].", 'role_includer' ),
																				'type'      => 'field_plugin_checkbox_option',
																				// the following are for tgmpa_register activation of the plugin
																				'slug'      			=> 'user-role-editor',
																				'plugin_dir'			=> ROLE_INCLUDER_PLUGIN_DIR,
																				'required'              => false,
																				'force_deactivation' 	=> false,
																				'force_activation'      => true,		
																				),
																			array(
																				'name' 		=> 'role_includer_capability_manager_enhanced_plugin',
																				'std' 		=> false,
																				'label' 	=> 'Capability Manager Enhanced',
																				'desc'		=> __( "This plugin also gives the ability to edit users capabilities.  Once installed go to menu [users]..[Capabilities].", 'role_includer' ),
																				'type'      => 'field_plugin_checkbox_option',
																				// the following are for tgmpa_register activation of the plugin
																				'slug'      			=> 'capability-manager-enhanced',
																				'plugin_dir'			=> ROLE_INCLUDER_PLUGIN_DIR,
																				'required'              => false,
																				'force_deactivation' 	=> false,
																				'force_activation'      => true,		
																				),
																			),
												)
										
											)
									);
			
        if ( null == self::$instance ) {
            self::$instance = new Tabbed_Settings( $settings, $config );
        }

        return self::$instance;
 
    } 
}

/**
 * ROLE_INCLUDER_Settings_Additional_Methods class.
 */
class ROLE_INCLUDER_Settings_Additional_Methods {


	/**
	 * @param array of arguments to pass the option name to render the form field.
	 * @access public
	 * @return void
	 */
	public function field_editable_roles_checkbox( array $args  ) {

        $redirect = urlencode( remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ) );
        $redirect = urlencode( $_SERVER['REQUEST_URI'] );
	 
		$option   = $args['option'];
		$roles = get_editable_roles( );

		if ( isset( $_GET['user_id'] ) ) {
			$user_id = $_GET['user_id'];
			$user = new WP_User( $user_id );
			$current_user_roles = (array) $user->roles;
			
			?><H2><?php

                        //Collect user name and link to profile page.  
                        $user_profile_link = sprintf(
                                    '<a href="%s">%s</a>',
                                    get_edit_user_link( $user_id ),
                                    $user->display_name
                                    );
  
			?><H2</br	><?php
			echo sprintf( __( 'Roles for %1$s :', 'role-includer' ), $user_profile_link );
			?> </H2><?php
			
			?><ul><?php 
			asort( $roles );


                        // this is necessary for the admin-post.php hook to find the element
                        ?><input type="hidden" name="action" value="<?php echo $option['name']; ?>"><?php

                        foreach( $roles as $role_key=>$_role )
                        {
                                $id = sanitize_key( $role_key );
                                $value = get_option( $option['name'] );

                                // Render the output  
                                ?> 


                                <input type='checkbox'  
                                        id="<?php echo esc_html( "user_role_{$id}" ) ; ?>" 
                                        name="<?php echo esc_html( $option['name'] ); ?>[]"
                                        value="<?php echo esc_attr( $role_key )	; ?>"<?php checked( in_array( $role_key, $current_user_roles ) ); ?>
                                >
                                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">					

                                <?php echo esc_html( $_role['name'] ); ?></label>	
                                <br/></li>
                                <?php 
                        }?></ul>

			<?php
			
		
		if ( ! empty( $option['desc'] ) )
			echo ' <p class="description">' . $option['desc'] . '</p>';			
			
		} else {  // no user_id
		
		echo '<a href="' . admin_url( 'users.php' ) . '">' . __( 'Select a users [Roles] option under their name.', 'role-includer' ) . "</a>";
		
		}
		

	}
		

		
}


// Include the Tabbed_Settings class.
if ( ! class_exists( 'Extendible_Tabbed_Settings' ) ) { 
	require_once( dirname( __FILE__ ) . '/class-tabbed-settings.php' );
}

// Create new tabbed settings object for this plugin..
// and Include additional functions that are required.
ROLE_INCLUDER_Settings::get_instance()->registerHandler( new ROLE_INCLUDER_Settings_Additional_Methods() );







		
?>