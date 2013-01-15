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

echo "<h1>Teams @ LAB</h1>\n\n";

$strSQL = "SELECT teamid, t.name, probid, submissions, is_correct FROM laboratory sbj LEFT JOIN team t ON (sbj.teamid = t.login) INNER JOIN contest ON sbj.cid = contest.cid where contestname like 'Lab%%' and t.`categoryid` = 1 ORDER BY teamid, probid;";
$res = $DB->q($strSQL);

$strSQL = 'SELECT DISTINCT sbj.probid, name, problem.cid FROM laboratory sbj INNER JOIN problem ON sbj.probid = problem.probid INNER JOIN contest c on problem.cid = c.cid where contestname like \'Lab%%\' ORDER BY problem.cid, c.starttime, sbj.probid';
$probs = $DB->q($strSQL);
$nprob = $probs->count();

$strSQL = 'SELECT contest.cid, contest.contestname, COUNT(probid) AS nprob_cid FROM contest INNER JOIN problem on contest.cid = problem.cid  where contestname like \'Lab%%\'  group by contest.cid order by cid;';
$conts = $DB->q($strSQL);
$nconts = $conts->count();

if( $res->count() == 0 ) {
	echo "<p class=\"nodata\">No participation information</p>\n\n";
} else {
	/* the matrix containing information */

	echo "<table  id='tableresults' class=\"list sortable\">\n";
	echo "<tr><th scope=\"col\" rowspan='2' colspan='2'>&nbsp;</th>";
	$strp = "";
	while($row = $conts->next()){
	 	$strp = $strp . "<th scope=\"col\" colspan='" . $row[nprob_cid] . "'  class='acenter sideborder'>" . $row[contestname] . "</th>";
	}
	echo $strp . "</tr>";
	echo "<tr><th scope=\"col\" rowspan='2' colspan='2' class='acenter'>Team</th>";
	
	
	$strp = '';
	$strinfo = '';
	$strlegend = '';
	$PROBS = array();
	$np = 0;
	while($row = $probs->next()){
		$PROBS[$np] = $row[probid];
	 	$strp = $strp . "<th scope=\"col\" class='acenter'><a href='#P" . $row[probid] . "'>P" . $row[probid] . "</a></th>";
		$strlegend = $strlegend . "<a name='P" . $row[probid] ."'></a><p>P" . $row[probid] . ": " . $row[name] . "</p>";
	 	$np = $np+1;
	}
	echo $strp;
	echo "</tr>";
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
		$strteam = "<th scope='row' class='aright'>" . ($i+1) . "</th><td>" . $TEAMS[$i]['id'] . "</td>" ;
		$j  = 0;
		while($j < $np){
			if(isset($MATRIX[$TEAMS[$i]['id']][$PROBS[$j]])){
				if($MATRIX[$TEAMS[$i]['id']][$PROBS[$j]]['res']) 
					$strteam = $strteam . "<td class='acenter lab_correct'>" . $MATRIX[$TEAMS[$i]['id']][$PROBS[$j]]['numsub'] . "</td>";
				else
					$strteam = $strteam . "<td class='acenter lab_incorrect'>-" . $MATRIX[$TEAMS[$i]['id']][$PROBS[$j]]['numsub'] . "</td>";
				
			 }else 
				$strteam = $strteam . "<td class='acenter'>0</td>";
			$j++;
			
		}
		echo "<tr>" . $strteam . "</tr>";
		$i++;
	}
	echo "</tbody>\n</table>\n\n";
	
	echo "<h4>Legenda</h4>\n" . $strlegend;
}


 
require(LIBWWWDIR . '/footer.php');
