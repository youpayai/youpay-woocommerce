<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://youpay.ai
 * @since      2.0.0
 *
 * @package    YouPay_WooCommerce
 * @subpackage YouPay_WooCommerce/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form action="options.php" method="post">
        <?php
        // output security fields for the registered setting "youpay"
        settings_fields( 'youpay' );
        // output setting sections and their fields
        // (sections are registered for "youpay", each field is registered to a specific section)
        do_settings_sections( 'youpay' );
        // output save settings button
        submit_button( 'Save Settings' );
        ?>
    </form>
</div>