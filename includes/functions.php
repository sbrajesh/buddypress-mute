<?php
/**
 * User defined functions
 *
 * @package BuddyPress Mute
 * @subpackage Functions
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Create a button.
 *
 * @since 1.0.0
 *
 * @param int $muted_id The ID of the muted user.
 * @return string
 */
function bp_mute_get_button( $muted_id ) {

	global $bp, $members_template;

	if ( ! $muted_id ) {
		return;
	}

	$obj = new Mute( $muted_id, bp_loggedin_user_id() );

	$action = $obj->id ? '/stop/' : '/start/';
	$url = bp_core_get_user_domain( $muted_id ) . $bp->mute->slug . $action;

	$button = array(
		'id'                => $obj->id ? 'muted' : 'unmuted',
		'link_class'        => $obj->id ? 'muted' : 'unmuted',
		'link_id'           => $obj->id ? 'mute-' . $muted_id : 'mute-' . $muted_id,
		'link_title'        => $obj->id ? _x( 'Unmute', 'Button', 'buddypress-mute' ) : _x( 'Mute', 'Button', 'buddypress-mute' ),
		'link_text'         => $obj->id ? _x( 'Unmute', 'Button', 'buddypress-mute' ) : _x( 'Mute', 'Button', 'buddypress-mute' ),
		'link_href'         => $obj->id ? wp_nonce_url( $url, 'unmute' ) : wp_nonce_url( $url, 'mute' ),
		'wrapper_class'     => 'mute-button',
		'component'         => 'mute',
		'wrapper_id'        => 'mute-button-' . $muted_id,
		'must_be_logged_in' => true,
		'block_self'        => true
	);
	return bp_get_button( $button );
}

/**
 * Output a button in the profile header area.
 *
 * @since 1.0.0
 */
function bp_mute_add_member_header_button() {

	echo bp_mute_get_button( bp_displayed_user_id() );
}
add_action( 'bp_member_header_actions', 'bp_mute_add_member_header_button', 99 );

/**
 * Output a button for each member in the loop.
 *
 * @since 1.0.0
 */
function bp_mute_add_member_dir_button() {

	global $members_template;

	echo bp_mute_get_button( $members_template->member->id );
}
add_action( 'bp_directory_members_actions', 'bp_mute_add_member_dir_button', 99 );

/**
 * Output a button for each member in the group.
 *
 * @since 1.0.3
 */
function bp_mute_add_group_member_dir_button() {

	global $members_template;

	echo bp_mute_get_button( $members_template->member->user_id );
}
add_action( 'bp_group_members_list_item_action', 'bp_mute_add_group_member_dir_button', 99 );

/**
 * Delete all mute records relating to a given user.
 *
 * @since 1.1.0
 *
 * @param int $user_id The ID of the identicon owner.
 */
function bp_mute_delete( $user_id ) {

	Mute::delete_all( $user_id );
}
add_action( 'delete_user', 'bp_mute_delete' );
add_action( 'bp_core_deleted_account', 'bp_mute_delete' );

/**
 * Start muting a user if JavaScript is disabled.
 *
 * @since 1.0.0
 */
function bp_mute_action_start() {

	if ( ! bp_is_current_component( 'mute' ) || ! bp_is_current_action( 'start' ) ) {
		return;
	}

	check_admin_referer( 'mute' );

	$obj = new Mute( bp_displayed_user_id(), bp_loggedin_user_id() );

	if ( $obj->id ) {

		$message = sprintf( __( 'You are already muting %s.', 'buddypress-mute' ), bp_get_displayed_user_fullname() );
		$status = 'error';

	} else {

		if ( $obj->save() === false ) {
			$message = __( 'This user could not be muted. Try again.', 'buddypress-mute' );
			$status = 'error';
		} else {
			$message = sprintf( __( 'You are now muting %s.', 'buddypress-mute' ), bp_get_displayed_user_fullname() );
			$status = 'success';
		}
	}
	bp_core_add_message( $message, $status );
	bp_core_redirect( wp_get_referer() );
}
add_action( 'bp_actions', 'bp_mute_action_start' );

/**
 * Stop muting a user if JavaScript is disabled.
 *
 * @since 1.0.0
 */
function bp_mute_action_stop() {

	if ( ! bp_is_current_component( 'mute' ) || ! bp_is_current_action( 'stop' ) ) {
		return;
	}

	check_admin_referer( 'unmute' );

	$obj = new Mute( bp_displayed_user_id(), bp_loggedin_user_id() );

	if ( ! $obj->id ) {

		$message = sprintf( __( 'You are not muting %s.', 'buddypress-mute' ), bp_get_displayed_user_fullname() );
		$status = 'error';

	} else {

		if ( $obj->delete() === false ) {
			$message = __( 'This user could not be unmuted. Try again.', 'buddypress-mute' );
			$status = 'error';
		} else {
			$message = sprintf( __( 'You have successfully unmuted %s.', 'buddypress-mute' ), bp_get_displayed_user_fullname() );
			$status = 'success';
		}
	}
	bp_core_add_message( $message, $status );
	bp_core_redirect( wp_get_referer() );
}
add_action( 'bp_actions', 'bp_mute_action_stop' );

/**
 * Start muting a user if JavaScript is enabled.
 *
 * @since 1.0.0
 */
function bp_mute_ajax_start() {

	check_ajax_referer( 'mute-nonce', 'start' );

	$mute = new Mute( (int) $_POST['uid'], bp_loggedin_user_id() );

	if ( $mute->id ) {

		$response['status'] = 'failure';

	} else {

		$response['status'] = $mute->save() ? 'success' : 'failure';
	}

	$count = Mute::get_count( bp_displayed_user_id() );
	$response['count'] = $count ? $count : 0;

	wp_send_json( $response );
}
add_action( 'wp_ajax_mute', 'bp_mute_ajax_start' );

/**
 * Stop muting a user if JavaScript is enabled.
 *
 * @since 1.0.0
 */
function bp_mute_ajax_stop() {

	check_ajax_referer( 'unmute-nonce', 'stop' );

	$mute = new Mute( (int) $_POST['uid'], bp_loggedin_user_id() );

	if ( ! $mute->id ) {

		$response['status'] = 'failure';

	} else {

		$response['status'] = $mute->delete() ? 'success' : 'failure';
	}

	$count = Mute::get_count( bp_displayed_user_id() );
	$response['count'] = $count ? $count : 0;

	wp_send_json( $response );
}
add_action( 'wp_ajax_unmute', 'bp_mute_ajax_stop' );

/**
 * Filter activity stream if scope isn't set.
 *
 * @since 1.0.3
 *
 * @param array $r The arguments.
 * @return array
 */
function bp_mute_activity_filter( $r ) {

	if ( ! is_user_logged_in() ) {
		return $r;
	}

	if ( ! bp_is_activity_directory() ) {
		return $r;
	}

	$filter_query[] = array(
		array(
			'compare' => 'NOT IN',
			'column'  => 'user_id',
			'value'   =>  (array) Mute::get_muting( bp_loggedin_user_id() )
		)
	);
	$r['filter_query'] = $filter_query;

	return $r;
}
add_filter( 'bp_after_has_activities_parse_args', 'bp_mute_activity_filter' );

/**
 * Filter activity stream if scope is 'friends'.
 *
 * @since 1.0.1
 *
 * @param array $retval The activity query clauses.
 * @param array $filter The current activity arguments.
 * @return array
 */
function bp_mute_friends_activity_scope( $retval = array(), $filter = array() ) {

	// Bail if not on the expected page.
	if ( ! bp_is_activity_directory() ) {
		if ( ! bp_is_my_profile() ) {
			return $retval;
		}
	}

	// Get the ID of the user.
	$user_id = bp_loggedin_user_id();

	// Get an array of friends.
	$friend_ids = friends_get_friend_user_ids( $user_id );

	// Get an array of muted users.
	$muted_ids = Mute::get_muting( $user_id );

	if ( empty( $friend_ids ) ) {
		$friend_ids = array( 0 );
	}

	// Get non-muted friends.
	$friend_ids = array_diff( $friend_ids, $muted_ids );

	if ( empty( $friend_ids ) ) {
		$friend_ids = array( 0 );
	}

	$retval = array(
		'relation' => 'AND',
		'override' => array(
			'filter'      => array( 'user_id' => 0 ),
			'show_hidden' => true
		)
	);

	$retval[] = array(
		'compare' => 'IN',
		'column'  => 'user_id',
		'value'   => (array) $friend_ids
	);

	$retval[] = array(
		'column' => 'hide_sitewide',
		'value'  => 0
	);
	return $retval;
}
add_filter( 'bp_activity_set_friends_scope_args', 'bp_mute_friends_activity_scope', 12, 2 );

/**
 * Filter the members loop to show muted friends.
 *
 * @since 1.0.0
 *
 * @param array $r Arguments for changing the contents of the loop.
 * @return array
 */
function bp_mute_filter_members_friends( $r ) {

	if ( ! bp_is_active( 'friends' ) ) {
		return $r;
	}

	if ( bp_is_current_component( 'mute' ) && bp_is_current_action( 'friends' ) ) {

		$ids = Mute::get_muting( bp_displayed_user_id() );

		foreach ( $ids as $id ) {

			$result = friends_check_friendship( bp_displayed_user_id(), $id );

			if ( $result )
				$array[] = $id;
		}

		if ( empty( $array ) ) {

			$r['include'] = 0;

		} else {

			$r['include'] = $array;
		}
	}

	return $r;
}
add_filter( 'bp_after_has_members_parse_args', 'bp_mute_filter_members_friends' );

/**
 * Filter the members loop to show all muted users.
 *
 * @since 1.0.0
 *
 * @param array $r Arguments for changing the contents of the loop.
 * @return array
 */
function bp_mute_filter_members_all( $r ) {

	if ( bp_is_current_component( 'mute' ) && bp_is_current_action( 'all' ) ) {

		$ids = Mute::get_muting( bp_displayed_user_id() );

		if ( empty( $ids ) ) {

			$r['include'] = 0;

		} else {

			$r['include'] = $ids;
		}
	}

	return $r;
}
add_filter( 'bp_after_has_members_parse_args', 'bp_mute_filter_members_all' );

/**
 * Disable ajax in the plugin.php members loop.
 *
 * @since 1.0.0
 */
function bp_mute_disable_members_loop_ajax() {
	?>
	<script>
		jQuery(document).ready( function() {
			jQuery( "#pag-top, #pag-bottom" ).addClass( "no-ajax" );
		});
	</script>
	<?php
}