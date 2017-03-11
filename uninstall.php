<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit ( );
}
	
if ( is_multisite( ) ) {

    $blogs = wp_list_pluck( wp_get_sites(), 'blog_id' );

    if ( $blogs ) {
        foreach( $blogs as $blog ) {
            switch_to_blog( $blog );
            role_includer_clean_database( );
        }
        restore_current_blog( );
    }
} else {
		role_includer_clean_database( );
}
		
// remove all database entries for currently active blog on uninstall.
function role_includer_clean_database( ) {
		
		delete_option( 'role_includer_plugin_version' );
		delete_option( 'role_includer_install_date' );

		// plugin specific database entries
		delete_option( 'role_includer_user_role_editor_plugin' );
		delete_option( 'role_includer_capability_manager_enhanced_plugin' );
		
		delete_option( 'role_includer_deactivate_user-role-editor' );
		delete_option( 'role_includer_deactivate_capability-manager-enhanced' );
		
		// user specific database entries
		delete_user_meta( get_current_user_id( ), 'role_includer_prompt_timeout' );
		delete_user_meta( get_current_user_id( ), 'role_includer_start_date' );
		delete_user_meta( get_current_user_id( ), 'role_includer_hide_notice' );

}

?>