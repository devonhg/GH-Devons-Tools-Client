<?php
if ( ! defined( 'WPINC' ) ) { die; }
/*
 * Plugin Name:       Devons Tools - Webmaster
 * Plugin URI:        
 * Description:       This is a really simple plugin that adds a "client" role to a wordpress website as well as a webmaster user. Useful if you have mutliple clients on different installations and need to be able to set up basic client functionality fast.
 * Version:           v1.0.2
 * Author:            Devon Godfrey
 * Author URI:        http://dhgodfrey.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
*/

//Overall functions
    //Check if a role exists
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


class WBMST_core{

    //Construct, handles actions and hooks. 
    public function __construct(){
        add_action('admin_enqueue_scripts' , array( $this, "admin_css" ) ); 
        add_action('admin_head' , array( $this , 'admin_f' ) );
        register_activation_hook( __FILE__, array( $this , 'a_hook' ) );
        register_uninstallation_hook( __FILE__, array( 'WBMST_core' , 'WBMST_u_hook') );
    }

    //Functions
        //Convert clients to admins.
        private function client_to_admin(){
            $usr_q = new WP_User_Query( array( 'role' => 'Client' ) );
            if ( ! empty( $usr_q->results ) ) {
                foreach( $usr_q->results as $u ){
                    $user = new WP_User( $u );
                    $user->remove_role ( 'Client' );
                    $user->add_role( 'administrator' ); 
                }
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
                add_action( 'admin_notices', array( $this, 'WBMST_msg' ) ); 
            }    

            //Check if webmaster password is still password, and nag to change if it is. 
            if ( in_array( "administrator", (array) $dt_usr->roles ) ) {
                $wm = get_user_by('login' , 'webmaster');
                $wmo = new WP_User( $wm->ID );
                if ( wp_check_password( 'password', $wmo->user_pass , $wm->ID ) ){
                    add_action('admin_notices', array( $this , 'WBMST_nag'));
                }
            }

            //If there are no administrators on the site, all clients are elevated to administrators. 
            if ( !WBMST_role_exists( 'administrator' ) && WBMST_role_exists( 'Client' )){
                $this->client_to_admin();               
            }
        }

        //Activation hook, sets up role and webmaster. 
        public function a_hook(){
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
                $wm = new WP_User ( wp_create_user( 'webmaster' , 'password' , 'devon@zodiacgraphics.biz'  ) );
                $wm->remove_role( 'subscriber' );
                $wm->add_role( 'administrator' );
            }
        }

        //Removal function, ran when the plugin is uninstalled. 
        public static function WBMST_u_hook(){
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
            if ( WBMST_users_roles( 'Client' ) ){
                $usr_q = new WP_User_Query( array( 'role' => 'Client' ) );
                if ( ! empty( $usr_q->results ) ) {
                    foreach( $usr_q->results as $u ){
                        $user = new WP_User( $u );
                        $user->remove_role ( 'Client' );
                        $user->add_role( 'administrator' ); 
                    }
                }  
            }

            if ( WBMST_role_exists('Client') ){
                $wp_roles = new WP_Roles();
                $wp_roles->remove_role( 'Client' );
            }
        }

        //The header message for the webmaster
        public function WBMST_msg(){
            $class = "updated";
            $message = "You are currently logged in as Webmaster. Uninstalling 'Devons Tools - Webmaster' will not remove 'webmaster' user.";
            echo"<div class=\"$class\"> <p>$message</p></div>";     
        }

        //Header warning for admin if password for webmaster is default. 
        public function WBMST_nag(){
            $class = "error";
            $message = "Warning! The webmaster account still has the default password! Change it immediately!";
            echo"<div class=\"$class\"> <h3>$message</h3></div>";     
        }
}

new WBMST_core;