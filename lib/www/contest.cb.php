<?php

/**
 * Functions for public contest information.
 *
 * $Id: contest.cb.php 3209 2010-06-12 00:13:43Z cbolk $
 *
 * Added to the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */


/**
 * The calcScoreRow is in lib/lib.misc.php because it's used by other
 * parts of the system aswell.
 */
require_once(LIBDIR . '/lib.misc.php');

/**
 * Generate contest data based on the cached data in table
 * 'scoreboard_{public,jury}'. If the function is called while
 * IS_JURY is defined, the scoreboard will always be current,
 * regardless of the freezetime setting in the contesttable.
 *
 * This function returns an array (scores, summary, matrix)
 * containing the following:
 *
 * scores[login](num_correct, total_time, solve_times[], rank,
 *               teamname, categoryid, sortorder, country, affilid)
 *
 * matrix[login][probid](is_correct, num_submissions, time, penalty)
 *
 * summary(num_correct, total_time, affils[affilid], countries[country], problems[probid])
 *    probid(num_submissions, num_correct, best_time)
 */
function genContestFull($cdata) {

	global $DB;

	$cid = $cdata['cid'];

	$now = now();
	
	//timing
	$fromdate = $cdata['starttime'];
	$todate = $cdata['endtime'];
	$days = (int)(strtotime($now) - strtotime($fromdate));

	if($days > 0)
		$cstarted = 1;
	else
		$cstarted = 0;
	// Don't leak information before start of contest
	if ( ! $cstarted && ! IS_JURY ) return;


	// get the teams and problems
	$teams = $DB->q('MAYBETUPLE SELECT probid, count(*) as numpartecipants
						from (SELECT probid, teamid, count(*) as number 
						FROM submission
						WHERE cid = %i 
						group by probid, teamid) as teams
						GROUP BY probid', $cid);
	$strSQL = "MAYBETUPLE SELECT probid, count(*) as numpartecipants
						from (SELECT probid, teamid, count(*) as number 
						FROM submission
						WHERE cid = " . $cid . " group by probid, teamid) as teams
						GROUP BY probid";
	$teams = $DB->q($strSQL);
	$strSQL = "SELECT probid, name, color, longdescription FROM problem
	                 WHERE cid = " .$cid . " AND allow_submit = 1
	                 ORDER BY probid";
	$probs = $DB->q($strSQL);
	$strSQL = "MAYBETUPLE SELECT probid, COUNT(*) as number FROM submission WHERE cid = " . $cid;
	$subs = $DB->q($strSQL);
									
	$strSQL = "SELECT testcaseid, input, output, rank FROM testcase INNER JOIN problem ON testcase.probid = problem.probid
									WHERE public = 1 AND cid = " . $cid;
	$tests = $DB->q($strSQL);
	
	return array( 'partecipants' => $teams,
	              'problems'   => $probs,
	              'testcases'	=> $tests,
	              'submissions' => $subs );
}

function genContestStats($cdata) {
	global $DB;

	$cid = $cdata['cid'];

	// get the teams and problems
	$teams = $DB->q('MAYBETUPLE SELECT probid, count(*) as numpartecipants
					from (SELECT probid, teamid, count(*) as number 
						FROM submission
						WHERE cid = %i 
						group by probid, teamid) as teams
						GROUP BY probid', $cid);
	$strSQL = "MAYBETUPLE SELECT probid, count(*) as numpartecipants
					from (SELECT probid, teamid, count(*) as number 
						FROM submission
						WHERE cid = " . $cid . " group by probid, teamid) as teams
						GROUP BY probid";
	$teams = $DB->q($strSQL);

	$strSQL = "SELECT probid, name, color, longdescription FROM problem
	                 WHERE cid = " .$cid . " ORDER BY probid";
	$probs = $DB->q($strSQL);

	$strSQL = "MAYBETUPLE SELECT probid, COUNT(*) as number FROM submission WHERE cid = " . $cid;
	$subs = $DB->q($strSQL);
										
	return array( 'partecipants' => $teams,
	              'problems'   => $probs,
	              'submissions' => $subs );
}


/**
 * Output the general scoreboard based on the cached data in table
 * 'scoreboard'. $myteamid can be passed to highlight a specific row.
 * If this function is called while IS_JURY is defined, the scoreboard
 * will always be current, regardless of the freezetime setting in the
 * contesttable. $static generates output suitable for static html pages.
 */
function renderContestFull($cdata, $sdata, $myteamid = null, $static = FALSE) {
	setlocale(LC_TIME, "it_IT");
	if ( empty( $cdata ) ) { echo "<p class=\"nodata\">No active contest</p>"; return; }
	$cid = $cdata['cid'];

	// 'unpack' the contest data:
	$teams   = $sdata['partecipants'];
	$probs   = $sdata['problems'];
	$tests   = $sdata['testcases'];
	$subs  = $sdata['submissions'];
	unset($sdata);

	// Show final scores if contest is over and unfreezetime has been
	// reached, or if contest is over and no freezetime had been set.
	// We can compare $now and the dbfields stringwise.
	$now = now();
	$showfinal = 0;
	$cstarted = 1;
	$daysleft = (int)((strtotime($cdata['endtime']) - strtotime($now)) / (24 * 3600));
	/* single problem per challenge */
	$rowprob = $probs->next();
	$problemtitle = $rowprob['name'];
	$problemdescription = $rowprob['longdescription'];

	// page heading with contestname and start/endtimes
	echo "<h1>" . htmlspecialchars($cdata['contestname']) . "</h1>";
	echo "<div class='content_box'>";
	echo "<h2 class='challenges_wd black'>" . $problemtitle . "</h2>";
	echo "<p class='right time_remaining'>tempo rimasto: ";
	if($daysleft == 1) 
		echo "<span> ". $daysleft . " giorno</span></p>";
	else
		echo "<span> ". $daysleft . " giorni</span></p>";
	if ( $showfinal ) {
		echo "<h3>Statistiche finali</h3>";
	} elseif ( ! $cstarted ) {
		echo "<h3>La sfida avr&agrave; inizio " . printtime($cdata['starttime']) . "</h3>";
		// Stop here (do not leak problem number, descriptions etc).
		// Alternatively we could only display the list of teams?
		if ( ! IS_JURY ) return;
	} else {
		echo "<h3>La sfida &egrave; aperta:</h3>" . 
				"<span class='challenge_open'>" . strftime("%e %B", strtotime($cdata['starttime'])) .
				" - " . strftime("%e %B %G", strtotime($cdata['endtime'])) . "</span>&nbsp;&nbsp;<span class='challenge_timezone'>(termina alle " . printtime($cdata['endtime']) .")</span>";

	}

	//Instructions
	echo "<div class='clear top bottom'>";
	echo "<h3 class='black'>Come sottomettere la soluzione:</h3>";
	echo "<ol><li>Leggi <a href='#problemdescription'>il problema</a></li>";
	echo "<ol><li>Prestate molta attenzione alle specifiche, effettuando solamente le stampe a video necessarie ";
	echo "(il programma non interagisce con un utente umano e tutto ci&ograve; che viene visualizzato viene confrontato ";
	echo "con il risultato atteso: attenetevi alle specifiche).</li>";
	echo "<li>Scegli il linguaggio che preferisci tra quelli consentiti (attualmente solo il <a href='./language.php?id=c'>linguaggio C</a>)</li>";
	echo "<li>Scrivi e collauda algoritmo e codice usando anche i <a href='#testcases'>casi di test</a> proposti (fai molta attenzione agli ingressi ed alle uscite e non introdurre stampe non richieste)</li>";
	echo "<li>Invia il codice seguendo il <a href='../team/websubmit.php'>link</a> (&egrave; necessario essere utenti registrati ed autenticati)</li></ol>";
	echo "</div>"; /* clear top bottom */
	echo "<p class='clear'></p>";
	echo "<div class='boxinthemiddle'>"; 
	echo "<div class='challenge_info left'><p>". fixedDigitFormat($teams['numpartecipants'],3) ."</p><h4>partecipanti</h4></div>";
	echo "<div class='challenge_info follow left'><p>". fixedDigitFormat($subs['number'],3) ."</p><h4>soluzioni inviate</h4></div>";
	echo "<div class='clear'></div>";
	echo "<img src='../images/look/2col_block_shadow_bottom.png' width='320px;' class='img_right_block_shadow_bottom'>";
	echo "<div class='clear'></div>";
	echo "</div>"; /* boxinthemiddle */
	echo "<hr class='points_line bottom clear'/>";
	echo "</div>"; /* content_box */
	
	// next part
	
	echo "<div class='content_box'>";
	echo "<div class='coding'>";
	echo "<div class='description'>";
	echo "<a name='problemdescription'></a><h3  class='black'>Descrizione</h3>";
	echo "<div class='coding'>" . $problemdescription . "</div>";
	echo "<a name='testcases'></a><h3  class='black'>Casi di test</h3>";
	echo "<p>Sono disponibili alcuni casi di test per poter collaudare il proprio algoritmo e programma prima di sottometterlo.</p>";
	echo "<p>Esegui il tuo programma utilizzando i dati in ingresso e verifica che l'uscita prodotta sia quella indicata.</p>";
	echo "<table id='testcases'>"; 
	echo "<thead><tr><th style='text-align: left;'>ingresso</th><th style='text-align: left;'>uscita attesa</th></tr></thead>"; 
	$i = 0;
	while($row = $tests->next()){
		if($i % 2)
			echo "<tr class='testcase-odd'><td>" . $row['input'] . "</td><td>" . $row['output'] . "</td></tr>";
		else
			echo "<tr class='testcase-even'><td>" . $row['input'] . "</td><td>" . $row['output'] . "</td></tr>";		
	}
	echo "</table>"; 
	echo "</div>"; /* description */
	echo "</div>"; /* coding */
	echo "</div>"; /* content_box */	
	echo "</div>"; /* main */
	return;
}

/**
 * Very simple wrapper around the contest data-generating and
 * rendering functions.
 */
function putContestFull($cdata, $myteamid = null, $static = FALSE) {

	$sdata = genContestFull($cdata);
	renderContestFull($cdata,$sdata,$myteamid,$static);
}

/**
 * Wraps up some stats on the latest closed contest
 */
function putContestStats($cdata, $myteamid = null, $static = FALSE) {

	$sdata = genContestStats($cdata); 
	renderContestStats($cdata,$sdata,$myteamid,$static);
}



function renderContestStats($cdata, $sdata, $myteamid = null, $static = FALSE) {
	// 'unpack' the contest data:
	$teams   = $sdata['partecipants'];
	$probs   = $sdata['problems'];
	$subs  = $sdata['submissions'];
	unset($sdata);

	// Show final scores if contest is over and unfreezetime has been
	// reached, or if contest is over and no freezetime had been set.
	// We can compare $now and the dbfields stringwise.
	$now = now();
	$rowprob = $probs->next();
	$problemtitle = $rowprob['name'];

	// page heading with contestname and start/endtimes
	echo "<h1>" . htmlspecialchars($cdata['contestname']) . "</h1>";
	echo "<div class='content_box'>";
	echo "<h2 class='challenges_wd black'>" . $problemtitle . "</h2>";
	echo "<div class='clear top bottom'>";
	echo "<div class='description'>";
	echo "<a name='problemdescription'></a><h3  class='black'>Descrizione</h3>";
	echo "<div class='coding'>" . substr($rowprob['longdescription'],0,500) . " ...</div>";
	echo "</div>"; /* description */
	echo "<p class='clear'></p>";
	echo "<div class='boxinthemiddle'>"; 
	echo "<div class='challenge_info left'><p>". fixedDigitFormat($teams['numpartecipants'],3) ."</p><h4>partecipanti</h4></div>";
	echo "<div class='challenge_info follow left'><p>". fixedDigitFormat($subs['number'],3) ."</p><h4>soluzioni inviate</h4></div>";
	echo "<div class='clear'></div>";
	echo "<img src='../images/look/2col_block_shadow_bottom.png' width='320px;' class='img_right_block_shadow_bottom'>";
	echo "<div class='clear'></div>";
	echo "</div>"; /* boxinthemiddle */
	echo "<hr class='points_line bottom clear'/>";
	echo "</div>"; /* content_box */
	
	echo "</div>"; /* main_content */
	echo "</div>"; /*main*/
	return;
}


/**
 * Returns a string containing an integer formatted on a fixed number
 * of digits
 */
function fixedDigitFormat($val, $ndig){
	$po = 1;
	$dig = 0;
	while($val / $po > 1){
		$po = $po * 10;
		$dig++;
	}
	$str = "";
	while($dig < $ndig){
		$str .= "0";
		$dig++;
	}
	return $str . strval($val);
}

function isContestClosed($cdata){
	$end = $cdata['endtime'];
	$now = now();
	$daysleft = (int)((strtotime($end) - strtotime($now)) / (24 * 3600));
	if($daysleft > 0)
		return 1;
	return 0;
}


function getContextStartTime($cid)
{
	global $DB;
	$strSQL = "SELECT starttime
				FROM contest
				WHERE cid = " . $cid . ";";
	$sdate = $DB->q($strSQL);
	return $sdate;

}