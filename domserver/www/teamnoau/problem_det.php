<?php
/**
 * $Id: index.php 3118 2010-02-22 20:23:55Z kink $
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require('init.php');
$refresh = '120;url=index.php';
$title = 'Current problem';
require(LIBWWWDIR . '/header.php');
require(LIBWWWDIR . '/contest.cb.php');

// call the general putContestFull function from contest.cb.php
echo "<div id='main_container'>";
$cid = getCurContest(TRUE);
if($cid != NULL){
	/* either presently open or the last closed one */
	if(isContestClosed($cid)==0)
		putContestStats($cid, null, $isstatic);
	else
		putContestFull($cid, null, $isstatic);
} else {
	/* TODO: show past contests and/or scoreboards */
	echo "<h2 class='tcenter'>Benvenuto</h2>";
	echo "<div class='vspace50'></div>";
	echo "<div class='clear'></div>";
	$cid = getNextContest(TRUE);
	if($cid != NULL){
		echo "<h3 class='tcenter'>La prossima avr&agrave; inizio il " . strftime("%e %B %G", strtotime($cid['starttime'])) . " alle " . strftime("%R", strtotime($cid['starttime'])) . "</h3>";
	} else {
		echo "<h3 class='tcenter'>Nessuna sfida aperta e/o prevista al momento</h3>";		
	}
	echo "<div class='vspace50'></div>";
	echo "<div class='clear'></div>";
}
echo "</div><!-- main container -->";

require(LIBWWWDIR . '/footer.php');

