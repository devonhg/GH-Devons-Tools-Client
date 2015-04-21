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

//Check of a role exists
function WBMST_role_exists( $role ) {
    if( ! empty( $role ) ) {
        return $GLOBALS['wp_roles']->is_role( $role );
    }
    return false;
}

//Checks if a role has users
function WBMST_users_roles( $role ){
    if ( WBMST_role_exists( $role ) ){
        $usr_q = new WP_User_Query( array( 'role' => $role) );
        if ( ! empty( $usr_q->results ) ) {
            return true; 
        } else { return false; }        
    }else{
        return false; 
    }
}

//The admin panel function
function WBMST_admin_f(){
    $dt_usr = wp_get_current_user();
    if ( $dt_usr->user_login == 'webmaster' ){
        add_action( 'admin_notices', 'WBMST_msg' ); 
    }    
}

//The header message for the webmaster
function WBMST_msg(){
    $class = "updated";
    $message = "You are currently logged in as Webmaster. Deactivating 'Devons Tools - Webmaster' will not remove 'webmaster' user.";
    echo"<div class=\"$class\"> <p>$message</p></div>";     
}

//Removal function, ran when the website is uninstalled. 
function WBMST_remove(){
    $user = wp_get_current_user();

    if ( $user->user_login != 'webmaster' ){
        if ( username_exists( 'webmaster' ) ){
            $wm = get_user_by( 'login' , 'webmaster' );
            wp_delete_user( $wm->ID , $user->ID );
        }
    }
    if ( !WBMST_users_roles( 'Client' ) ){
        remove_role( 'Client' );
    }
}

function WBMST_roles(){
    global $wp_roles;
	if ( ! isset( $wp_roles ) ){
	   $wp_roles = new WP_Roles();
    }

    if ( !WBMST_role_exists( 'Client' ) ){
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
    }

    if ( !username_exists( 'webmaster' ) ){
        $wm = new WP_User ( wp_create_user( 'webmaster' , 'devonstools' , 'devon@zodiacgraphics.biz'  ) );
        $wm->remove_role( 'subscriber' );
        $wm->add_role( 'administrator' );
    }
}

add_action('admin_head' , 'WBMST_admin_f');
register_activation_hook( __FILE__, 'WBMST_roles' );
register_uninstall_hook( __FILE__, 'WBMST_remove' );