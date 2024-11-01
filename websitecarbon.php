<?php

/**
 * Plugin Name: Website Carbon
 * Plugin URI: https://bitbucket.org/jmstopper/website-carbon
 * Description: Measure the carbon emissions of your website right inside WordPress
 * Version: 1.1.3
 * Requires PHP: 7.1
 * Tested up to: 6.1
 * Author: beleaf
 * Author URI: https://beleaf.au/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: website-carbon
 */

namespace WebsiteCarbon;

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}
if (! class_exists('\WebsiteCarbon\WebsiteCarbon')) {
    class WebsiteCarbon
    {
        public static function init(): void
        {
            // Check on page load if we need to run a test
            add_action('admin_init', [__CLASS__, 'checkURL']);

            // Add the admin columns
            add_filter('manage_posts_columns', [__CLASS__, 'addEmissionsColumns']);
            add_filter('manage_pages_columns', [__CLASS__, 'addEmissionsColumns']);

            // Add the content to the admin columns
            add_action('manage_pages_custom_column', [__CLASS__, 'addEmissionsColumnsContent'], 5, 2);
            add_action('manage_posts_custom_column', [__CLASS__, 'addEmissionsColumnsContent'], 5, 2);

            // Add the dashboard widget
            add_action('wp_dashboard_setup', [__CLASS__, 'wpDashboardSetup']);

            // Add a management page to show all the results
            add_action('admin_menu', [__CLASS__, 'addAdminMenus']);

            // Add admins scripts
            add_action('admin_enqueue_scripts', [__CLASS__, 'adminEnqueueScripts']);

            // Add AJAX handler
            add_action('wp_ajax_websitecarbon-total', [__CLASS__, 'ajaxTotal']);
            add_action('wp_ajax_websitecarbon-posts', [__CLASS__, 'ajaxPosts']);
            add_action('wp_ajax_websitecarbon-measure', [__CLASS__, 'ajaxMeasure']);
        }

        public static function wpDashboardSetup(): void
        {
            wp_add_dashboard_widget(
                'websitecarbon-dashboard-widget-worst',
                __(
                    'Website Carbon - Highest emissions',
                    'website-carbon'
                ),
                [__CLASS__, 'dashboardWidgetContentWorst']
            );

            wp_add_dashboard_widget(
                'websitecarbon-dashboard-widget-best',
                __('Website Carbon - Lowest emissions', 'website-carbon'),
                [__CLASS__, 'dashboardWidgetContentBest']
            );
        }

        public static function dashboardWidgetContentWorst(): void
        {
            $posts = self::getPosts([
                'pagination' => 0,
                'order' => 'DESC',
            ]);

            include 'views/dashboard-widget.php';
        }

        public static function dashboardWidgetContentBest(): void
        {
            $posts = self::getPosts([
                'pagination' => 0,
                'order' => 'ASC',
            ]);

            include 'views/dashboard-widget.php';
        }

        public static function ajaxTotal(): void
        {
            if (!wp_verify_nonce($_REQUEST['nonce'], 'websitecarbon-nonce')) {
                wp_send_json(
                    [
                    'error' => __('Invalid nonce', 'website-carbon')
                    ]
                );
            }

            $processed = isset($_REQUEST['processed'])
                ? self::normaliseProcessed($_REQUEST['processed'])
                : self::normaliseProcessed();

            wp_send_json(
                [
                'total' => count(
                    self::getPosts(
                        [
                        'processed' => $processed
                        ]
                    )
                ),
                'nonce' => wp_create_nonce('websitecarbon-nonce'),
                ]
            );
        }

        public static function ajaxPosts(): void
        {
            if (!wp_verify_nonce($_REQUEST['nonce'], 'websitecarbon-nonce')) {
                wp_send_json([
                    'error' => __('Invalid nonce', 'website-carbon')
                ]);
            }

            $processed = isset($_REQUEST['processed'])
                ? self::normaliseProcessed($_REQUEST['processed'])
                : self::normaliseProcessed();

            wp_send_json([
                'posts' => self::getPosts(
                    [
                    'processed' => $processed,
                    'pagination' => isset($_REQUEST['pagination']) ? intval($_REQUEST['pagination']) : 0,
                    ]
                ),
                'nonce' => wp_create_nonce('websitecarbon-nonce'),
            ]);
        }

        public static function ajaxMeasure(): void
        {
            if (!wp_verify_nonce($_REQUEST['nonce'], 'websitecarbon-nonce')) {
                wp_send_json(
                    [
                    'error' => __('Invalid nonce', 'website-carbon')
                    ]
                );
            }

            // Alias so we dont keep making mistakes
            $postID = intval($_REQUEST['id']);

            // If there was an error
            if (!self::processPost($postID)) {
                wp_send_json(
                    [
                    'id' => $postID,
                    'error' => __('Sorry, something went wrong', 'website-carbon'),
                    'nonce' => wp_create_nonce('websitecarbon-nonce'),
                    ]
                );
            }

            wp_send_json(
                [
                'id' => $postID,
                'co2' => self::getEmissionsValue($postID),
                'nonce' => wp_create_nonce('websitecarbon-nonce'),
                ]
            );
        }

        public static function adminEnqueueScripts($hook): void
        {
            if ('tools_page_websitecarbon' != $hook) {
                return;
            }

            wp_enqueue_script(
                'websitecarbon-admin',
                plugin_dir_url(__FILE__) . 'assets/scripts/admin.js',
                ['jquery'],
                self::getPluginVersion()
            );

            wp_localize_script(
                'websitecarbon-admin',
                'websitecarbonvars',
                [
                'ajax' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('websitecarbon-nonce')
                ]
            );

            wp_enqueue_style(
                'websitecarbon-admin',
                plugin_dir_url(__FILE__) . 'assets/styles/admin.css',
                [],
                self::getPluginVersion()
            );
        }

        public static function addAdminMenus(): void
        {
            add_management_page(
                __('Website Carbon', 'website-carbon'),
                __('Website Carbon', 'website-carbon'),
                'install_plugins',
                'websitecarbon',
                [__CLASS__, 'viewTools']
            );
        }

        public static function viewTools(): void
        {
            include 'views/tools.php';
        }

        public static function checkURL(): void
        {
            if (isset($_REQUEST['wc-action'])) {
                if ($_REQUEST['wc-action'] === 'measure-emissions') {
                    if (isset($_REQUEST['post'])) {
                        self::processPost($_REQUEST['post']);
                    }
                } elseif ($_REQUEST['wc-action'] === 'download') {
                    self::downloadAsCSV();
                }
            }
        }

        public static function addEmissionsColumns(array $columns): array
        {
            // If we are not on an edit screen, we don't want to show the column
            if (self::currentAdminPage() !== 'edit.php') {
                return $columns;
            }

            // If the post type is not public, we can't test the page
            $postTypeObject = get_post_type_object(get_query_var('post_type'));

            if ($postTypeObject->public !== true) {
                return $columns;
            }

            $columns['co2ppv'] = __('CO2 Emissions', 'website-carbon');

            return $columns;
        }

        public static function addEmissionsColumnsContent(string $columnName, int $postID): void
        {
            if ($columnName === 'co2ppv') {
                echo self::getEmissionsString($postID);
            }
        }

        private static function getPosts($args = []): array
        {
            $args = wp_parse_args(
                $args,
                [
                // Real WP_Query parameters
                'post_status' => 'publish', // Only published posts are useful
                'posts_per_page' => -1, // Will get overwritten if needed
                'order' => '',

                // Fake parameters that we transform in to something else
                'processed' => '', // true, false, or empty string
                'pagination' => -1, // -1 means get all, otherwise its the page to get the posts for
                ]
            );

            // Get all truly public post types
            $postTypes = get_post_types(
                [
                'public' => true,
                ]
            );

            // Remove blacklisted post types and add the post types to the query args
            $args['post_type'] = array_filter(
                $postTypes,
                function ($postType) {
                    return !in_array($postType, ['attachment']);
                }
            );

            if ($args['order'] !== '') {
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = 'wc-co2';
            }

            // Do we want to do some pagination?
            if (is_int($args['pagination']) && $args['pagination'] !== -1) {
                // The default amount of posts to get per page
                $perPage = 10;

                $args['posts_per_page'] = $perPage;

                // Calculate the offset as a multiple of pagination
                $args['offset'] = $perPage * $args['pagination'];
            }

            // Are we looking for posts that have or haven't been transformed?
            if (is_bool($args['processed'])) {
                // Now we need to do a meta query for either having been processed
                // or now. Processed should mean theres a piece of post meta in
                // existence for a post.

                // a literal SQL command to test the meta with
                $compare = $args['processed'] === true ? 'EXISTS' : 'NOT EXISTS';

                $args['meta_query'] = [
                    [
                        'key' => 'wc-report', // The post meta name we are looking for
                        'compare' => $compare
                    ]
                ];
            }

            // Remove parameters that are not valid wp_query parameters
            unset($args['processed']);
            unset($args['pagination']);

            // Make the query
            $posts = new \WP_Query($args);

            return array_map(
                function ($myPost) {
                    return [
                    'id' => $myPost->ID,
                    'title' => get_the_title($myPost),
                    ];
                },
                $posts->posts
            );
        }

        private static function normaliseProcessed($value = '')
        {
            if ($value === 'true') {
                return true;
            }

            if ($value === 'false') {
                return false;
            }

            return '';
        }

        private static function downloadAsCSVLink(): string
        {
            $url = add_query_arg(
                [
                'page' => 'websitecarbon',
                'wc-action' => 'download'
                ],
                admin_url() . 'tools.php'
            );

            return $url;
        }

        private static function downloadAsCSV(): void
        {
            include 'views/download-as-csv.php';
            // Make sure nothing else is sent, our file is done
            exit;
        }

        private static function processPost(int $postID): bool
        {
            $url = get_permalink($postID);

            $report = self::getWebsiteCarbonReport($url);

            if ($report !== false) {
                update_option('websitecarbon-green', $report->green);
                update_post_meta($postID, 'wc-report', $report);
                update_post_meta($postID, 'wc-co2', self::emissions($report));

                return true;
            }

            return false;
        }

        private static function getEmissionsString(int $postID): string
        {
            $emissions = self::getEmissionsValue($postID);

            return $emissions !== '' ? round($emissions, 3) . ' grams per page view' : self::testLink($postID);
        }

        private static function getEmissionsValue(int $postID)
        {
            $report = get_post_meta($postID, 'wc-report', true);

            // This needs reviewing. The conditional is broken...
            return !empty($report->cleanerThan)
                ? self::emissions($report)
                : '';
        }

        private static function testLink(int $postID): string
        {
            if (get_post_status($postID) !== 'publish') {
                return __('Only published posts can be tested', 'website-carbon');
            }

            $url = add_query_arg(
                [
                'post_type' => get_post_type($postID),
                'post' => $postID,
                'wc-action' => 'measure-emissions'
                ],
                admin_url() . 'edit.php'
            );

            return '<a href="' . esc_url($url) . '">' . __('Measure emissions', 'website-carbon') . '</a>';
        }

        private static function emissions($report)
        {
            return $report->green
                ? $report->statistics->co2->renewable->grams
                : $report->statistics->co2->grid->grams;
        }

        private static function getWebsiteCarbonReport(string $url)
        {
            $url = 'https://api.websitecarbon.com/site?url=' . $url;

            $response = wp_remote_get(
                $url,
                [
                'timeout' => 40, // Default is 5 seconds, some pages are really slow in lighthouse
                ]
            );

            if (is_wp_error($response)) {
                return false;
            }

            $body = json_decode(wp_remote_retrieve_body($response));

            if (property_exists($body, 'error')) {
                return false;
            }

            return $body;
        }

        private static function currentAdminPage(): string
        {
            global $pagenow;
            return $pagenow;
        }

        private static function getPluginVersion(): string
        {
            return get_plugin_data(__FILE__)['Version'];
        }
    }

    \WebsiteCarbon\WebsiteCarbon::init();
}
