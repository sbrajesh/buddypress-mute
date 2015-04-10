<?php
/**
 * Ajax request functions
 *
 * @package BuddyPress Mute
 * @subpackage Ajax
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Start muting a given user.
 *
 * @since 1.0.0
 */
function bp_mute_ajax_start() {

	check_ajax_referer( 'mute-nonce', 'start' );

	$muted_id = (int) $_POST['uid'];

	$obj = new Mute( $muted_id, bp_loggedin_user_id() );

	if ( $obj->id ) {

		$response['status'] = 'failure';

	} else {

		$result = $obj->save();

		if ( $result === false ) {

			$response['status'] = 'failure';

		} else {

			$response['status'] = 'success';
		}
	}

	$count = Mute::get_count( bp_displayed_user_id() );

	if ( $count )
		$response['count'] = $count;
	else
		$response['count'] = 0;

	wp_send_json( $response );
}
add_action( 'wp_ajax_mute', 'bp_mute_ajax_start' );

/**
 * Stop muting a given user.
 *
 * @since 1.0.0
 */
function bp_mute_ajax_stop() {

	check_ajax_referer( 'unmute-nonce', 'stop' );

	$muted_id = (int) $_POST['uid'];

	$obj = new Mute( $muted_id, bp_loggedin_user_id() );

	if ( ! $obj->id ) {

		$response['status'] = 'failure';

	} else {

		$result = $obj->delete();

		if ( $result === false ) {

			$response['status'] = 'failure';

		} else {

			$response['status'] = 'success';
		}
	}

	$count = Mute::get_count( bp_displayed_user_id() );

	if ( $count )
		$response['count'] = $count;
	else
		$response['count'] = 0;

	wp_send_json( $response );
}
add_action( 'wp_ajax_unmute', 'bp_mute_ajax_stop' );