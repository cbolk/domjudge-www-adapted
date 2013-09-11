<?php
/**
 * Show problems details
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require('init.php');
$cid = $_REQUEST['id'];
$title = 'Contest ' . $cid;
require(LIBWWWDIR . '/header.php');
require(LIBWWWDIR . '/contest.cb.php');


// call the general putContestFull function from contest.cb.php
echo "<div id='main_container'>";
if($cid != NULL){
	echo "<div class='vspace50'></div>";
	echo "<div class='clear'></div>";
	renderContestPrint($cid);
	echo "<div class='vspace50'></div>";
	echo "<div class='clear'></div>";
}
echo "</div><!-- main container -->";

require(LIBWWWDIR . '/footer.php');

