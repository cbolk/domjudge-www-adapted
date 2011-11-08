<?php
/**
 * View teams participation
 *
 * $Id: participation.php 3209 2011-10-12 14:13:43Z cbolk $
 *
 * Added to the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 * Added by CBolk
 */

require('init.php');
$title = 'Team Participation';

require(LIBWWWDIR . '/header.php');

echo "<h1>Teams & Problems</h1>\n\n";

$strSQL = 'SELECT starttime FROM contest WHERE now() > starttime AND endtime > now() ORDER BY starttime DESC LIMIT 1;';
$activetime = $DB->q($strSQL);
$ats = $activetime->next();
$at = $ats['starttime'];

if($activetime){
	$strSQL = 'SELECT name FROM team WHERE teampage_first_visited > "' . $at . '" ORDER BY name;';
	$checkedin = $DB->q($strSQL);
}



$strSQL = 'SELECT teamid, t.name, probid, submissions, is_correct FROM scoreboard_jury sbj INNER JOIN team t ON (sbj.teamid = t.login) ORDER BY t.name;';

$res = $DB->q($strSQL);

$strSQL = 'SELECT DISTINCT sbj.probid, name FROM scoreboard_jury sbj INNER JOIN problem ON sbj.probid = problem.probid INNER JOIN contest c on problem.cid = c.cid ORDER BY c.starttime';

$probs = $DB->q($strSQL);
$nprob = $probs->count();


if( $res->count() == 0 ) {
	echo "<p class=\"nodata\">No participation information</p>\n\n";
} else {
	/* the matrix containing information */

	echo "<table  id='tablespec' class=\"list sortable\">\n" .
	     "<tr><th scope=\"col\" rowspan='2' colspan='2'>Team</th>";
	$strp = '';
	$strinfo = '';
	$PROBS = array();
	$np = 0;
	while($row = $probs->next()){
		$PROBS[$np] = $row[probid];
	 	$strp = $strp . "<th scope=\"col\" colspan='2'  class='acenter'>" . substr($row[name],0,10) . "</th>";
	 	$strinfo = $strinfo . "<th class='acenter'>#sub</th><th class='acenter'>res</th>";
	 	$np = $np+1;
	}
	echo $strp;
	echo "</tr>";
	echo "<tr><th></th><th></th>" .$strinfo . "</tr>";
	echo "<tbody>\n";
	$nteam = 0;
	$TEAMS = $MATRIX = array();
	$lastteam = '';
	while($row = $res->next()){
		$MATRIX[$row['teamid']][$row['probid']] =  array (
			'res'      => (bool) $row['is_correct'],
			'numsub' => $row['submissions'],
			'checkedin' => 0
		);
		if($row['teamid'] != $lastteam){
			$TEAMS[$nteam]['id'] = $row['teamid'];
			$TEAMS[$nteam]['name'] = $row['name'];
			$nteam++;
			$lastteam = $row['teamid'];
		}
	}
	$i = 0;
	while ($i < $nteam) {
		$strteam = "<th scope='row' class='aright'>" . ($i+1) . "</th><td>" . $TEAMS[$i]['name'] . "</td>" ;
		$j  = 0;
		while($j < $np){
			if(isset($MATRIX[$TEAMS[$i]['id']][$PROBS[$j]])){
				$strteam = $strteam . "<td class='acenter'>" . $MATRIX[$TEAMS[$i]['id']][$PROBS[$j]]['numsub'] . "</td><td class='acenter'>";
				if($MATRIX[$TEAMS[$i]['id']][$PROBS[$j]]['res']) 
					$strteam = $strteam . "<img width='16px' src='../images/correct.png' alt='okay' />";
				else
					$strteam = $strteam . "<img width='16px' src='../images/wrong-answer.png' alt='not correct' />";
				$strteam = $strteam . "</td>";			
				
			 }else 
				$strteam = $strteam . "<td></td><td></td>";
			$j++;
			
		}
		echo "<tr>" . $strteam . "</tr>";
		$i++;
	}
	echo "</tbody>\n</table>\n\n";
}


require(LIBWWWDIR . '/footer.php');
