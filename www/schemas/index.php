<?php 
/*
 * This file is part of the focus-resources-server package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This file displays all the schemas being stored in this repository.
 */

$files = glob( '*/*.json');
sort($files, SORT_NATURAL);
// all errors silently discarded

$schemas = array();

foreach ($files as $f) {
	$content = file_get_contents($f);
	if ($content === FALSE) {
		continue;
	}
	
	$json = json_decode($content);
	if ($json === FALSE) {
		continue;
	}
	
	if (!isset($json->id) || !$json->id || !isset($json->title) || !$json->title) {
		continue;
	}
	
	$matches = array();
	if (!preg_match('|^(.*)/v\d+(\.\d+)?$|', $json->id, $matches)) {
		continue;
	}
	
	$type_id_no_version = $matches[1];
	$type_id_no_version = rtrim($type_id_no_version, '/');
	
	if (!isset($schemas[$type_id_no_version])) {
		$schemas[$type_id_no_version] = array('name' => '', 'versions' => array());
	}
	$schemas[$type_id_no_version]['name'] = $json->title;
	$schemas[$type_id_no_version]['versions'][] = array('uri' => $json->id, 'content' => $content);
	
	
}
// FIXME FIXME FIXME
// FIXME should inspect content and re-order based on dependencies.
// and retrieve examples $type_id_short = explode('/', $json->id);
	//$c = count($type_id_short);
	//$type_id_short = $type_id_short[ $c - 2 ];
	//$example_file_name = $type_id_short[ $c - 2 ] . '/*.json';
	
	 
// FIXME FIXME FIXME

$duh = 'debug';

?><!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>JSON Schemas</title>
	</head>

	<body>

	<pre>
	<?php var_dump($duh); ?>
	</pre>
	
		<h1>JSON Schemas</h1>
		
		<p>The following schemas are made available by this server.</p>
		
		<hr/>
		
		<?php foreach ($schemas as $no_version_uri => $schema): ?>
			<h2><?php print $schema['name']; ?></h2>
			<ul>
				<?php foreach ($schema['versions'] as $v): ?>
					<li>
						<a href="<?php print $v['uri']; ?>"><?php print $v['uri'];?></a>
						<pre><?php print print $v['content']; ?></pre>
					</li>
				<?php endforeach; ?>
			</ul>
			<hr/>
		<?php endforeach; ?>
		
		
	</body>

</html>

