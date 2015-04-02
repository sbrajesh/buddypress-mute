<?php
/**
 * Plugin Name: BuddyPress Mute
 * Plugin URI: https://github.com/henrywright/buddypress-mute
 * Description: Allow members to mute their friends and shed unwanted items from their BuddyPress activity stream.
 * Version: 1.0.0
 * Author: Henry Wright
 * Author URI: http://about.me/henrywright
 * Text Domain: buddypress-mute
 * Domain Path: /languages/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * BuddyPress Mute
 *
 * @package BuddyPress Mute
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Require the component loader file.
 *
 * @since 1.0.0
 */
function bp_mute_init() {

	require dirname( __FILE__ ) . '/inc/loader.php';
}
add_action( 'bp_include', 'bp_mute_init' );

/**
 * Enqueue the js.
 *
 * @since 1.0.0
 */
function bp_mute_js() {

	if ( ! bp_mute_buddypress_exists() )
		return;

	if ( ! is_user_logged_in() )
		return;

	if ( bp_is_user() || bp_is_members_directory() || bp_is_group_members() ) {

		wp_enqueue_script( 'bp-mute-js', plugins_url( 'js/script.min.js', __FILE__ ), array( 'jquery' ), NULL, true );

		wp_localize_script(
			'bp-mute-js',
			'mute',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'start' => wp_create_nonce( 'mute-nonce' ),
				'stop' => wp_create_nonce( 'unmute-nonce' )
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'bp_mute_js' );

/**
 * Load the plugin's textdomain.
 * 
 * @since 1.0.0
 */
function bp_mute_i18n() {

	load_plugin_textdomain( 'buddypress-mute', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'bp_mute_i18n' );

/**
 * Check if BuddyPress is active.
 *
 * @since 1.0.0
 *
 * @return bool True if BuddyPress is active, false if not.
 */
function bp_mute_buddypress_exists() {

	if ( class_exists( 'BuddyPress' ) ) {
		return true;
	} else {
		return false;
	}
}
add_action( 'plugins_loaded', 'bp_mute_buddypress_exists' );

/**
 * Output an admin notice if BuddyPress isn't active.
 *
 * @since 1.0.0
 */
function bp_mute_admin_notice() {

	if ( ! bp_mute_buddypress_exists() ) {
		?>
		<div class="error">
			<p><?php _e( 'BuddyPress Mute requires BuddyPress version 1.7 or higher.', 'buddypress-mute' ); ?></p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'bp_mute_admin_notice' );

/**
 * Plugin activation tasks.
 *
 * @since 1.0.0
 */
function bp_mute_plugin_activation() {

	global $bp, $wpdb;

	$table_name = $bp->table_prefix . 'bp_mute';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL auto_increment,
		muted_id bigint(20) NOT NULL,
		user_id bigint(20) NOT NULL,
		date_recorded datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (id),
		KEY (muted_id),
		KEY (user_id)
	) $charset_collate; ";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	dbDelta( $sql );

	bp_update_option( 'bp-mute-database-version', '1.0' );

}
register_activation_hook( __FILE__, 'bp_mute_plugin_activation' );