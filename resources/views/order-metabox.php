
<div id="yp-order-metabox">
    <div class="yp-box">
        <div class="yp-logo-header">
            <img src="<?php echo $this->youpay->resource_root; ?>/images/youpay-logo.svg" width="200">
        </div>
        <div class="yp-middle">
            <a href="<?php echo $order_link; ?>" target="_blank" class="button button-primary">View Order on YouPay</a>
        </div>
    </div>
</div>

<style>
    #youpay_order_metabox h2 {
        display: none;
    }
    #yp-order-metabox .yp-box {
        display: flex;
        padding-top: 6px;
    }
    .yp-logo-header {
        padding: 20px;
        width: 200px;
    }
    .yp-middle {
        display: flex;
        align-items: center;
    }
</style>