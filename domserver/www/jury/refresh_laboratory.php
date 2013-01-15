<?php

/**
 * Recalculate all cached data in DOMjudge:
 * - The scoreboard.
 * Use this sparingly since it requires
 * (3 x #teams x #problems) queries.
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require('init.php');
$title = 'Refresh Cache';
require(LIBWWWDIR . '/header.php');
require(LIBWWWDIR . '/scoreboard.php');

echo "<h1>Refresh Laboratory</h1>\n\n";

requireAdmin();

if ( ! isset($_REQUEST['refresh']) ) {
	echo addForm('');
	echo msgbox('Significant database impact',
	       'Refreshing the laboratory view can have a significant impact on the database load, ' .
	       'and is not necessary in normal operating circumstances.<br /><br />Refresh scoreboard cache now?' .
	       '<br /><br />' .
               addSubmit(" Refresh now! ", 'refresh') );
        echo addEndForm();

	require(LIBWWWDIR . '/footer.php');
	exit;	
}

$time_start = microtime(TRUE);

auditlog('laboratory', null, 'refresh cache');

// no output buffering... we want to see what's going on real-time
ob_implicit_flush();

// get the contest, teams and problems
$teams = $DB->q('TABLE SELECT login FROM team WHERE categoryid = 1 ORDER BY login');
$probs = $DB->q("COLUMN SELECT p.probid FROM problem p INNER JOIN contest c ON p.cid = c.cid
                 WHERE c.contestname LIKE 'Lab%%' ORDER BY p.cid, p.probid");

echo "<p>Recalculating all values for the laboratory cache (" .
	count($teams) . " teams, " . count($probs) ." problems for all contests)...</p>\n\n<pre>\n";

if ( count($teams) == 0 ) {
	echo "No teams defined, doing nothing.</pre>\n\n";
	require(LIBWWWDIR . '/footer.php');
	exit;
}
if ( count($probs) == 0 ) {
	echo "No problems defined, doing nothing.</pre>\n\n";
	require(LIBWWWDIR . '/footer.php');
	exit;
}

$teamlist = array();

// for each team, fetch the status of each problem
foreach( $teams as $team ) {

	$teamlist[] = $team['login'];

	echo "Team " . htmlspecialchars($team['login']) . ":";

	// for each problem fetch the result
	foreach( $probs as $pr ) {
		echo " " .htmlspecialchars($pr);
		calcLaboratoryStats($team['login'], $pr);
	}

	echo "\n";
}

$time_end = microtime(TRUE);

echo "<p>Laboratory cache refresh completed in ".round($time_end - $time_start,2)." seconds.</p>\n\n";

require(LIBWWWDIR . '/footer.php');