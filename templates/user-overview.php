<?php
/**
 * The Template for displaying the current user overview.
 *
 * This template can be overridden by copying it to yourtheme/wpum/user-overview.php
 *
 * HOWEVER, on occasion WPUM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

$user = wp_get_current_user();

?>

<div id="wpum-user-overview">
	<div class="wpum-row">
		<div class="wpum-col-xs-3" id="avatar">
			<?php echo get_avatar( $user->data->ID, 100 ); ?>
		</div>
		<div class="wpum-col-xs-9">
			<span>
				<strong><?php echo $user->display_name; ?></strong>
			</span>
			<ul>
				<li>
					<a href="<?php echo get_permalink( wpum_get_core_page_id( 'account' ) ); ?>"><?php echo esc_html__( 'Edit account' ); ?></a>
				</li>
				<li>|</li>
				<li>
					<a href="<?php echo wp_logout_url(); ?>"><?php echo esc_html__( 'Logout' ); ?></a>
				</li>
			</ul>
		</div>
	</div>
</div>
