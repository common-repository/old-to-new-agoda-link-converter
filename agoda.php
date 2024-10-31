<?php ob_start();ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if ( ! defined( 'ABSPATH' ) ) exit;
include 'includes/AGLinkConverter.php';
include 'includes/AgSqlInsert.php';
global $wpdb;
/**
 * Plugin Name:  Old-to-New Agoda Link Converter
 * Plugin URI: http://www.agoda.com
 * Description: With the Old-to-New Agoda Link Converter plug-in, existing old link structure Agoda affiliate links will be converted to new link structures for improved tracking as well as automatic conversion of popular destination keywords to Agoda affiliate links.
 * Version: 1.7.1
 * Author: Agoda Partners
 * Author URI: https://partners.agoda.com
 * License: GPL2
 */


function wp_agoda_admin_notice() {
    global $wpdb;
    $settings = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."agoda_settings  WHERE setting_id = '1'", ARRAY_A);

    if($settings
        ['affiliate_id'] == 'XXXXXX'):

    ?>
    <div class="notice notice-warning">
        <p><?php _e( '<A href=/wp-admin/admin.php?page=agoda-plugin>Please enter your CID in the Agoda</A> - Old-to-New Agoda Link Converter. ' , 'sample-text-domain' ); ?></p>
    </div>
    <?php endif;?>
    <?php
}
add_action('admin_notices', 'wp_agoda_admin_notice');

/**
 * Activation settings
 */

register_activation_hook( __FILE__, 'agoda_activate' );
function agoda_activate()
{
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}agoda_settings` (
          `setting_id` int(11) NOT NULL DEFAULT '1',
          `affiliate_id` VARCHAR(255) NULL DEFAULT 'XXXXXX',
		  `max_links_per_page` int(11)  DEFAULT 10,
		  `disable_keyword_conversion` int(4) DEFAULT 0,
		  `exclude_post_ids` VARCHAR (255)  NULL,
		  `exclude_cat_ids` VARCHAR (255)  DEFAULT ''
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;
		
		INSERT INTO `{$wpdb->prefix}agoda_settings` (`setting_id`, `max_links_per_page`, `disable_keyword_conversion`) VALUES
		(1, '10', '1');";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}agoda_countries` (
          `dest` varchar(255) NOT NULL DEFAULT '',
          `url` varchar(255) NOT NULL DEFAULT '',
          INDEX `dest` (`dest`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
    dbDelta($sql);

    $AgInsert = new AgSqlInsert();
    $AgInsert->fetchPopulairDestinations();








}

add_action('activated_plugin','my_save_error');
function my_save_error()
{
    file_put_contents(dirname(__file__).'/error_activation.txt', ob_get_contents());
}

register_deactivation_hook( __FILE__, 'agoda_deactivate' );
function agoda_deactivate()
{
    global $wpdb;
    $wpdb->query("DROP TABLE ".$wpdb->prefix ."agoda_settings");
    $wpdb->query("DROP TABLE ".$wpdb->prefix ."agoda_countries");
}


/**
 * Add the menu
 */
function agoda_plugin_menu()
{
    add_menu_page('Agoda','Agoda Site Configuration', 'manage_options', 'agoda-plugin', 'agoda_settings');
    add_action('admin_menu', 'agoda-config');
}
add_action('admin_menu', 'agoda_plugin_menu');




/**
 * Settings for the plugin
 */
function agoda_settings()
{

    include 'includes/settings.php';
}



/**
 * Add the filter to change the links...
 */
add_filter( 'the_content', 'agoda_the_content_filter' );
function agoda_the_content_filter( $content ) {
    global $wpdb;
    $settings = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."agoda_settings  WHERE setting_id = '1'", ARRAY_A);
    $categories = get_the_category();
    $cat_id = -1;
    if(count($categories) > 0 ) {
        $cat_id = (int) @$categories[0]->cat_ID;
    }
    $exclude_post_ids = explode(',',@$settings['exclude_post_ids']);
    $exclude_cat_ids = explode(',',@$settings['exclude_cat_ids']);
    $post = get_post();
    $max_links_per_page = $settings['max_links_per_page'];
    $doc = new \DOMDocument();
    libxml_use_internal_errors(true);
    if(!empty($content)) {
        $old_content = $content;
        $doc->loadHTML($content);
        $tags = $doc->getElementsByTagName('a');
        $Linkchanger = new AGLinkConverter_Linkchanger($settings['affiliate_id']);
        foreach ($tags as $tag) {

            if(strpos($tag->getAttribute('href'),'agoda.com') !== false ) {

                if(strpos($tag->getAttribute('href'),'cid') !== false) {
                    $content = str_replace($tag->getAttribute('href'),$Linkchanger->contentChangerFactory($tag->getAttribute('href')),$content);

                }

            }
        }


//        if($settings['disable_keyword_conversion'] == 1) {
//            if(array_search($post->ID,$exclude_post_ids)  === false ) {
//                $content = $Linkchanger->transformPopulairDestinations($content,$max_links_per_page);
//            }
//            if(array_search($cat_id,$exclude_cat_ids)  !== false) {
//                $content = $old_content;
//            }
//        }


    }



    return $content;
}

