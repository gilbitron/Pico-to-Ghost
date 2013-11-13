<?php

/**
 * Pico to Ghost Exporter
 *
 * @author Gilbert Pellegrom
 * @link http://pico.dev7studios.com
 * @license http://opensource.org/licenses/MIT
 * @version 1.0
 */

if(!file_exists('lib/markdown.php')) die('Error: This script needs to be run from a Pico root install.')
include_once 'lib/markdown.php';

$output = array();
$output['meta'] = array(
	'exported_on' => time() * 1000,
	'version' => '000'
);

$posts = array();
$i = 1;
foreach(glob("content/*.md") as $file){
	$content = file_get_contents($file);
	$meta = read_file_meta($content);
	$content = preg_replace('#/\*.+?\*/#s', '', $content); // Remove comments and meta
	$html = Markdown($content);
	$time = isset($meta['date']) && $meta['date'] ? strtotime($meta['date']) * 1000 : filemtime($file) * 1000;

	if($content && $meta){
		$post = array(
			"id" => $i,
			"uuid" => uniqid(),
			"title" => $meta['title'],
			"slug" => to_slug($meta['title']),
			"markdown" => $content,
			"html" => $html,
			"image" => null,
			"featured" => 0,
			"page" => 0,
			"status" => "published",
			"language" => "en_US",
			"meta_title" => null,
			"meta_description" => $meta['description'],
			"author_id" => 1,
			"created_at" => $time,
			"created_by" => 1,
			"updated_at" => $time,
			"updated_by" => 1,
			"published_at" => $time,
			"published_by" => 1
		);
		$posts[] = $post;
		$i++;
	}
}
$output['data']['posts'] = $posts;

$output = json_encode($output);
file_put_contents('ghost.json', $output);
header('Content-type: application/json');
die($output);

function read_file_meta($content)
{
	$headers = array(
		'title'       	=> 'Title',
		'description' 	=> 'Description',
		'author' 		=> 'Author',
		'date' 			=> 'Date',
		'robots'     	=> 'Robots'
	);

 	foreach ($headers as $field => $regex){
		if (preg_match('/^[ \t\/*#@]*' . preg_quote($regex, '/') . ':(.*)$/mi', $content, $match) && $match[1]){
			$headers[ $field ] = trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $match[1]));
		} else {
			$headers[ $field ] = '';
		}
	}

	return $headers;
}

function to_slug($string){
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}