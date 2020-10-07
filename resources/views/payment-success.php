<?php
/**
 * Payment Success Default HTML
 */
?>

<div id="youpay-payment-success">
    <!-- TODO: Icon in SVG -->
    <div>
        <img id="youpay-logo" src="<?php echo $this->youpay->resource_root; ?>/images/youpay-logo.svg" />
    </div>
    <div>
        <img id="youpay-tick" src="<?php echo $this->youpay->resource_root; ?>/images/success-tick.svg" />
    </div>
    <h1>Payment Confirmed</h1>
    <p>Thank you for your payment!</p>
    <p>The order will now be processed. The order recipient will be notified.</p>
    <p><a class="youpay-link" href="<?php echo get_permalink( woocommerce_get_page_id( 'shop' ) ); ?>">Back to Shop</a></p>
    <style>
        #youpay-payment-success,
        #youpay-payment-success #youpay-tick,
        #youpay-payment-success #youpay-logo
        #youpay-payment-success h1,
        #youpay-payment-success p {
            font-weight: bold;
            text-align: center;
        }
        #youpay-payment-success #youpay-tick {
            display: inline;
            width: 60px;
        }

        #youpay-payment-success h1 {
            color: #2D2D2D;
            font-weight: 700;
        }

        #youpay-payment-success p {
            color: #2D2D2D;
            margin: 0;
            font-size: 21px;
            font-weight: 400;
        }

        #youpay-payment-success #youpay-logo {
            display: inline;
            height: 50px;
            margin-bottom: 20px;
        }

        #youpay-payment-success a.youpay-link {
            display: inline-block;
            margin-top: 15px;
            font-size: 22px;
            background-color: rgb(12, 217, 220);
            color: #FFFFFF;
            border-radius: 100px 100px 100px 100px;
            padding: 15px 22px 17px 20px;
            text-decoration: none!important;
            line-height: 100%;
        }
    </style>
</div>
