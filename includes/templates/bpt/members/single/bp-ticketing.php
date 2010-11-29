<?php get_header() ?>

	<div id="content">
		<div class="padder">

			<?php do_action( 'bp_before_member_home_content' ) ?>

			<div id="item-header">
				<?php bpt_load_template( array( 'members/single/member-header.php' ) ) ?>
			</div><!-- #item-header -->

			<div id="item-nav">
				<div class="item-list-tabs no-ajax" id="object-nav">
					<ul>
						<?php bp_get_displayed_user_nav() ?>

						<?php do_action( 'bp_member_options_nav' ) ?>
					</ul>
				</div>
			</div><!-- #item-nav -->

			<div id="item-body">
				<?php do_action( 'bp_before_member_body' ) ?>

				<?php if ( bpt_is_member_redeem_page() ) : ?>
					<?php bpt_load_template( array( 'bpt/members/single/bp-ticketing/redeem.php' ) ) ?>
				<?php elseif ( bpt_is_member_tickets_page() ) : ?>
					<?php bpt_load_template( array( 'bpt/members/single/bp-ticketing/history.php' ) ) ?>
				<?php endif; ?>

				<?php do_action( 'bp_after_member_body' ) ?>

			</div><!-- #item-body -->

			<?php do_action( 'bp_after_member_home_content' ) ?>

		</div><!-- .padder -->
	</div><!-- #content -->

	<?php bpt_load_template( array( 'sidebar.php' ) ) ?>

<?php get_footer() ?>