<?php

/**
 * Functions for team additional information.
 *
 * $Id: team.cb.php 3209 2010-06-12 00:13:43Z cbolk $
 *
 * Added to the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */


/**
 */
function renderTeamSubmissionHistoryStats($teamid, $tdata){

	// 'unpack' the team data:
	$probs   = $tdata['problems'];
	$subs   = $tdata['submissions'];
	unset($tdata);
	$submissions = array();

	while($v = $subs->next()){
		$submissions[$v['probid']]['number'] = $v['number'];
		$submissions[$v['probid']]['isOK'] = $v['isOK'];
	}
	
	echo "<div id='thistorystats'>";
	echo "<table id='tablespec' >";
	echo "<tr><th scope='column'>problem</th><th scope='column'>#sub</th><th scope='column'>ok</th></tr>";
	$i = 0;
	while($row = $probs->next()){
		if($i % 2)
			echo "<tr class='rowodd'>";
		else
			echo "<tr class='roweven'>";
		echo "<td>" . $row['problemname'] . "</td><td class='acenter'>";
		if(isset($submissions[$row['probid']])){
			echo  $submissions[$row['probid']]['number'] . "</td><td class='acenter'>";
			echo "<img width='16px' src='../images/";
			if($submissions[$row['probid']]['isOK'] == 1)
				echo "correct";
			else
				echo "wrong-answer";

			echo ".png' />";
		} else {
			echo "-</td><td class='acenter'>-";
		}
		echo "</td></tr>";
		$i = $i + 1;
	}	
	echo "</table>";
	echo "</div>";
}


function genTeamSubmissionHistoryStats($teamid) {
	global $DB;

	$strSQL = "SELECT c.cid, p.probid, p.name AS problemname 
				FROM contest c INNER JOIN problem p ON c.cid = p.cid
				ORDER BY  c.`starttime`";

	$problems = $DB->q($strSQL);

	// get the team and his/her submission info
	$strSQL = "SELECT scoreboard_jury.cid, probid, submissions AS number, is_correct AS isOK
				FROM scoreboard_jury INNER JOIN contest ON scoreboard_jury.cid = contest.cid
				WHERE teamid = '" . $teamid . "'
				ORDER BY starttime;";
				
	$submissions = $DB->q($strSQL);
		
	return array( 'problems'   => $problems,
	              'submissions' => $submissions );
}


/**
 * Very simple wrapper around the contest data-generating and
 * rendering functions.
 */
function putTeamSubmissionHistoryStats($teamid) {

	$tdata = genTeamSubmissionHistoryStats($teamid);
	renderTeamSubmissionHistoryStats($teamid,$tdata);
	
}

/**
 * Team summary stats: number of contests, number of problems and number of 
 * correct solutions
 */
function getTeamStats($teamid){
	global $DB;
	$strSQL = "SELECT scoreboard_jury.cid, probid, submissions AS number, is_correct AS isOK
				FROM scoreboard_jury INNER JOIN contest ON scoreboard_jury.cid = contest.cid
				WHERE teamid = '" . $teamid . "'   
				ORDER BY starttime;";
				/*   '" . $teamid . "' */
	$submissions = $DB->q($strSQL);
	return $submissions;
}

function renderTeamStats($teamid,$tdata){
	
	$subs   = $tdata;
	$ncontests = 0;
	$nproblems = 0;
	$nsubs = 0;
	$nok = 0;
	$cid = -1;
	$pid = -1;
	while($v = $subs->next()){
		
		if($v['cid'] != $cid){
			$ncontests++;
			$nproblems++;
			$pid = $v['probid'];
		} else 
				if($v['probid'] != $pid){
					$nproblems++;
					$pid = $v['probid'];
		}
				
		$nsubs =  $nsubs + $v['number'];
		$nok = $nok + $v['isOK'];
	}
	
	echo "\n<div class='statsbox'>\n&nbsp;";
    echo "  <ul class='stats'>\n";
    echo "    <li>\n";
    echo "      <strong>" . $ncontests . "</strong>\n";
    echo "      <span>contests</span></li>\n";
    echo "    <li>\n";
    echo "      <strong>" . $nproblems . "</strong>\n";
    echo "      <span>problems</span></li>\n";
    echo "    <li>\n";
    echo "      <strong>" . $nsubs . "</strong>\n";
    echo "      <span>submissions</span></li>\n";
    echo "    <li>\n";
    echo "      <strong>" . $nok . "</strong>\n";
    echo "      <span>correct</span></li>\n";
    echo " </ul>\n";
	echo "</div>\n";
	
}

/**
  * outputting information 
 */
function putTeamStats($teamid) {
	$tdata = getTeamStats($teamid);
	renderTeamStats($teamid,$tdata);
}