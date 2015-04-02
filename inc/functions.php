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
 */
function bp_mute_get_button( $muted_id ) {

	global $bp, $members_template;

	if ( ! $muted_id ) {
		return;
	}

	$obj = new Mute( $muted_id, bp_loggedin_user_id() );

	if ( $obj->id ) {

		$actionurl = bp_core_get_user_domain( $muted_id ) . $bp->mute->slug . '/stop/';

		$button['id'] = 'muted';
		$button['link_href'] = wp_nonce_url( $actionurl, 'unmute' );
		$button['link_class'] = 'muted';
		$button['link_id'] = 'mute-' . $muted_id;
		$button['link_title'] = _x( 'Unmute', 'Button', 'buddypress-mute' );
		$button['link_text'] = _x( 'Unmute', 'Button', 'buddypress-mute' );
		$button['wrapper_class'] = 'mute-button';

	} else {

		$actionurl = bp_core_get_user_domain( $muted_id ) . $bp->mute->slug . '/start/';

		$button['id'] = 'unmuted';
		$button['link_href'] = wp_nonce_url( $actionurl, 'mute' );
		$button['link_class'] = 'unmuted';
		$button['link_id'] = 'mute-' . $muted_id;
		$button['link_title'] = _x( 'Mute', 'Button', 'buddypress-mute' );
		$button['link_text'] = _x( 'Mute', 'Button', 'buddypress-mute' );
		$button['wrapper_class'] = 'mute-button';
	}

	$button['component'] = 'mute';
	$button['wrapper_id'] = 'mute-button-' . $muted_id;
	$button['must_be_logged_in'] = true;
	$button['block_self'] = true;

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
 * Output a button for each member.
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
 * @since 1.0.0
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
 * Start muting a user.
 *
 * Hooked to bp_actions, this function is used when js is disabled.
 *
 * @since 1.0.0
 */
function bp_mute_action_start() {

	global $bp;

	if ( ! bp_is_current_component( 'mute' ) || ! bp_is_current_action( 'start' ) ) {
		return;
	}

	check_admin_referer( 'mute' );

	$obj = new Mute( bp_displayed_user_id(), bp_loggedin_user_id() );

	if ( $obj->id ) {

		bp_core_add_message( sprintf( __( 'You are already muting %s.', 'buddypress-mute' ), bp_get_displayed_user_fullname() ), 'error' );

	} else {

		$result = $obj->save();

		if ( $result === false ) {

			bp_core_add_message( __( 'An error occurred, please try again.', 'buddypress-mute' ), 'error' );

		} else {

			bp_core_add_message( sprintf( __( 'You are now muting %s.', 'buddypress-mute' ), bp_get_displayed_user_fullname() ) );
		}
	}

	bp_core_redirect( wp_get_referer() );
}
add_action( 'bp_actions', 'bp_mute_action_start' );

/**
 * Stop muting a user.
 *
 * Hooked to bp_actions, this function is used when js is disabled.
 *
 * @since 1.0.0
 */
function bp_mute_action_stop() {

	global $bp;

	if ( ! bp_is_current_component( 'mute' ) || ! bp_is_current_action( 'stop' ) ) {
		return;
	}

	check_admin_referer( 'unmute' );

	$obj = new Mute( bp_displayed_user_id(), bp_loggedin_user_id() );

	if ( ! $obj->id ) {

		bp_core_add_message( sprintf( __( 'You are not muting %s.', 'buddypress-mute' ), bp_get_displayed_user_fullname() ), 'error' );

	} else {

		$result = $obj->delete();

		if ( $result === false ) {

			bp_core_add_message( __( 'An error occurred, please try again.', 'buddypress-mute' ), 'error' );

		} else {

			bp_core_add_message( sprintf( __( 'You have successfully unmuted %s.', 'buddypress-mute' ), bp_get_displayed_user_fullname() ) );
		}
	}

	bp_core_redirect( wp_get_referer() );
}
add_action( 'bp_actions', 'bp_mute_action_stop' );

/**
 * Filter the activity loop to show items from unmuted users only.
 *
 * @since 1.0.0
 *
 * @return array
 */
function bp_mute_filter_activity( $r ) {

	$ids = Mute::get_muting( bp_loggedin_user_id() );

	$args = array(
		'exclude' => $ids,
		'fields' => 'ID'
	);

	$r['user_id'] = get_users( $args );

	return $r;
}
add_filter( 'bp_after_has_activities_parse_args', 'bp_mute_filter_activity' );

/**
 * Filter the members loop to show muted friends.
 *
 * @since 1.0.0
 */
function bp_mute_filter_members_friends( $r ) {

	if ( bp_is_current_component( 'mute' ) && bp_is_current_action( 'friends' ) ) {

		$ids = Mute::get_muting( bp_loggedin_user_id() );

		foreach ( $ids as $id ) {

			$result = friends_check_friendship( bp_loggedin_user_id(), $id );

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
 */
function bp_mute_filter_members_all( $r ) {

	if ( bp_is_current_component( 'mute' ) && bp_is_current_action( 'all' ) ) {

		$ids = Mute::get_muting( bp_loggedin_user_id() );

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