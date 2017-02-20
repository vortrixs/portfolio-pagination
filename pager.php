<?php

ini_set('display_errors', true);
error_reporting(E_ALL^E_NOTICE);

require_once 'includes/DB.php';
require 'Pagination.php';

foreach (parse_ini_file('config.ini', true) as $key => $value) {
    $arr[$key] = (object) $value;
}
$ini = (object) $arr;

$pager = new Pagination(new DB($ini->db->host,$ini->db->database,$ini->db->username,$ini->db->password));

$pager->setter($_POST['page'], 15);

$results = $pager->pagination(['*'], 'pokemon');

echo '<h1>List of Pokémon</h1>';

echo "<div id='pokemon_list'>";
foreach ($results as $result) {
	echo "<span class='pokemon_name'><b>" . $result->name . '</b></span>';
	echo "<br>";
	echo "<input type='button' class='details_btn' value='Show details' data-value='{$result->name}'>";
	echo "<div class='details' id='{$result->name}'>";
		echo "<ul>";
		foreach ($result as $key => $value) {
			if ($key != "name" && $key != "id" && $key != "total") {
				echo "<li><b>{$key}</b>: {$value}</li>";
			}
		}
		echo "</ul>";
	echo "</div>";
	echo "<br><br>";
}
echo "</div>";

echo "<div id='loading-div'>Loading...</div>";

echo '<div id="pager" align="center">';
echo $pager->generateLinks();
echo '</div>';
