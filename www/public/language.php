<?php
/**
 * View a language
 *
 * $Id: language.php 3209 2010-06-12 00:13:43Z eldering $
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 * Modified by CBolk
 */

$pagename = basename($_SERVER['PHP_SELF']);

require('init.php');

$id = @$_REQUEST['id'];
$title = 'Language '.htmlspecialchars(@$id);

if ( ! preg_match('/^\w*$/', $id) ) error("Invalid language id");

require(LIBWWWDIR . '/header.php');

$data = $DB->q('TUPLE SELECT langid, name, extension, time_factor, usageinformation FROM language WHERE langid = %s', $id);

if ( ! $data ) error("Missing or invalid language id");
$languageicon = strtolower('../images/languages/' . $data['name'] . '_file.png');
if ( is_readable($languageicon) ) {
	echo '<h1>Language ' .htmlspecialchars($data['name']).' <img class="imgmiddle" src="' . $languageicon . '"' .
		 ' alt="language '   . htmlspecialchars($data['name']) . '"' .
		 ' title="language ' . htmlspecialchars($data['name']) . '" /></h1>';
} else 
	echo "<h1>Language ".htmlspecialchars($data['name'])."</h1>";
?>
<table width="800">
<tr><td class="theader aleft" scope="row">Extension:   </td><td class="filename aleft">.<?php echo htmlspecialchars($data['extension'])?></td></tr>
<tr><td class="theader aleft" scope="row">Time factor:  </td><td class="aleft"><?php echo htmlspecialchars($data['time_factor'])?> x</td></tr>
<tr><td class="theader aleft vtop" scope="row">Usage:  </td><td class="aleft"><?php echo $data['usageinformation'] ?> </td></tr>
</table>

<?php
require(LIBWWWDIR . '/footer.php');
?>