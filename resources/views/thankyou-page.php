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
<div style="max-width: 600px; margin: 0 auto;">
    <div id="youpay-share-app" data-id="<?php echo $youpay_order_id; ?>"></div>
</div>
<script src="https://youpay.link/checkout.js"></script>