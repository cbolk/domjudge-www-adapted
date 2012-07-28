<?php
/**
 * Show problems details
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require('init.php');
$probid = $_REQUEST['id'];
$title = 'Problem p' . $probid;
require(LIBWWWDIR . '/header.php');
require(LIBWWWDIR . '/contest.cb.php');


// call the general putContestFull function from contest.cb.php
echo "<div id='main_container'>";
$cid = getContestFromProblem($probid);
if($cid != NULL){
	echo "<h2 class='tcenter'></h2>";
	echo "<div class='vspace50'></div>";
	echo "<div class='clear'></div>";
	putProblemAsViewed($probid);
	echo "<div class='vspace50'></div>";
	echo "<div class='clear'></div>";
}
echo "</div><!-- main container -->";

require(LIBWWWDIR . '/footer.php');

