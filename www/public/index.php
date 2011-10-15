<?php
/**
 * Produce a total score. Call with parameter 'static' for
 * output suitable for static HTML pages.
 *
 * $Id: index.php 3209 2010-06-12 00:13:43Z eldering $
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 * Modified by CBolk
 */

require('init.php');
$title="sfide ...";
// set auto refresh
$refresh="30;url=./";
$menu = false;
require(LIBWWWDIR . '/header.php');
require(LIBWWWDIR . '/contest.cb.php');
setlocale(LC_ALL, 'it_IT');
$isstatic = @$_SERVER['argv'][1] == 'static' || isset($_REQUEST['static']);

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
		echo "<h3 class='tcenter'>Nessuna sfida aperta al momento</h3>";		
	}
	echo "<div class='vspace50'></div>";
	echo "<div class='clear'></div>";
	echo "<div class='login'>";
    echo "<div class='left w200'>";
	echo "<h4 class='login_member'>Non sei abilitato?<br/>&nbsp;</h4>";
	echo "<a href='mailto:bolchini@elet.polimi.it?subject=richiesta accesso webapp sfide' class='login_request'>&nbsp;chiedi l'accesso</a>";
	echo "</div>";
    echo "<div class='left w200'>";
	echo "<h4 class='login_member'>Accesso utente registrato:</h4>";
	echo "<div class='clear'>";
	echo "<a href='../team/index.php' class='login_signin'>&nbsp;vai al login</a>";
	echo "</div>";
	echo "</div>";
	echo "<img src='../images/shadow_bottom.png' class='left img_right_block_shadow_bottom' width='405px'>";
	echo "</div>"; 
	/*putScoreBoard($cid, null, $isstatic);*/
}
echo "</div><!-- main container -->";

require(LIBWWWDIR . '/footer.php');

