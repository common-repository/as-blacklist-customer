<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WooCommerce_AS_Blacklist_Customer_Functions {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'as_admin_meta_box'));
        add_action('save_post', array($this, 'as_save_meta_box_blacklist_customer'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'as_blacklist_customer_notice'), 10, 2);
    }

    function as_admin_meta_box() {
        add_meta_box('as_blacklist_customer', 'Blacklist Customer (displayed only to admin)', array($this, 'as_blacklist_customer_callback'), 'shop_order', 'normal', 'high');
    }

    function as_blacklist_customer_callback($post) {
        wp_nonce_field('as_blacklist_customer_metabox', 'as_blacklist_customer_metabox_nonce');

        $as_blacklist_reason = get_user_meta($post->post_author, '_as_blacklist_reason', true);
        $as_blacklist = get_user_meta($post->post_author, '_as_blacklist', true);
        ?>
        <div class="as_metabox_wrap">
            <div class="as_metabox_row">
                <label for="as_blacklist_reason">
                    <?php _e('Blacklist', 'woo-as-blacklist-customer'); ?> :
                </label>
                <input type="checkbox" id="as_blacklist" name="as_blacklist" value="1" <?php checked($as_blacklist, 1, true); ?> />
            </div>
            <div class="as_metabox_row">
                <label for="as_blacklist_reason">
                    <?php _e('Reason', 'woo-as-blacklist-customer'); ?> :
                </label>
                <textarea type="text" id="as_blacklist_reason" name="as_blacklist_reason" ><?php echo esc_attr($as_blacklist_reason) ?></textarea>
            </div>
        </div>
        <style>
            .as_metabox_row{margin-top : 10px;}   
            .as_metabox_row input{border: 1px solid green;margin-left : 30px;}
            .as_metabox_row textarea{border: 1px solid green;width: 500px;height: 100px;margin-left : 30px;}
            .as_metabox_row label{float : left;}
        </style>
        <?php
    }

    function as_save_meta_box_blacklist_customer($post_id) {

        /*
         * We need to verify this came from our screen and with proper authorization,
         * because the save_post action can be triggered at other times.
         */

        /* Check if our nonce is set. */
        if (!isset($_POST['as_blacklist_customer_metabox_nonce'])) {
            return;
        }

        /* Verify that the nonce is valid. */
        if (!wp_verify_nonce($_POST['as_blacklist_customer_metabox_nonce'], 'as_blacklist_customer_metabox')) {
            return;
        }

        /* If this is an autosave, our form has not been submitted, so we don't want to do anything. */
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        /* Check the user's permissions. */
        if (isset($_POST['post_type']) && 'shop_order' != $_POST['post_type']) {
            return;
        }

        /* OK, it's safe for us to save the data now. */
        $customer_id = get_post_field('post_author', $post_id);
        // Make sure that it is set.
        if (!isset($_POST['as_blacklist_reason']) || !isset($_POST['as_blacklist']) || $_POST['as_blacklist'] == '' || $_POST['as_blacklist_reason'] == '') {
            delete_user_meta($customer_id, '_as_blacklist_reason');
            delete_user_meta($customer_id, '_as_blacklist');
            return;
        }

        // Sanitize user input.
        $as_blacklist_reason = sanitize_text_field($_POST['as_blacklist_reason']);
        $as_blacklist = sanitize_text_field($_POST['as_blacklist']);

        // Update the user meta field in the database.
        update_user_meta($customer_id, '_as_blacklist_reason', $as_blacklist_reason);
        update_user_meta($customer_id, '_as_blacklist', $as_blacklist);
    }

    function as_blacklist_customer_notice($column, $int) {

        global $post;

        $customer_id = $post->post_author;


        $as_blacklist_notice = trim(get_user_meta($customer_id, '_as_blacklist_reason', true));

        switch ($column) {
            case "order_number":

                if (isset($as_blacklist_notice) && !empty($as_blacklist_notice))
                    echo "<div class='as_blacklist_notice' style='background-color: red; color: white; padding: 2px;'>";
                printf("%s", esc_html($as_blacklist_notice));
                echo "</div>";

                break;
        }
    }

}

return new WooCommerce_AS_Blacklist_Customer_Functions();
