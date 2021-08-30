<?php
/*
Plugin Name: Choose Variation
description: Choose variation on checkout page
Version: 0.8
Author: Sandi Rosyandi
License: GPL2
*/

add_action('wp_head', function () {
?>
    <style>        
        .checkout-attribute-item {
            background: #ffffff;
            margin-bottom: 20px;
        }
        
        .checkout-attribute-item:last-child {
            margin-bottom: 0px;
        }
        
        .checkout-attribute-item h3 {
            font-size: 24px;
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .checkout-attribute-item-container {
            display: flex;
        }
        
        .checkout-attribute-image {
            border: 2px solid #eeeeee;
            border-radius: 2px;
            cursor: pointer;
            margin-right: 10px;
            padding: 5px;
        }
        
        .checkout-attribute-image:hover, .checkout-attribute-image.active {
            border-color: #aaaaaa;
        }
        
        .checkout-attribute-image img {
            width: 70px;
        }
        
        .checkout-attribute-radio input {
            margin-right: 5px;
            position: static !important;
        }
        
        .wfacp-order-summary-label, .wfacp_elementor_mini_cart_widget, .wfacp_template_9_cart_item_details {
            display: none !important;
        }
        
        .checkout-attribute-radio {
            width: 100%;
        }
        
        .checkout-attribute-radio-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 10px;
            position: relative;
        }
        
        .checkout-attribute-radio-item.highlight {
            background-color: #fef036;
            border: 1px solid #000000;
            border-radius: 3px;
            font-weight: 700;
        }
        
        .checkout-attribute-radio-best-seller {
            left: -50px;
            position: absolute;
            top: -10px;
        }
        
        .checkout-attribute-radio-arrow {
            left: -65px;
            position: absolute;
            top: -90px;
            transform: rotate(40deg);
        }
    </style>
<?php
});

add_shortcode('choose-variation', function () {
    ob_start();
    global $woocommerce;
    
    if (!$woocommerce->cart) {
        return false;
    }
    
    foreach ($woocommerce->cart->get_cart() as $cart) {
        $tempProduct = wc_get_product($cart['product_id']);
        if ($tempProduct->is_type('variable')) {
            $productId = $cart['product_id'];
            $product = wc_get_product($productId);  
        }
    }

    if (!$product) {
        return false;
    }
    
    $swatches = get_post_meta($productId, 'th_custom_attribute_settings', true);
    $defaultAttributes = $product->get_default_attributes();
    
    $prices = [];
    foreach ($product->get_available_variations() as $variation) {
        $quantity = get_post_meta($variation['variation_id'], 'custom_quantity', true);
        if (!$quantity) {
            $quantity = 1;
        }
        $prices[$variation['attributes']['attribute_deal']] = $variation['display_price'] * $quantity;
    }
?>
    <div class="checkout-attribute" id="checkout-attribute" data-url="<?php echo admin_url('admin-ajax.php') ?>">
        <input type="hidden" name="action" value="checkout_attribute" />
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('checkout_attribute') ?>" />
        <input type="hidden" name="product_id" value="<?php echo $productId ?>" />
<?php
        $headings = get_post_meta($productId, 'custom_heading', true);
        foreach($product->get_attributes() as $key => $attribute) {
            $attributeName = strtolower($attribute->get_name());
?>
            <div class="checkout-attribute-item">
                <h3>
                    <?php
                        if (isset($headings[$key])) {
                            if ($headings[$key]) {
                                echo $headings[$key];
                            } else {
                                echo $attribute->get_name();    
                            }
                        } else {
                            echo $attribute->get_name();
                        }
                    ?>
                </h3>
                <div class="checkout-attribute-item-container">
<?php
                    if (isset($swatches[$attributeName])) {
                        $swatch = $swatches[$attributeName];
                        if ($swatch['type'] == 'image') {                       
                            echo '<input type="hidden" name="' . $attributeName . '" class="checkout-attribute-item-value" value="' . $defaultAttributes[$attributeName] . '" />';
                            foreach ($attribute->get_options() as $option) {
                                $active = '';
                                if ($defaultAttributes[$attributeName] == $option) {
                                    $active = 'active';
                                }
                                
                                $termId = $swatch[$option]['term_value'];
                                $attachment = wp_get_attachment_image_src($termId);
                                if ($attachment) {
?>
                                    <div class="checkout-attribute-image <?php echo $active ?>" data-option="<?php echo $option ?>">
                                        <img src="<?php echo $attachment[0] ?>" />
                                    </div>          
<?php   
                                }
                            }   
                        } else if ($attributeName == 'deal') {
                            $highlightDeal = get_post_meta($productId, 'highlight_deal', true);
                            $popularDeal = get_post_meta($productId, 'highlight_deal_popular', true);
?>
                            <div class="checkout-attribute-radio">      
<?php
                                foreach ($attribute->get_options() as $option) {
                                    $checked = '';
                                    if ($defaultAttributes['deal'] == $option) {
                                        $checked = 'checked="checked"';
                                    }
                                    
                                    if ($option == $highlightDeal) {
                                        echo '
                                            <div class="checkout-attribute-radio-item highlight">
                                                <div class="checkout-attribute-radio-item-wrapper">
                                                    <span class="checkout-attribute-radio-arrow"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" width="50" height="110" viewBox="0 0 106 110" enable-background="new 0 0 106 110" xml:space="preserve"><path fill="#fdc301" d="M5.11 29.39c3.87 10.2 9.34 19.72 16.1 28.27 1.8 2.28 3.69 4.49 5.67 6.62 1.02 1.3 2.07 2.58 3.15 3.84 13.79 16.04 35.63 32.15 57.92 28.05 -2.16 0.55-4.3 1.2-6.43 1.81 -6.48 1.82-12.93 3.73-19.37 5.69 -1.62 0.5-1.02 2.82 0.64 2.54 0.86-0.15 1.72-0.3 2.58-0.44 0.15 0.03 0.31 0.03 0.48 0 0.73-0.12 1.47-0.26 2.2-0.4 -0.81 0.28-1.62 0.56-2.43 0.87 -1.51 0.58-1.14 2.62 0.35 2.64 0.25 0.14 0.56 0.2 0.93 0.12 1.13-0.26 2.26-0.53 3.39-0.8 10.43-1.76 20.76-3.99 30.98-6.74 0.45-0.12 0.73-0.38 0.87-0.68 0.63-0.16 1.14-0.74 1.07-1.35 1.18 0.3 2.41-1.16 1.31-2.08 -6.73-5.59-13.53-11.14-20.96-15.66 -3.99-3.15-8.11-6.13-12.38-8.91 -1.23-0.8-2.35 1.12-1.17 1.95 0.43 0.3 0.86 0.6 1.29 0.9 -0.78-0.1-1.3 0.65-1.27 1.39 -0.72-0.42-1.44-0.84-2.17-1.26 -1.45-0.84-2.69 1.27-1.33 2.22 7.08 4.91 14.17 9.83 21.25 14.75 -0.47-0.06-0.95-0.12-1.42-0.19 -9.64-1.95-18.62-6.83-26.79-12.15 -9.06-5.9-17.31-13.04-24.49-21.12C22.61 45.25 13.56 28.59 8.55 10.63 8.12 8.58 7.74 6.52 7.4 4.46c-0.05-0.31-0.27-0.57-0.55-0.75 -0.13-0.6-0.27-1.2-0.39-1.81C6.17 0.5 3.86 0.73 3.93 2.18c0.04 0.92 0.1 1.84 0.16 2.76C-1.93 11.24 2.44 22.37 5.11 29.39zM71.95 104.63c0.88-0.15 1.77-0.3 2.65-0.45 -1.3 0.34-2.6 0.69-3.91 1.03 -0.08-0.1-0.19-0.2-0.3-0.27C70.91 104.85 71.43 104.74 71.95 104.63zM78.59 93.55c1.15 0.28 2.3 0.53 3.46 0.76 -1.2 0.04-2.39 0.02-3.59-0.06C78.56 94.03 78.61 93.79 78.59 93.55zM71.43 93.24c-7.11-1.64-13.96-5.02-20.25-9.31C57.58 87.72 64.37 90.86 71.43 93.24zM34.02 62c7.56 8.3 16.26 15.6 25.82 21.5 0.95 0.59 1.92 1.16 2.89 1.73 -9.77-4.68-18.69-11.17-26.23-19.02 -6.08-6.34-11.18-13.42-15.37-21.01C24.95 51.15 29.26 56.77 34.02 62zM32.87 66.75c-1.44-1.39-2.85-2.83-4.22-4.3 -3.68-4.73-6.96-9.77-9.79-15.05C22.93 54.32 27.59 60.83 32.87 66.75zM4.43 8.76C4.48 9.25 4.54 9.75 4.6 10.25c-0.55 0.21-0.95 0.73-0.75 1.45 0.63 2.25 1.31 4.49 2.04 6.71 1.38 6.97 3.44 13.79 6.13 20.34 -0.63-1.23-1.25-2.48-1.83-3.74 -2.11-4.56-3.88-9.28-5.26-14.11C3.91 17.3 2.78 12.38 4.43 8.76z"></path></svg></span>
                                                    <img src="' . plugin_dir_url(__FILE__) . 'images/best-seller.png" class="checkout-attribute-radio-best-seller" />
                                                    <input type="radio" name="' . $attributeName . '" value="' . $option . '" ' . $checked . ' />
                                                    <span>' . $option . '</span>
                                                </div>
                                                <div class="deal-price">' . wc_price($prices[$option]) . '</div>
                                            </div>
                                        ';
                                    } else if ($option == $popularDeal) {
                                        echo '
                                            <div class="checkout-attribute-radio-item highlight">
                                                <div class="checkout-attribute-radio-item-wrapper">
                                                    <span class="checkout-attribute-radio-arrow"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" width="50" height="110" viewBox="0 0 106 110" enable-background="new 0 0 106 110" xml:space="preserve"><path fill="#fdc301" d="M5.11 29.39c3.87 10.2 9.34 19.72 16.1 28.27 1.8 2.28 3.69 4.49 5.67 6.62 1.02 1.3 2.07 2.58 3.15 3.84 13.79 16.04 35.63 32.15 57.92 28.05 -2.16 0.55-4.3 1.2-6.43 1.81 -6.48 1.82-12.93 3.73-19.37 5.69 -1.62 0.5-1.02 2.82 0.64 2.54 0.86-0.15 1.72-0.3 2.58-0.44 0.15 0.03 0.31 0.03 0.48 0 0.73-0.12 1.47-0.26 2.2-0.4 -0.81 0.28-1.62 0.56-2.43 0.87 -1.51 0.58-1.14 2.62 0.35 2.64 0.25 0.14 0.56 0.2 0.93 0.12 1.13-0.26 2.26-0.53 3.39-0.8 10.43-1.76 20.76-3.99 30.98-6.74 0.45-0.12 0.73-0.38 0.87-0.68 0.63-0.16 1.14-0.74 1.07-1.35 1.18 0.3 2.41-1.16 1.31-2.08 -6.73-5.59-13.53-11.14-20.96-15.66 -3.99-3.15-8.11-6.13-12.38-8.91 -1.23-0.8-2.35 1.12-1.17 1.95 0.43 0.3 0.86 0.6 1.29 0.9 -0.78-0.1-1.3 0.65-1.27 1.39 -0.72-0.42-1.44-0.84-2.17-1.26 -1.45-0.84-2.69 1.27-1.33 2.22 7.08 4.91 14.17 9.83 21.25 14.75 -0.47-0.06-0.95-0.12-1.42-0.19 -9.64-1.95-18.62-6.83-26.79-12.15 -9.06-5.9-17.31-13.04-24.49-21.12C22.61 45.25 13.56 28.59 8.55 10.63 8.12 8.58 7.74 6.52 7.4 4.46c-0.05-0.31-0.27-0.57-0.55-0.75 -0.13-0.6-0.27-1.2-0.39-1.81C6.17 0.5 3.86 0.73 3.93 2.18c0.04 0.92 0.1 1.84 0.16 2.76C-1.93 11.24 2.44 22.37 5.11 29.39zM71.95 104.63c0.88-0.15 1.77-0.3 2.65-0.45 -1.3 0.34-2.6 0.69-3.91 1.03 -0.08-0.1-0.19-0.2-0.3-0.27C70.91 104.85 71.43 104.74 71.95 104.63zM78.59 93.55c1.15 0.28 2.3 0.53 3.46 0.76 -1.2 0.04-2.39 0.02-3.59-0.06C78.56 94.03 78.61 93.79 78.59 93.55zM71.43 93.24c-7.11-1.64-13.96-5.02-20.25-9.31C57.58 87.72 64.37 90.86 71.43 93.24zM34.02 62c7.56 8.3 16.26 15.6 25.82 21.5 0.95 0.59 1.92 1.16 2.89 1.73 -9.77-4.68-18.69-11.17-26.23-19.02 -6.08-6.34-11.18-13.42-15.37-21.01C24.95 51.15 29.26 56.77 34.02 62zM32.87 66.75c-1.44-1.39-2.85-2.83-4.22-4.3 -3.68-4.73-6.96-9.77-9.79-15.05C22.93 54.32 27.59 60.83 32.87 66.75zM4.43 8.76C4.48 9.25 4.54 9.75 4.6 10.25c-0.55 0.21-0.95 0.73-0.75 1.45 0.63 2.25 1.31 4.49 2.04 6.71 1.38 6.97 3.44 13.79 6.13 20.34 -0.63-1.23-1.25-2.48-1.83-3.74 -2.11-4.56-3.88-9.28-5.26-14.11C3.91 17.3 2.78 12.38 4.43 8.76z"></path></svg></span>
                                                    <img src="' . plugin_dir_url(__FILE__) . 'images/most-popular.png" class="checkout-attribute-radio-best-seller" />
                                                    <input type="radio" name="' . $attributeName . '" value="' . $option . '" ' . $checked . ' />
                                                    <span>' . $option . '</span>
                                                </div>
                                                <div class="deal-price">' . wc_price($prices[$option]) . '</div>
                                            </div>
                                        ';
                                    } else {
                                        echo '
                                            <div class="checkout-attribute-radio-item">
                                                <div class="checkout-attribute-radio-item-wrapper">
                                                    <input type="radio" name="' . $attributeName . '" value="' . $option . '" ' . $checked . ' />
                                                    <span>' . $option . '</span>
                                                </div>
                                                <div class="deal-price">' . wc_price($prices[$option]) . '</div>
                                            </div>';    
                                    }
                                }
?>
                            </div>      
<?php
                        } else {
?>
                            <div class="checkout-attribute-select">
                                <select name="<?php echo $attributeName ?>">
                                    <?php
                                        foreach ($attribute->get_options() as $option) {
                                            if ($defaultAttributes[$attributeName] == $option) {
                                                echo '<option selected="selected">' . $option . '</option>';
                                            } else {
                                                echo '<option>' . $option . '</option>';    
                                            }
                                        }
                                    ?>
                                </select>
                            </div>
<?php
                        }
                    } else if ($attributeName == 'deal') {
                            $highlightDeal = get_post_meta($productId, 'highlight_deal', true);
                            $popularDeal = get_post_meta($productId, 'highlight_deal_popular', true);
?>
                            <div class="checkout-attribute-radio">      
<?php
                                foreach ($attribute->get_options() as $option) {
                                    $checked = '';
                                    if ($defaultAttributes['deal'] == $option) {
                                        $checked = 'checked="checked"';
                                    }
                                    
                                    if ($option == $highlightDeal) {
                                        echo '
                                            <div class="checkout-attribute-radio-item highlight">
                                                <div class="checkout-attribute-radio-item-wrapper">
                                                    <span class="checkout-attribute-radio-arrow"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" width="50" height="110" viewBox="0 0 106 110" enable-background="new 0 0 106 110" xml:space="preserve"><path fill="#fdc301" d="M5.11 29.39c3.87 10.2 9.34 19.72 16.1 28.27 1.8 2.28 3.69 4.49 5.67 6.62 1.02 1.3 2.07 2.58 3.15 3.84 13.79 16.04 35.63 32.15 57.92 28.05 -2.16 0.55-4.3 1.2-6.43 1.81 -6.48 1.82-12.93 3.73-19.37 5.69 -1.62 0.5-1.02 2.82 0.64 2.54 0.86-0.15 1.72-0.3 2.58-0.44 0.15 0.03 0.31 0.03 0.48 0 0.73-0.12 1.47-0.26 2.2-0.4 -0.81 0.28-1.62 0.56-2.43 0.87 -1.51 0.58-1.14 2.62 0.35 2.64 0.25 0.14 0.56 0.2 0.93 0.12 1.13-0.26 2.26-0.53 3.39-0.8 10.43-1.76 20.76-3.99 30.98-6.74 0.45-0.12 0.73-0.38 0.87-0.68 0.63-0.16 1.14-0.74 1.07-1.35 1.18 0.3 2.41-1.16 1.31-2.08 -6.73-5.59-13.53-11.14-20.96-15.66 -3.99-3.15-8.11-6.13-12.38-8.91 -1.23-0.8-2.35 1.12-1.17 1.95 0.43 0.3 0.86 0.6 1.29 0.9 -0.78-0.1-1.3 0.65-1.27 1.39 -0.72-0.42-1.44-0.84-2.17-1.26 -1.45-0.84-2.69 1.27-1.33 2.22 7.08 4.91 14.17 9.83 21.25 14.75 -0.47-0.06-0.95-0.12-1.42-0.19 -9.64-1.95-18.62-6.83-26.79-12.15 -9.06-5.9-17.31-13.04-24.49-21.12C22.61 45.25 13.56 28.59 8.55 10.63 8.12 8.58 7.74 6.52 7.4 4.46c-0.05-0.31-0.27-0.57-0.55-0.75 -0.13-0.6-0.27-1.2-0.39-1.81C6.17 0.5 3.86 0.73 3.93 2.18c0.04 0.92 0.1 1.84 0.16 2.76C-1.93 11.24 2.44 22.37 5.11 29.39zM71.95 104.63c0.88-0.15 1.77-0.3 2.65-0.45 -1.3 0.34-2.6 0.69-3.91 1.03 -0.08-0.1-0.19-0.2-0.3-0.27C70.91 104.85 71.43 104.74 71.95 104.63zM78.59 93.55c1.15 0.28 2.3 0.53 3.46 0.76 -1.2 0.04-2.39 0.02-3.59-0.06C78.56 94.03 78.61 93.79 78.59 93.55zM71.43 93.24c-7.11-1.64-13.96-5.02-20.25-9.31C57.58 87.72 64.37 90.86 71.43 93.24zM34.02 62c7.56 8.3 16.26 15.6 25.82 21.5 0.95 0.59 1.92 1.16 2.89 1.73 -9.77-4.68-18.69-11.17-26.23-19.02 -6.08-6.34-11.18-13.42-15.37-21.01C24.95 51.15 29.26 56.77 34.02 62zM32.87 66.75c-1.44-1.39-2.85-2.83-4.22-4.3 -3.68-4.73-6.96-9.77-9.79-15.05C22.93 54.32 27.59 60.83 32.87 66.75zM4.43 8.76C4.48 9.25 4.54 9.75 4.6 10.25c-0.55 0.21-0.95 0.73-0.75 1.45 0.63 2.25 1.31 4.49 2.04 6.71 1.38 6.97 3.44 13.79 6.13 20.34 -0.63-1.23-1.25-2.48-1.83-3.74 -2.11-4.56-3.88-9.28-5.26-14.11C3.91 17.3 2.78 12.38 4.43 8.76z"></path></svg></span>
                                                    <img src="' . plugin_dir_url(__FILE__) . 'images/best-seller.png" class="checkout-attribute-radio-best-seller" />
                                                    <input type="radio" name="' . $attributeName . '" value="' . $option . '" ' . $checked . ' />
                                                    <span>' . $option . '</span>
                                                </div>
                                                <div class="deal-price">' . wc_price($prices[$option]) . '</div>
                                            </div>
                                        ';
                                    } else if ($option == $popularDeal) {
                                        echo '
                                            <div class="checkout-attribute-radio-item highlight">
                                                <div class="checkout-attribute-radio-item-wrapper">
                                                    <span class="checkout-attribute-radio-arrow"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" width="50" height="110" viewBox="0 0 106 110" enable-background="new 0 0 106 110" xml:space="preserve"><path fill="#fdc301" d="M5.11 29.39c3.87 10.2 9.34 19.72 16.1 28.27 1.8 2.28 3.69 4.49 5.67 6.62 1.02 1.3 2.07 2.58 3.15 3.84 13.79 16.04 35.63 32.15 57.92 28.05 -2.16 0.55-4.3 1.2-6.43 1.81 -6.48 1.82-12.93 3.73-19.37 5.69 -1.62 0.5-1.02 2.82 0.64 2.54 0.86-0.15 1.72-0.3 2.58-0.44 0.15 0.03 0.31 0.03 0.48 0 0.73-0.12 1.47-0.26 2.2-0.4 -0.81 0.28-1.62 0.56-2.43 0.87 -1.51 0.58-1.14 2.62 0.35 2.64 0.25 0.14 0.56 0.2 0.93 0.12 1.13-0.26 2.26-0.53 3.39-0.8 10.43-1.76 20.76-3.99 30.98-6.74 0.45-0.12 0.73-0.38 0.87-0.68 0.63-0.16 1.14-0.74 1.07-1.35 1.18 0.3 2.41-1.16 1.31-2.08 -6.73-5.59-13.53-11.14-20.96-15.66 -3.99-3.15-8.11-6.13-12.38-8.91 -1.23-0.8-2.35 1.12-1.17 1.95 0.43 0.3 0.86 0.6 1.29 0.9 -0.78-0.1-1.3 0.65-1.27 1.39 -0.72-0.42-1.44-0.84-2.17-1.26 -1.45-0.84-2.69 1.27-1.33 2.22 7.08 4.91 14.17 9.83 21.25 14.75 -0.47-0.06-0.95-0.12-1.42-0.19 -9.64-1.95-18.62-6.83-26.79-12.15 -9.06-5.9-17.31-13.04-24.49-21.12C22.61 45.25 13.56 28.59 8.55 10.63 8.12 8.58 7.74 6.52 7.4 4.46c-0.05-0.31-0.27-0.57-0.55-0.75 -0.13-0.6-0.27-1.2-0.39-1.81C6.17 0.5 3.86 0.73 3.93 2.18c0.04 0.92 0.1 1.84 0.16 2.76C-1.93 11.24 2.44 22.37 5.11 29.39zM71.95 104.63c0.88-0.15 1.77-0.3 2.65-0.45 -1.3 0.34-2.6 0.69-3.91 1.03 -0.08-0.1-0.19-0.2-0.3-0.27C70.91 104.85 71.43 104.74 71.95 104.63zM78.59 93.55c1.15 0.28 2.3 0.53 3.46 0.76 -1.2 0.04-2.39 0.02-3.59-0.06C78.56 94.03 78.61 93.79 78.59 93.55zM71.43 93.24c-7.11-1.64-13.96-5.02-20.25-9.31C57.58 87.72 64.37 90.86 71.43 93.24zM34.02 62c7.56 8.3 16.26 15.6 25.82 21.5 0.95 0.59 1.92 1.16 2.89 1.73 -9.77-4.68-18.69-11.17-26.23-19.02 -6.08-6.34-11.18-13.42-15.37-21.01C24.95 51.15 29.26 56.77 34.02 62zM32.87 66.75c-1.44-1.39-2.85-2.83-4.22-4.3 -3.68-4.73-6.96-9.77-9.79-15.05C22.93 54.32 27.59 60.83 32.87 66.75zM4.43 8.76C4.48 9.25 4.54 9.75 4.6 10.25c-0.55 0.21-0.95 0.73-0.75 1.45 0.63 2.25 1.31 4.49 2.04 6.71 1.38 6.97 3.44 13.79 6.13 20.34 -0.63-1.23-1.25-2.48-1.83-3.74 -2.11-4.56-3.88-9.28-5.26-14.11C3.91 17.3 2.78 12.38 4.43 8.76z"></path></svg></span>
                                                    <img src="' . plugin_dir_url(__FILE__) . 'images/most-popular.png" class="checkout-attribute-radio-best-seller" />
                                                    <input type="radio" name="' . $attributeName . '" value="' . $option . '" ' . $checked . ' />
                                                    <span>' . $option . '</span>
                                                </div>
                                                <div class="deal-price">' . wc_price($prices[$option]) . '</div>
                                            </div>
                                        ';
                                    } else {
                                        echo '
                                            <div class="checkout-attribute-radio-item">
                                                <div class="checkout-attribute-radio-item-wrapper">
                                                    <input type="radio" name="' . $attributeName . '" value="' . $option . '" ' . $checked . ' />
                                                    <span>' . $option . '</span>
                                                </div>
                                                <div class="deal-price">' . wc_price($prices[$option]) . '</div>
                                            </div>';    
                                    }
                                }
?>
                            </div>      
<?php
                    } else {
?>
                        <div class="checkout-attribute-select">
                            <select name="<?php echo $attributeName ?>">
                                <?php
                                    foreach ($attribute->get_options() as $option) {
                                        if ($defaultAttributes[$attributeName] == $option) {
                                            echo '<option selected="selected">' . $option . '</option>';
                                        } else {
                                            echo '<option>' . $option . '</option>';    
                                        }
                                    }
                                ?>
                            </select>
                        </div>
<?php
                    }
?>
                </div>
            </div>
<?php
        }
?>
    </div>     
<?php
    return ob_get_clean();
});

add_action('wp_footer', function () {
?>
    <script>
        jQuery(document).ready(function ($) {
            $('.checkout-attribute-image').click(function () {
                var option = $(this).data('option');
                $(this).parent().find('.checkout-attribute-item-value').val(option);
                $(this).parent().find('.checkout-attribute-image').removeClass('active');
                $(this).addClass('active');
                submitAttribute();
            });
            
            $('.checkout-attribute-radio-item input').click(function () {
                submitAttribute();
            });
            
            $('.checkout-attribute-select').change(function () {
                submitAttribute();;
            });
            
            $(document).ajaxComplete(function () {
                if ($('input[name="deal"]').length) {
                    var dealChecked = false;
                    $('input[name="deal"]').each(function () {
                        if ($(this).is(':checked')) {
                            dealChecked = true;
                        }
                    });
                    
                    if (!dealChecked) {
                        $('#place_order').attr('disabled', 'disabled')
                    }
                }
            });
            
            $('.checkout-attribute-radio-item input:checked').trigger('click');
            $('body').trigger('update_checkout');
        }); 
        
        function submitAttribute () {
            var $ = jQuery;
            var url = $('#checkout-attribute').data('url');
            var data = $('#checkout-attribute').find('input, select, textarea').serialize();
            $('#place_order').attr('disabled', 'disabled');
            $.ajax({
                url: url,
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 'success') {
                            $('body').trigger('update_checkout');
                        }
                        $('#place_order').removeAttr('disabled');
                }
            });
        }
    </script>       
<?php
});

add_action('wp_ajax_checkout_attribute', 'checkout_attribute');
add_action('wp_ajax_nopriv_checkout_attribute', 'checkout_attribute');
function checkout_attribute () {
    global $woocommerce;
    $nonce = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($nonce, 'checkout_attribute')) {
        exit;
    }
    
    $productId = sanitize_text_field($_POST['product_id']);
    $product = wc_get_product($productId);
    $attributes = [];
    foreach ($product->get_attributes() as $attributeName => $attribute) {
        if (isset($_POST[$attributeName])) {
            $attributes[$attributeName] = sanitize_text_field($_POST[$attributeName]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => __('All fields are required', 'choose_variation')
            ]);
            exit;
        }
    }
    
    foreach ($product->get_available_variations() as $variation) {
        $exist = true;
        foreach ($attributes as $attributeName => $attribute) {
            if ($variation['attributes']['attribute_' . $attributeName] == $attribute || $variation['attributes']['attribute_' . $attributeName] == '') {
            } else {
                $exist = false;
                break;
            }   
        }
        if ($exist) {
            foreach ($woocommerce->cart->get_cart() as $key => $item) {
                $product = wc_get_product($item['product_id']);
                if ($product->is_type('variable')) {
                    $woocommerce->cart->remove_cart_item($key);
                }
            }
            $quantity = get_post_meta($variation['variation_id'], 'custom_quantity', true);
            if (!$quantity) {
                $quantity = 1;
            }
            $woocommerce->cart->add_to_cart($productId, $quantity, $variation['variation_id']);
            echo json_encode([
                'status' => 'success',
                'price' => $woocommerce->cart->get_cart_total()
            ]);
            exit;
        }
    }
    
    echo json_encode([
        'status' => 'error',
        'message' => __('Product is not available', 'choose_variation')
    ]);
    exit;
}

add_filter('woocommerce_product_data_tabs', function ($tabs) {
    $tabs['highlight_deal'] = [
        'label' =>  __('Highlight Deal', 'choose_variation'),
        'target'  =>  'highlight_deal',
        'priority' => 60,
        'class'   => []
    ];
    $tabs['custom_heading'] = [
        'label' =>  __('Custom Heading', 'choose_variation'),
        'target'  =>  'custom_heading',
        'priority' => 60,
        'class'   => []
    ];
    return $tabs;
});

add_action('woocommerce_product_data_panels', 'highlight_deal');
function highlight_deal () {
    if (isset($_GET['post'])) {
        $productId = sanitize_text_field($_GET['post']);
        $product = wc_get_product($productId);
        $deal = $product->get_attribute('deal');
        if ($deal) {
            $highlightDeal = get_post_meta($productId, 'highlight_deal', true);
            $popularDeal = get_post_meta($productId, 'highlight_popular_deal', true);
            $deals = explode('|', $deal);
?>
            <div id="highlight_deal" class="panel woocommerce_options_panel" style="display: none">
                <div style="padding: 10px">
                    <div>
                        <h3 style="margin-bottom: 5px"><?php echo __('Best Deal', 'choose_variation') ?></h3>
                        <select id="highlight-deal-select" style="float: none">
                            <option value=""></option>
                            <?php
                                foreach ($deals as $deal) {
                                    $deal = trim($deal);
                                    if ($deal == $highlightDeal) {
                                        echo '<option value="' . $deal . '" selected="selected">' . $deal . '</option>';
                                    } else {
                                        echo '<option value="' . $deal . '">' . $deal . '</option>';  
                                    }
                                }
                            ?>
                        </select>
                    </div>
        
                    <div>
                        <h3 style="margin-bottom: 5px"><?php echo __('Most Popular', 'choose_variation') ?></h3>
                        <select id="highlight-deal-popular-select" style="margin-bottom: 10px; float: none">
                            <option value=""></option>
                            <?php
                                foreach ($deals as $deal) {
                                    $deal = trim($deal);
                                    if ($deal == $popularDeal) {
                                        echo '<option value="' . $deal . '" selected="selected">' . $deal . '</option>';
                                    } else {
                                        echo '<option value="' . $deal . '">' . $deal . '</option>';  
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <button id="highlight-deal-button" class="button" data-url="<?php echo admin_url('admin-ajax.php') ?>" data-nonce="<?php echo wp_create_nonce('highlight_deal') ?>" data-product="<?php echo $productId ?>"><?php echo __('Save', 'choose_variation') ?></button>
                </div>
            </div>      
<?php   
        }
    }
}

add_action('woocommerce_product_data_panels', 'custom_heading');
function custom_heading () {
    if (isset($_GET['post'])) {
        $productId = sanitize_text_field($_GET['post']);
        $product = wc_get_product($productId);
        $headings = get_post_meta($productId, 'custom_heading', true);
?>
        <div id="custom_heading" class="panel woocommerce_options_panel" style="display: none">
            <div style="padding: 10px">
                <?php
                    foreach ($product->get_attributes() as $key => $attribute) {
                        if (isset($headings[$key])) {
                            $value = $headings[$key];
                        } else {
                            $value = '';
                        }
                ?>
                        <div style="margin-bottom: 10px">
                            <label style="float: none; display: block; margin: 0px 0px 0px 0px; font-weight: bold;"><?php echo $attribute->get_name() ?></label>
                            <input type="text" name="<?php echo $key ?>" style="float: none" class="custom-heading-fields" value="<?php echo $value ?>" />
                        </div>
                <?php
                    }
                ?>
                <button id="custom-heading-button" class="button" data-url="<?php echo admin_url('admin-ajax.php') ?>" data-nonce="<?php echo wp_create_nonce('custom_heading') ?>" data-product="<?php echo $productId ?>"><?php echo __('Save', 'choose_variation') ?></button>
            </div>
        </div>
<?php
    }
}

add_action('admin_footer', function () {
?>
    <script>
        jQuery(document).ready(function ($) {
            $('#highlight-deal-select').change(function () {
                if ($(this).val().trim() != '') {
                    $('#highlight-deal-popular-select').val('');
                }   
            });
            
            $('#highlight-deal-popular-select').change(function () {
                if ($(this).val().trim() != '') {
                    $('#highlight-deal-select').val('');
                }   
            });
            
            $('#highlight-deal-button').click(function (e) {
                e.preventDefault();
                $(this).text('Saving...').prop('disabled', true);
                var url = $(this).data('url');
                var nonce = $(this).data('nonce');
                var deal = $('#highlight-deal-select').val();
                var popularDeal = $('#highlight-deal-popular-select').val();
                var productId = $(this).data('product');
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                        action: 'highlight_deal',
                        deal: deal,
                        popular: popularDeal,
                        product: productId,
                        nonce: nonce
                    },
                    success: function () {
                        $('#highlight-deal-button').prop('disabled', false).text('Save');
                    }
                });
            });

            $('#custom-heading-button').click(function (e) {
                e.preventDefault();
                $(this).text('Saving...').prop('disabled', true);
                var url = $(this).data('url');
                var nonce = $(this).data('nonce');
                var productId = $(this).data('product');
                var data = {
                    action: 'custom_heading',
                    nonce: nonce,
                    product: productId
                };
                $('.custom-heading-fields').each(function () {
                    var name = $(this).attr('name');
                    var value = $(this).val().trim();
                    data[name] = value;
                });
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: data,
                    success: function () {
                        $('#custom-heading-button').prop('disabled', false).text('Save');
                    }
                });
            });
        });
    </script>       
<?php
});

add_action('wp_ajax_highlight_deal', function () {
    $nonce = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($nonce, 'highlight_deal')) {
        exit;
    }
    $productId = sanitize_text_field($_POST['product']);
    $deal = sanitize_text_field($_POST['deal']);
    $popular = sanitize_text_field($_POST['popular']);
    update_post_meta($productId, 'highlight_deal', $deal);
    update_post_meta($productId, 'highlight_deal_popular', $popular);
    exit;
});

add_action('wp_ajax_custom_heading', function () {
    $nonce = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($nonce, 'custom_heading')) {
        exit;
    }
    $productId = sanitize_text_field($_POST['product']);
    $product = wc_get_product($productId);
    foreach ($product->get_attributes() as $key => $attribute) {
        if (isset($_POST[$key])) {
            $data[$key] = sanitize_text_field($_POST[$key]);
        }
    }
    update_post_meta($productId, 'custom_heading', $data);
    exit;
});

add_action('woocommerce_variation_options_pricing', function ($loop, $variationData, $variation) {
    $quantity = get_post_meta($variation->ID, 'custom_quantity', true);
?>
    <p class="form-field form-row form-row-first">
        <label>Quantity</label>
        <input type="text" class="short" name="custom_quantity[<?php echo $loop ?>]" value="<?php echo $quantity ?>" />
    </p>        
<?php
}, 10, 3);

add_action('woocommerce_save_product_variation', function ($variationId, $loop) {
    if (isset($_POST['custom_quantity'][$loop])) {
        $quantity = $_POST['custom_quantity'][$loop];
        update_post_meta($variationId, 'custom_quantity', $quantity);   
    }
}, 10, 2);

add_action('woocommerce_checkout_create_order', function ($order) {
    foreach ($order->get_items() as $item) {
        $names = explode('-', $item->get_name());
        $item->set_name(trim($names[0]));
        $item->save();  
    }
    $order->save();
});