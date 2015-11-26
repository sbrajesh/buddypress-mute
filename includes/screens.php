<?php
/**
 * Screen functions
 *
 * @package BuddyPress Mute
 * @subpackage Screens
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Catch the 'all' screen.
 *
 * @since 1.0.0
 */
function bp_mute_all_screen() {

	/**
	 * Fires before loading the 'all' screen template.
	 *
	 * @since 1.0.0
	 */
	do_action( 'bp_mute_all_screen' );
	
	bp_mute_load_template_content();
	
	bp_core_load_template( 'members/single/plugins' );
}

/**
 * Catch the 'friends' screen.
 *
 * @since 1.0.0
 */
function bp_mute_friends_screen() {

	/**
	 * Fires before loading the 'friends' screen template.
	 *
	 * @since 1.0.0
	 */
	do_action( 'bp_mute_friends_screen' );
	
	bp_mute_load_template_content();
	
	bp_core_load_template( 'members/single/plugins' );
}

/**
 * Load actual content to the plugins template
 * 
 */
function bp_mute_load_template_content() {

		add_action( 'bp_after_member_plugin_template', 'bp_mute_disable_members_loop_ajax' );

		add_action( 'bp_template_content', 'bp_mute_template_part' );

}

/**
 * Get a template directory location.
 *
 * @since 1.0.0
 *
 * @return string
 */
function bp_mute_get_template_directory() {

	return trailingslashit( trailingslashit( BP_PLUGIN_DIR ) . 'bp-templates/bp-legacy' );
}

/**
 * Get a template part.
 *
 * @since 1.0.0
 */
function bp_mute_template_part() {

	bp_get_template_part( 'members/members-loop' );
}