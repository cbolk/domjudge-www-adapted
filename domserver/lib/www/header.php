<?
/**
* Common page header.
* Before including this, one can set $title, $refresh and $popup.
*
*
* Part of the DOMjudge Programming Contest Jury System and licenced
* under the GNU GPL. See README and COPYING for details.
*
* Modified by CBolk
*/
if (!defined('DOMJUDGE_VERSION')) die("DOMJUDGE_VERSION not defined.");

header('Content-Type: text/html; charset=' . DJ_CHARACTER_SET);

/* Prevent clickjacking by forbidding framing in modern browsers.
 * Really want to frame DOMjudge? Then change DENY to SAMEORIGIN
 * or even comment out the header altogether.
 */
header('X-Frame-Options: DENY');

if ( isset($refresh) &&
     (!isset($_COOKIE["domjudge_refresh"]) ||
      (bool)$_COOKIE["domjudge_refresh"]) ) {
	header('Refresh: ' . $refresh);
}
echo '<?xml version="1.0" encoding="' . DJ_CHARACTER_SET . '" ?>' . "\n";

if(!isset($menu)) {
	$menu = true;
}
if(!isset($ajaxtitle)) {
	$ajaxtitle = '';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<link href='http://fonts.googleapis.com/css?family=Andika' rel='stylesheet' type='text/css'>

<!-- DOMjudge version <?php echo DOMJUDGE_VERSION?> -->
<title><?php echo $title?></title>
<link rel="shortcut icon" href="../images/favicon.png" type="image/png" />
<link rel="stylesheet" href="../style.css" type="text/css" />

<!-- code formatting -->
<script type="text/javascript" src="../addon/syntaxhighlighter/js/shCore.js"></script>
<script type="text/javascript" src="../addon/syntaxhighlighter/js/shBrushCpp.js"></script>
<!-- Include *at least* the core style and default theme -->
<link href="../addon/syntaxhighlighter/css/shCoreSmaller.css" rel="stylesheet" type="text/css" />
<link href="../addon/syntaxhighlighter/css/shThemeDefault.css" rel="stylesheet" type="text/css" />


<?php
if ( IS_JURY ) {
echo "<link rel=\"stylesheet\" href=\"style_jury.css\" type=\"text/css\" />\n";
if (isset($printercss)) {
echo "<link rel=\"stylesheet\" href=\"style_printer.css\" type=\"text/css\" media=\"print\" />\n";
}
if (isset($jscolor)) {
echo "<script type=\"text/javascript\" src=\"" .
"../js/jscolor.js\"></script>\n";
}
echo "<script type=\"text/javascript\" src=\"" .
"../js/sorttable.js\"></script>\n";
}

echo "<script type=\"text/javascript\" src=\"../js/domjudge.js\"></script>\n";

/* NOTE: here a local menu.php is included
* both jury and team have their own menu.php
*/
if ($menu) {
	?>
</head>
<body onload="setInterval('updateClarifications(\'<?php echo $ajaxtitle?>\')', 20000)">
<div id='wrapper'>
<div id='toptitle'><a class='nodec' href='/contests/'>domjudge @ fondamenti di informatica</a></div>
<?php 
	include("menu.php");
} else {
	?>
</head>
<body>
<div id='wrapper'>
<div id='toptitle'><a class='nodec' href='/contests/'>domjudge @ fondamenti di informatica</a></div>
<div id='header'></div>
<div id='main'>
<?php 
}