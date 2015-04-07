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

	bp_core_load_template( 'members/single/plugins' );
}

/**
 * Filter the template location.
 *
 * @since 1.0.0
 *
 * @param string $found_template Located template path.
 * @param array $templates Array of templates to attempt to load.
 * @param string
 */
function bp_mute_load_template_filter( $found_template, $templates ) {

	global $bp;

	if ( ! bp_is_current_component( $bp->mute->slug ) ) {

		return $found_template;
	}

	if ( empty( $found_template ) ) {

		bp_register_template_stack( 'bp_mute_get_template_directory', 14 );

		$found_template = locate_template( 'members/single/plugins.php', false, false );

		add_action( 'bp_after_member_plugin_template', 'bp_mute_disable_members_loop_ajax' );

		add_action( 'bp_template_content', 'bp_mute_template_part' );
	}

	return $found_template;
}
add_filter( 'bp_located_template', 'bp_mute_load_template_filter', 10, 2 );

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