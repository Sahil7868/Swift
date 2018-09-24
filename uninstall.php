<?php 

/**

* Trigger this file on plugin uninstall


*/

if(! defined('WP_UNINSTALL_PLUGIN'))
{
    die;
}

// Access database via sql
global $wpdb;

$wpdb->query("DELETE FROM wp_posts WHERE post_type = 'sw2_images'");
$wpdb->query("DELETE FROM wp_term_taxonomy WHERE taxonomy = 'sw_category'");
$wpdb->query("DELETE FROM wp_postmeta WHERE post_id NOT IN(SELECT id FROM wp_posts)" );
$wpdb->query("DELETE FROM wp_term_relationships WHERE object_id NOT IN(SELECT id FROM wp_posts)" );
