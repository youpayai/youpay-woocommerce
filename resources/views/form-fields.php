<?php
/**
 * Form Fields for the WooCommerce Payment Gateway
 */

$this->form_fields = array(
    'core-configuration-title' => array(
        'title'				=> __( 'Core Configuration', 'woo_youpay' ),
        'type'				=> 'title'
    ),
    'enabled' => array(
        'title'       => __( 'Enable/Disable', 'woocommerce' ),
        'label'       => __( 'Enable YouPay', 'woo_youpay' ),
        'type'        => 'checkbox',
        'description' => '',
        'default'     => 'yes',
    ),

    'title' => array(
        'title'				=> __( 'Title', 'woo_youpay' ),
        'type'				=> 'text',
        'description'		=> __( 'This controls the payment method title which the user sees during checkout.', 'woo_youpay' ),
        'default'			=> __( 'YouPay', 'woo_youpay' )
    ),

    /**
     * Customisation
     */

    'presentational-customisation-title' => array(
        'title'				=> __( 'Customisation', 'woo_youpay' ),
        'type'				=> 'title',
        'description'		=> __( 'Please feel free to customise the presentation of the YouPay elements below to suit the individual needs of your web store.</p><p><em>Please talk to development team for all advanced customisations. ' /*<a id="reset-to-default-link" style="cursor:pointer;text-decoration:underline;">Restore Defaults</a></em>'*/, 'woo_youpay' )
    ),

    'redirect_url' => array(
        'title'       => __( 'URL to redirect the Payer to after they complete a YouPay Payment', 'woo_youpay' ),
        'label'       => 'Redirect URL after payment',
        'type'        => 'text',
        'description' => '',
        'default'     => '',
    ),

    'show-info-on-product-pages' => array(
        'title'				=> __( 'Payment Info on Individual Product Pages', 'woo_youpay' ),
        'label'				=> __( 'Enable', 'woo_youpay' ),
        'type'				=> 'checkbox',
        'description'		=> __( 'Enable to display YouPay elements on individual product pages', 'woo_youpay' ),
        'default'			=> 'yes'
    ),
    'product-pages-info-text' => array(
        'type'				=> 'wysiwyg',
        'default'			=> '[youpay-popup]',
        'description'		=> __( 'Use [youpay-popup] for the default YouPay message.', 'woo_youpay' )
    ),
    'product-pages-hook' => array(
        'type'				=> 'text',
        'placeholder'		=> 'Enter hook name (e.g. woocommerce_single_product_summary)',
        'default'			=> 'woocommerce_single_product_summary',
        'description'		=> __( 'Set the hook to be used for Payment Info on Individual Product Pages.', 'woo_youpay' )
    ),
    'product-pages-priority' => array(
        'type'				=> 'number',
        'placeholder'		=> 'Enter a priority number',
        'default'			=> 15,
        'description'		=> __( 'Set the hook priority to be used for Payment Info on Individual Product Pages.', 'woo_youpay' )
    ),
);