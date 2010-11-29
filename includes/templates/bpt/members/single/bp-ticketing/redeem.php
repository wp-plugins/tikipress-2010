<div class="item-list-tabs no-ajax" id="subnav">
	<ul>
		<?php if ( bp_is_my_profile() ) : ?>
			<?php bp_get_options_nav() ?>
		<?php endif; ?>
	</ul>
</div><!-- .item-list-tabs -->

<?php do_action( 'bpt_before_tickets_redeem_content' ) ?>

<form method="post" id="redeem-code-form" class="standard-form" action="<?php echo bp_loggedin_user_domain() . BPT_TICKETS_SLUG . '/' . BPT_REDEEM_SLUG ?>">
	<?php wp_nonce_field( 'redemption' ) ?>

	<p><?php _e( "If you've been sent a WordCamp ticket code, enter the redemption code to claim it:", 'bpt' ) ?></p>
	<p><input type="text" name="redeem-code" id="redeem-code" autofocus /></p>

	<p><input type="submit" name="redeem-code-submit" value="<?php _e( "Give me the good stuff!", 'bpt' ) ?>"></p>
</form>

<?php do_action( 'bpt_after_tickets_redeem_content' ) ?>