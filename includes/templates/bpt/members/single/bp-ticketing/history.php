<div class="item-list-tabs no-ajax" id="subnav">
	<ul>
		<?php if ( bp_is_my_profile() ) : ?>
			<?php bp_get_options_nav() ?>
		<?php endif; ?>
	</ul>
</div><!-- .item-list-tabs -->

<?php do_action( 'bpt_before_tickets_history_content' ) ?>

<?php $purchases = bpt_get_ticket_purchases(); ?>

<?php if ( $purchases ) : ?>
	<table id="ticketing-history">
		<thead>
			<tr>
				<th scope="col"><?php _e( "Event", 'bpt' ) ?></th>
				<th scope="col"><?php _e( "Purchase information", 'bpt' ) ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
	
			foreach( (array)$purchases as $purchase ) :
				$url = esc_url( bpt_wpsc_get_product_meta( $purchase->product_id, 'wordcamp' ) );
				$name = apply_filters( 'bpt_members_ticketing_history_name', $purchase->product_name );
			?>
				<tr>
					<td><a href="<?php echo $url ?>"><?php echo $name ?></a></td>
					<td><a href="<?php echo site_url( 'your-account' ) ?>"><?php _e( "Purchase information", 'bpt' ) ?></a></td>
				</tr>
			<?php
			endforeach;
			?>
		</tbody>
	</table>

<?php else : ?>

<p><?php _e( "You have not bought any tickets. Tickets which you've redeemed by voucher are not listed.", 'bpt' ) ?></p>
<p><?php printf( __( "If you've been sent a claim code, go to the <a href='%s'>voucher redemption</a> page.", 'bpt' ), bp_get_loggedin_user_link() . BPT_TICKETS_SLUG . '/' . BPT_REDEEM_SLUG ) ?></p>

<?php endif; ?>

<?php do_action( 'bpt_after_tickets_history_content' ) ?>