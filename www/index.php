<?php 
/*
 * This file displays all the files made available by this server. It does
 * not fetch remote schemas.
 *
 * All errors are silently discarded.
 * 
 * --
 * 
 * This file is part of the focus-resources-server package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$files = glob('schemas/*/*.json');
sort($files, SORT_NATURAL);

$schemas = array();
$schemas_version_ref = array();

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
	
	$diagram_path = preg_replace('/^schemas(\/.*\.)json$/', 'documentation/diagrams$1png', $f);
	if (!is_readable($diagram_path)) {
		$diagram_path = FALSE;
	}
	
	$deps = array_unique(look_for_refs($json));
	
	$type_id_no_version = $matches[1];
	$type_id_no_version = rtrim($type_id_no_version, '/');
	
	if (!isset($schemas[$type_id_no_version])) {
		$schemas[$type_id_no_version] = array('name' => '', 'versions' => array());
	}
	$schemas[$type_id_no_version]['name'] = $json->title;
	$cnt = count($schemas[$type_id_no_version]['versions']);
	$schemas[$type_id_no_version]['versions'][$cnt] = array(
			'uri' => $json->id, 
			'content' => $content,
			'diagram' => $diagram_path,
			'examples' => array(), 
			'dependencies' => $deps // FIXME must check if local or remote.
	);
	$schemas_version_ref[$json->id] = &$schemas[$type_id_no_version]['versions'][$cnt];
}

// retrieve examples
$files = glob('documentation/examples/*/*.json');
sort($files, SORT_NATURAL);
foreach ($files as $f) {
	$content = file_get_contents($f);
	$json = json_decode($content);
	if (!isset($json->type)) {
		continue;
	}
	if (!isset($schemas_version_ref[$json->type])) {
		continue;
	}
	$schemas_version_ref[$json->type]['examples'][] = $f;
}

// recursively look for $ref-ed schemas
function look_for_refs($obj)
{
	$ret = array();
	foreach($obj as $k => $o) {
		if (is_array($o) || is_object($o)) {
			$ret = array_merge($ret, look_for_refs($o));
		}
		else if ($k === '$ref') {
			$found = preg_replace('/#.*$/', '', $o);
			if ($found) {
				$ret[] = $found;
			}	
		}
	}
	return $ret;	
}

?><!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>JSON Schemas</title>
		<link href="misc/css/styles.css" rel="stylesheet" type="text/css" />
		
	</head>

	<body>
	
		<h1>JSON Schemas</h1>
		
		<p>The following schemas are made available by this server.</p>
		
		<hr/>
		
		<?php foreach ($schemas as $no_version_uri => $schema): ?>
			<h2><?php print $schema['name']; ?></h2>
			<ul>
				<?php foreach ($schema['versions'] as $v): ?>
					<li>
						<div id="<?php print preg_replace('/[^0-9a-zA-Z_-]/', '-', $v['uri']); ?>">
							<a href="<?php print $v['uri']; ?>"><?php print $v['uri'];?></a>
							<?php if ($v['diagram']): ?>
								&ndash; <a class="diagram" href="<?php print $v['diagram']; ?>">Diagram</a>
							<?php endif; ?>
						</div>
						<div>
							Examples:
							<?php if ($v['examples']): ?>
								<?php foreach ($v['examples'] as $e): ?>
									<a href="<?php print $e; ?>"><?php print basename($e); ?></a>
								<?php endforeach; ?>
							<?php else: ?>
								none.
							<?php endif; ?>
						</div>
						<div>
							Dependencies:
							<?php if ($v['dependencies']): ?>
								<?php foreach ($v['dependencies'] as $d): ?>
									<a href="#<?php print preg_replace('/[^0-9a-zA-Z_-]/', '-', $d); ?>"><?php print $d; ?></a>
								<?php endforeach; ?>
							<?php else: ?>
								none.
							<?php endif; ?>
						</div>
						<pre>Schema:<br/><?php print $v['content']; ?></pre>
					</li>
				<?php endforeach; ?>
			</ul>
			<hr/>
		<?php endforeach; ?>
		
		
	</body>

</html>

