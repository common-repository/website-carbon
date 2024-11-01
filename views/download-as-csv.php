<?php

// Exit if accessed directly
if(! defined('ABSPATH') ) {
    exit;
}

$fileName = str_replace(['https://', 'http://', 'www.'], '', get_site_url()) . '-co2.csv';

$results = self::getPosts();

$results = array_map(
    function ($post) {
        return [
        'id' => $post['id'],
        'title' => wp_strip_all_tags($post['title']),
        'co2' => self::getEmissionsValue($post['id']),
        'type' => get_post_type($post['id']),
        'url' => get_permalink($post['id']),
        ];
    }, $results
);

header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header('Content-Description: File Transfer');
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename={$fileName}");
header("Expires: 0");
header("Pragma: public");

$fh = @fopen('php://output', 'w');

$headerDisplayed = false;

foreach ( $results as $data ) {
    // Add a header row if it hasn't been added yet
    if (!$headerDisplayed ) {
        // Use the keys from $data as the titles
        fputcsv($fh, array_keys($data));
        $headerDisplayed = true;
    }

    // Put the data into the stream
    fputcsv($fh, $data);
}
// Close the file
fclose($fh);
