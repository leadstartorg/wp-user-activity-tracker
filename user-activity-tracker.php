<?php
/**
 * Plugin Name: User Activity Tracker
 * Description: Tracks user post views, activity, and profile changes.
 * Version: 1.0.0
 * Author: Leadstart Media, Inc.
 * License: GPL-2.0-or-later
 */

/**
 * Post Views with User Metadata (Eastern Time, Limited to 10).
 */
function uat_set_user_view() {
    $post_id = get_the_ID();
    $user_id = get_current_user_id();

    if ($user_id) {
        $post = get_post($post_id);
        $author = get_userdata($post->post_author);

        // Eastern Time Zone
        $timezone = new DateTimeZone('America/New_York');
        $datetime = new DateTime('now', $timezone);
        $timestamp_viewed = $datetime->format('Y-m-d H:i:s');

        $view_data = array(
            'post_id' => $post_id,
            'post_type' => $post->post_type,
            'post_title' => $post->post_title,
            'post_url' => get_permalink($post_id),
            'post_author' => $author->display_name,
            'author_role' => isset($author->roles[0]) ? $author->roles[0] : '',
            'gravatar' => get_avatar_url($author->ID),
            'timestamp_published' => get_the_date('Y-m-d H:i:s', $post_id),
            'timestamp_viewed' => $timestamp_viewed,
        );

        $user_views = get_user_meta($user_id, 'user_post_views', true);

        if (empty($user_views) || !is_array($user_views)) {
            $user_views = array();
        }

        $user_views[] = $view_data;

        // Limit to 100 entries
        if (count($user_views) > 100) {
            array_shift($user_views); // Remove the oldest entry
        }

        update_user_meta($user_id, 'user_post_views', $user_views);
    }
}
add_action('wp_head', 'uat_trigger_set_user_view');
function uat_trigger_set_user_view() {
    if (is_singular()) {
        uat_set_user_view();
    }
}

/**
 * Track Post Creation and Updates for Specific Users.
 */
function uat_track_user_post_activity($post_id, $post, $update) {
    // Check if it's a valid post and not an auto-save.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Get the author ID.
    $author_id = $post->post_author;

    // Get the author data
    $author = get_userdata($author_id);

    // Eastern Time Zone
    $timezone = new DateTimeZone('America/New_York');
    $datetime = new DateTime('now', $timezone);
    $timestamp = $datetime->format('Y-m-d H:i:s');

    // Activity data.
    $activity_data = array(
        'post_id' => $post_id,
        'post_title' => $post->post_title,
        'post_type' => $post->post_type,
        'post_status' => $post->post_status,
        'timestamp' => $timestamp,
        'action' => $update ? 'updated' : 'created', // Determine if it's an update or creation
        'author_role' => isset($author->roles[0]) ? $author->roles[0] : '',
    );

    // Get existing activity data or create an empty array.
    $user_activity = get_user_meta($author_id, 'user_post_activity', true);
    if (empty($user_activity) || !is_array($user_activity)) {
        $user_activity = array();
    }

    // Add the new activity data.
    $user_activity[] = $activity_data;

    // Limit to 100 entries.
    if (count($user_activity) > 100) {
        array_shift($user_activity);
    }

    // Update the user metadata.
    update_user_meta($author_id, 'user_post_activity', $user_activity);
}

add_action('save_post', 'uat_track_user_post_activity', 10, 3);

/**
 * Track User Profile Changes (Display Name, Social Metadata, Bio, Website).
 */
function uat_track_user_profile_changes($user_id) {
    $user = get_userdata($user_id);

    // Get old values or set defaults if they don't exist
    $old_display_name = get_user_meta($user_id, '_uat_old_display_name', true) ?: '';
    $old_nickname = get_user_meta($user_id, '_uat_old_nickname', true) ?: '';
    $old_nicename = get_user_meta($user_id, '_uat_old_nicename', true) ?: '';
    $old_social_meta = get_user_meta($user_id, '_uat_old_social_meta', true) ?: array();
    $old_bio = get_user_meta($user_id, '_uat_old_bio', true) ?: '';
    $old_website = get_user_meta($user_id, '_uat_old_website', true) ?: '';
    $old_contact_methods = get_user_meta($user_id, '_uat_old_contact_methods', true) ?: array();

    // Get all social metadata
    $social_meta = array(
        'facebook' => get_user_meta($user_id, 'facebook', true),
        'twitter' => get_user_meta($user_id, 'twitter', true),
        'disqus' => get_user_meta($user_id, 'disqus', true),
        'instagram' => get_user_meta($user_id, 'instagram', true),
        'pinterest' => get_user_meta($user_id, 'pinterest', true),
        'behance' => get_user_meta($user_id, 'behance', true),
        'dribbble' => get_user_meta($user_id, 'dribbble', true),
        'github' => get_user_meta($user_id, 'github', true),
        'discord' => get_user_meta($user_id, 'discord', true),
        'soundcloud' => get_user_meta($user_id, 'soundcloud', true),
        'spotify' => get_user_meta($user_id, 'spotify', true),
        'vimeo' => get_user_meta($user_id, 'vimeo', true),
        'youtube' => get_user_meta($user_id, 'youtube', true),
        'medium' => get_user_meta($user_id, 'medium', true),
        'reddit' => get_user_meta($user_id, 'reddit', true),
        'skype' => get_user_meta($user_id, 'skype', true),
    );

    $bio = $user->description;
    $website = $user->user_url;
    $nickname = $user->nickname;
    $nicename = $user->user_nicename;

    //Get contact methods
    $contact_methods = apply_filters('user_contactmethods', array(), $user);

    $user_contact_values = array();
    foreach($contact_methods as $key => $label){
        $user_contact_values[$key] = get_user_meta($user_id, $key, true);
    }

    if ($old_display_name !== $user->display_name || $old_nickname !== $nickname || $old_nicename !== $nicename || serialize($old_social_meta) !== serialize($social_meta) || $old_bio !== $bio || $old_website !== $website || serialize($old_contact_methods) !== serialize($user_contact_values)) {
        // Log the change
        $timezone = new DateTimeZone('America/New_York');
        $datetime = new DateTime('now', $timezone);
        $timestamp = $datetime->format('Y-m-d H:i:s');
        $change_log = array(
            'timestamp' => $timestamp,
            'display_name' => $user->display_name,
            'nickname' => $nickname,
            'nicename' => $nicename,
            'social_meta' => $social_meta,
            'bio' => $bio,
            'website' => $website,
            'contact_methods' => $user_contact_values,
            'old_display_name' => $old_display_name,
            'old_nickname' => $old_nickname,
            'old_nicename' => $old_nicename,
            'old_social_meta' => $old_social_meta,
            'old_bio' => $old_bio,
            'old_website' => $old_website,
            'old_contact_methods' => $old_contact_methods,
        );

        $user_profile_changes = get_user_meta($user_id, 'user_profile_changes', true);
        if (empty($user_profile_changes) || !is_array($user_profile_changes)) {
            $user_profile_changes = array();
        }
        $user_profile_changes[] = $change_log;
        if (count($user_profile_changes) > 10) {
            array_shift($user_profile_changes);
        }
        update_user_meta($user_id, 'user_profile_changes', $user_profile_changes);

        // Update the old values for future comparison
        update_user_meta($user_id, '_uat_old_display_name', $user->display_name);
        update_user_meta($user_id, '_uat_old_nickname', $nickname);
        update_user_meta($user_id, '_uat_old_nicename', $nicename);
        update_user_meta($user_id, '_uat_old_social_meta', $social_meta);
        update_user_meta($user_id, '_uat_old_bio', $bio);
        update_user_meta($user_id, '_uat_old_website', $website);
        update_user_meta($user_id, '_uat_old_contact_methods', $user_contact_values);
    }
}
add_action('profile_update', 'uat_track_user_profile_changes');

/**
 * Track Social Login Activity (WSL).
 */
function uat_track_wsl_login_activity($user_id) {
    // Eastern Time Zone
    $timezone = new DateTimeZone('America/New_York');
    $datetime = new DateTime('now', $timezone);
    $timestamp = $datetime->format('Y-m-d H:i:s');
    $ip_address = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';

    $login_data = array(
        'timestamp' => $timestamp,
        'ip_address' => $ip_address, // Get user's IP address
    );

    $user_logins = get_user_meta($user_id, 'user_logins', true);
    if (empty($user_logins) || !is_array($user_logins)) {
        $user_logins = array();
    }

    $user_logins[] = $login_data;

    if (count($user_logins) > 10) {
        array_shift($user_logins);
    }

    update_user_meta($user_id, 'user_logins', $user_logins);
}
add_action('wsl_process_login', 'uat_track_wsl_login_activity');

/**
 * Track Login Activity.
 */
function uat_track_login_activity($user_login, $user) {
    $user_id = $user->ID;

    // Eastern Time Zone
    $timezone = new DateTimeZone('America/New_York');
    $datetime = new DateTime('now', $timezone);
    $timestamp = $datetime->format('Y-m-d H:i:s');
    $ip_address = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';


    $login_data = array(
        'timestamp' => $timestamp,
        'ip_address' => $ip_address, // Get user's IP address
    );

    $user_logins = get_user_meta($user_id, 'user_logins', true);
    if (empty($user_logins) || !is_array($user_logins)) {
        $user_logins = array();
    }

    $user_logins[] = $login_data;

    if (count($user_logins) > 10) {
        array_shift($user_logins);
    }

    update_user_meta($user_id, 'user_logins', $user_logins);
}
add_action('wp_login', 'uat_track_login_activity', 10, 2);

/**
 * Track Logout Activity.
 */
function uat_track_logout_activity() {
    $user_id = get_current_user_id();

    if ($user_id) {
        // Eastern Time Zone
        $timezone = new DateTimeZone('America/New_York');
        $datetime = new DateTime('now', $timezone);
        $timestamp = $datetime->format('Y-m-d H:i:s');

        $logout_data = array(
            'timestamp' => $timestamp,
        );

        $user_logouts = get_user_meta($user_id, 'user_logouts', true);
        if (empty($user_logouts) || !is_array($user_logouts)) {
            $user_logouts = array();
        }

        $user_logouts[] = $logout_data;

        if (count($user_logouts) > 10) {
            array_shift($user_logouts);
        }

        update_user_meta($user_id, 'user_logouts', $user_logouts);
    }
}
add_action('wp_logout', 'uat_track_logout_activity');

/**
 * Track WooCommerce Purchase Activity (Individual Products).
 */
function uat_track_purchase_activity($order_id) {
    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();

    if ($user_id) {
        // Eastern Time Zone
        $timezone = new DateTimeZone('America/New_York');
        $datetime = new DateTime('now', $timezone);
        $timestamp = $datetime->format('Y-m-d H:i:s');

        $items = $order->get_items();

        foreach ($items as $item) {
            $product = $item->get_product();
            if ($product) {
                $product_data = array(
                    'timestamp' => $timestamp,
                    'product_id' => $product->get_id(),
                    'name' => $product->get_name(),
                    'quantity' => $item->get_quantity(),
                    'post_type' => $product->get_type(),
                );

                $user_purchases = get_user_meta($user_id, 'user_purchases', true);
                if (empty($user_purchases) || !is_array($user_purchases)) {
                    $user_purchases = array();
                }

                $user_purchases[] = $product_data;

                if (count($user_purchases) > 100) {
                    array_shift($user_purchases);
                }

                update_user_meta($user_id, 'user_purchases', $user_purchases);
            }
        }
    }
}
add_action('woocommerce_order_status_completed', 'uat_track_purchase_activity');

/**
 * All Activity.
 */
function uat_get_combined_user_data($user_id) {
    $combined_data = array();

    // Post Views
    $views = get_user_meta($user_id, 'user_post_views', true);
    if (!empty($views) && is_array($views)) {
        foreach ($views as $view) {
            $combined_data[] = array(
                'timestamp' => $view['timestamp_viewed'],
                'type' => 'view',
                'data' => $view,
            );
        }
    }

    // Post Activity
    $activity = get_user_meta($user_id, 'user_post_activity', true);
    if (!empty($activity) && is_array($activity)) {
        foreach ($activity as $act) {
            $combined_data[] = array(
                'timestamp' => $act['timestamp'],
                'type' => 'activity',
                'data' => $act,
            );
        }
    }

    // Profile Changes
    $profile_changes = get_user_meta($user_id, 'user_profile_changes', true);
    if (!empty($profile_changes) && is_array($profile_changes)) {
        foreach ($profile_changes as $change) {
            $combined_data[] = array(
                'timestamp' => $change['timestamp'],
                'type' => 'profile_change',
                'data' => $change,
            );
        }
    }

    // Purchases
    $purchases = get_user_meta($user_id, 'user_purchases', true);
    if (!empty($purchases) && is_array($purchases)) {
        foreach ($purchases as $purchase) {
            $combined_data[] = array(
                'timestamp' => $purchase['timestamp'],
                'type' => 'purchase',
                'data' => $purchase,
            );
        }
    }

    // Logins
    $logins = get_user_meta($user_id, 'user_logins', true);
    if (!empty($logins) && is_array($logins)) {
        foreach ($logins as $login) {
            $combined_data[] = array(
                'timestamp' => $login['timestamp'],
                'type' => 'login',
                'data' => $login,
            );
        }
    }

    // Logouts
    $logouts = get_user_meta($user_id, 'user_logouts', true);
    if (!empty($logouts) && is_array($logouts)) {
        foreach ($logouts as $logout) {
            $combined_data[] = array(
                'timestamp' => $logout['timestamp'],
                'type' => 'logout',
                'data' => $logout,
            );
        }
    }

    return $combined_data;
}

function uat_sort_combined_data($a, $b) {
    return strtotime($b['timestamp']) - strtotime($a['timestamp']);
}

function uat_display_combined_data_shortcode($atts) {
    $atts = shortcode_atts(array(
        'user_id' => get_current_user_id(),
    ), $atts);

    $user_id = intval($atts['user_id']);
    if (!$user_id) {
        return '<p>Invalid user ID.</p>';
    }

    $combined_data = uat_get_combined_user_data($user_id);
    usort($combined_data, 'uat_sort_combined_data');

    if (empty($combined_data)) {
        return '<p>No user activity found.</p>';
    }

    $output = '<h3>User Activity:</h3>';
    $output .= '<ul>';

    foreach ($combined_data as $item) {
        $output .= '<li>';
        //$timestamp = esc_html($item['timestamp']);
        //$date = date('Y-m-d', strtotime($timestamp));
        //$time = date('H:i:s', strtotime($timestamp));
        //$type = esc_html($item['type']);
        //$data = $item['data'];

        $timestamp = strtotime($item['timestamp']);

        $timezone = new DateTimeZone('America/New_York');
        $datetime = new DateTime("@$timestamp");
        $datetime->setTimezone($timezone);

        $date = $datetime->format('Y-m-d');
        $time = $datetime->format('H:i:s');
        $type = esc_html($item['type']);
        $data = $item['data'];

        switch ($type) {
            case 'view':
                $output .= $date . ' ' . $time . ' - Viewed: <a href="' . esc_url($data['post_url']) . '">' . esc_html($data['post_title']) . '</a> <small>(' . esc_html($data['post_type']) . ')</small>';
                break;
            case 'activity':
                $output .= $date . ' ' . $time . ' - ' . esc_html($data['action']) . ' <a href="' . get_permalink($data['post_id']) . '">' . esc_html($data['post_title']) . '</a> <small>(' . esc_html($data['post_type']) . ', ' . esc_html($data['post_status']) . ')</small>';
                break;
            case 'profile_change':
                $output .= $date . ' ' . $time . ' - Profile Changed: ';

                if ($data['old_display_name'] !== $data['display_name']) {
                    $output .= 'Display Name: ' . esc_html($data['display_name']) . '<br>';
                }
                if ($data['old_nickname'] !== $data['nickname']) {
                    $output .= 'Nickname: ' . esc_html($data['nickname']) . '<br>';
                }
                if ($data['old_nicename'] !== $data['nicename']) {
                    $output .= 'Nicename: ' . esc_html($data['nicename']) . '<br>';
                }
                if ($data['old_bio'] !== $data['bio']) {
                    $output .= 'Bio: ' . esc_html($data['bio']) . '<br>';
                }
                if ($data['old_website'] !== $data['website']) {
                    $output .= 'Website: ' . esc_html($data['website']) . '<br>';
                }

                foreach ($data['social_meta'] as $key => $value) {
                    if (isset($data['old_social_meta'][$key]) && $data['old_social_meta'][$key] !== $value) {
                        if (!empty($value)) {
                            $output .= esc_html(ucfirst($key)) . ': ' . esc_html($value) . '<br>';
                        }
                    } elseif (!isset($data['old_social_meta'][$key]) && !empty($value)) {
                        $output .= esc_html(ucfirst($key)) . ': ' . esc_html($value) . '<br>';
                    }
                }

                // Display Contact Methods
                $contact_methods = apply_filters('user_contactmethods', array(), get_userdata($atts['user_id']));
                foreach ($data['contact_methods'] as $key => $value) {
                    if (isset($data['old_contact_methods'][$key]) && $data['old_contact_methods'][$key] !== $value) {
                        if (!empty($value)) {
                            $output .= esc_html($contact_methods[$key]) . ': ' . esc_html($value) . '<br>';
                        }
                    } elseif (!isset($data['old_contact_methods'][$key]) && !empty($value)) {
                        $output .= esc_html($contact_methods[$key]) . ': ' . esc_html($value) . '<br>';
                    }
                }
                break;
            case 'login':
                $output .= $date . ' ' . $time . ' - Login (IP: ' . esc_html($data['ip_address']) . ')';
                break;
            case 'logout':
                $output .= $date . ' ' . $time . ' - Logout';
                break;
            case 'purchase':
                $output .= $date . ' ' . $time . ' - Purchased: ' . esc_html($data['name']) . ' (x' . esc_html($data['quantity']) . '), Product ID: ' . esc_html($data['product_id']) . ' <small>(' . esc_html($data['post_type']) . ')</small>';
                break;
        }

        $output .= '</li>';
    }

    $output .= '</ul>';
    return $output;
}
add_shortcode('uat_combined_data', 'uat_display_combined_data_shortcode');
