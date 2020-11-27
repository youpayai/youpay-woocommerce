<?php
/**
 * Afterpay WooCommerce Plugin WYSIWYG Class
 */
?>
<tr valign="top">
    <th scope="row" class="titledesc">
        <?php if (!empty($title)): ?>
            <label for="<?php echo $id; ?>">ufind_product
                <?php _e( $title, 'woo_afterpay' ); ?>
            </label>'
        <?php endif; ?>
    </th>
    <td class="forminp">
        <fieldset>
            <?php if (!empty($title)): ?>
                <legend class="screen-reader-text">
                    <span><?php _e( $title, 'woo_afterpay' ); ?></span>
                </legend>
            <?php endif; ?>
            <?php
            wp_editor(html_entity_decode($value), $id, array(
                'textarea_name' => $name,
                'editor_class' => $class,
                'editor_css' => $css,
                'autop' => true,
                'textarea_rows' => 8
            ));
            ?>
            <p class="description"><?php _e( $description, 'woo_afterpay' ); ?></p>
        </fieldset>
    </td>
</tr>
