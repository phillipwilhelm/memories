<?php
/*
 * Plugin Name: Memories
 * Plugin URI: http://wordpress.org/plugins/memories/
 * Description: Rediscover the post(s) you published years ago
 * Author: Fikri Rasyid
 * Version: 0.1
 * Author URI: fikrirasyid.com/wordpress-plugins/memories/
 * License: GPL2+
 * Text Domain: memories
 */

define( 'MEMORIES__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

class Memories_Setup{
	var $templates;
	var $memories;

	function __construct(){
		$this->requiring_files();

		$this->templates = new Memories_Templates;
		$this->memories = new Memories;

		add_action( 'daily_memories', array( $this, 'daily_email' ) );

		register_activation_hook( __FILE__, array( $this, 'activation' ) );

		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
	}

	/**
	 * Requiring external files
	 * 
	 * @return void
	 */
	function requiring_files(){
		require_once( MEMORIES__PLUGIN_DIR . 'class-memories.php' );
		require_once( MEMORIES__PLUGIN_DIR . 'class-memories-templates.php' );
		require_once( MEMORIES__PLUGIN_DIR . 'class-memories-dashboard.php' );
	}

	/**
	 * Activation task. Do this when the plugin is activated
	 * 
	 * @return void
	 */
	function activation(){
		if( !wp_next_scheduled( 'daily_memories' ) ){
			// Register the schedule a minute after now, so user can feel the result rightaway
			$a_minute_after_now = current_time( 'timestamp', wp_timezone_override_offset() ) + 60;
			
			wp_schedule_event( $a_minute_after_now, 'daily', 'daily_memories' );
		}
	}

	/**
	 * Deactivation task. Do this when the plugin is deactivated
	 * 
	 * @return void
	 */
	function deactivation(){
		wp_clear_scheduled_hook( 'daily_memories' );
	}

	/**
	 * Sending email to administrator
	 * 
	 * @return void
	 */
	function daily_email(){
		$to 		= get_option( 'admin_email' );

		$subject 	= __( 'Your post today in history', 'memories' );

		ob_start();
			
			// Get today's posts
			$today_posts = $this->memories->get_today_posts();

			// Display today's posts content
			$this->templates->today_posts( $today_posts );

		$message = ob_get_clean();

		add_filter( 'wp_mail_content_type', array( $this, 'html_content_type' ) );

		$sent = wp_mail( $to, $subject, $message );

		remove_filter( 'wp_mail_content_type', array( $this, 'html_content_type' ) );

		var_dump( $sent );
		echo 'sent bro';
		die();
	}

	/**
	 * Set HTML content type
	 * 
	 * @return string
	 */
	function html_content_type(){
		return 'text/html';
	}
}
new Memories_Setup;