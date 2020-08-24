<?php
/**
 * YouPay Thank YouPage
 *
 * @package 1.0.0
 */

$youpay_order = wc_get_order( $order_id );
$youpay_link  = 'https://youpay.link/' . $youpay_order->get_meta( 'youpay_url' );
$youpay_order_id  = $youpay_order->get_meta( 'youpay_order_id' );

?>
<div id="youpay-share-app" data-id="<?php echo $youpay_order_id; ?>"></div>
<script src="<?php echo $youpay_js; ?>"></script>