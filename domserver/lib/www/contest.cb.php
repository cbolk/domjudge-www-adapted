<?php

/**
 * Functions for public contest information.
 *
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
	$strSQL = "SELECT probid, count(*) as numpartecipants
						from (SELECT probid, teamid, count(*) as number 
						FROM submission
						WHERE cid = " . $cid . " group by probid, teamid) as teams
						GROUP BY probid";
	$teams = $DB->q($strSQL);

	$strSQL = "SELECT probid, name, color, shortdescription, longdescription FROM problem
	                 WHERE cid = " .$cid . " AND allow_submit = 1
	                 ORDER BY probid";
	$probs = $DB->q($strSQL);


	$strSQL = "MAYBETUPLE SELECT probid, COUNT(*) as number FROM submission WHERE cid = " . $cid;
	$subs = $DB->q($strSQL);
									
	$strSQL = "SELECT testcaseid, testcase.probid, input, output, ifile, ofile, rank FROM testcase INNER JOIN problem ON testcase.probid = problem.probid
									WHERE public = 1 AND cid = " . $cid . " ORDER BY probid, rank";
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
	$strSQL = "SELECT probid, count(*) as numpartecipants
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
	if ( empty( $cdata ) ) { 
		echo "<p class=\"nodata\">No active contest</p>"; 
		return; 
	}
	$cid = $cdata['cid'];

	// 'unpack' the contest data:
	$teams   = $sdata['partecipants'];
	$probs   = $sdata['problems'];
	$tests   = $sdata['testcases'];
	$subs  = $sdata['submissions'];
	unset($sdata);

	$nprobs = 0;
	$prarray = array();
	while($problem = $probs->next()){
		$prarray[$nprobs] = $problem;
		$nprobs++;
	}

	$nparts = 0;
	while($part = $teams->next()){
		$nparts = $nparts + $part['numpartecipants'];
	}

	if($nprobs > 1){	
		echo "<div id='slider4'>\n";
		echo "<div class='right'>\n";
		echo "<ul class='pager'>\n";
		echo "<li><a rel='0' href='#pd1' class='pagenum active'>Problema 1</a></li>\n";
		for($i = 1; $i < $nprobs; $i++)
			echo "<li><a rel='" . $i . "' href=\"#pd" . ($i + 1) ."\" class='pagenum'>Problema " . ($i + 1) . "</a></li>\n";
		echo "</ul>\n";
		echo "</div>\n";		
		echo "<div class='clear'></div>\n";
/*		echo "<div class='viewport'>\n";
		echo "<ul class='overview'>\n";
		for($i = 0; $i < $nprobs; $i++){
			echo "<li class='page'>";
			renderProblemShort($prarray[$i], $static);
			echo "</li>\n";
		}
		echo "</ul>\n";
		echo "</div> <!-- viewport -->\n";
		echo "</div> <!-- slider -->\n";
*/
		echo "<hr class='points_line' />\n";
	} 

	//testcases per problem
	$tdata = array();
	$i =0;
	while($testcase = $tests->next()){
	    $tdata[$testcase['probid']][$testcase['rank']] = $testcase;
	    $i= $i+1;
	}
	
	// Show final scores if contest is over and unfreezetime has been
	// reached, or if contest is over and no freezetime had been set.
	// We can compare $now and the dbfields stringwise.
	$now = now();
	$showfinal = 0;
	$cstarted = 1;
	$daysleft = (int)((strtotime($cdata['endtime']) - strtotime($now)) / (24 * 3600));
	$timeleft = (int)((strtotime($cdata['endtime']) - strtotime($now)) / 3600);
	/* single problem per challenge */
	// page heading with contestname and start/endtimes
	echo "<div class='content_box'>\n";
	echo "<p class='right time_remaining'>tempo rimasto: ";
	if($daysleft == 1) 
		echo "<span> ". $daysleft . " giorno</span></p>";
	else if ($daysleft > 1)
		echo "<span> ". $daysleft . " giorni</span></p>";
	else /* hours and/or minutes */ 
		echo "<span>meno di un giorno</span></p>";
	if ( $showfinal ) {
		echo "<h3>Statistiche finali</h3>\n";
	} elseif ( ! $cstarted ) {
		echo "<h3>La sfida avr&agrave; inizio " . printtime($cdata['starttime']) . "</h3>";
		// Stop here (do not leak problem number, descriptions etc).
		// Alternatively we could only display the list of teams?
		if ( ! IS_JURY ) return;
	} else {
		echo "<h3>La consegna &egrave; aperta "  .  ":</h3>" . 
				"<span class='challenge_open'>" . strftime("%e %B", strtotime($cdata['starttime'])) .
				" - " . strftime("%e %B %G", strtotime($cdata['endtime'])) . "</span>&nbsp;&nbsp;<span class='challenge_timezone'>(termina alle " . printtime($cdata['endtime']) .")</span>";

	}
	
	//Instructions
	echo "<div class='clear top bottom'>";
	echo "<h3 class='black'>Come sottomettere la soluzione:</h3>";
	if($nprobs > 1){
		echo "<ol><li>Leggi i problemi</li>";
	} else {
		echo "<ol><li>Leggi <a href='#problemdescription'>il problema</a></li>";
	}
	echo "<ul><li>Prestate molta attenzione alle specifiche, effettuando solamente le stampe a video necessarie ";
	echo "(il programma non interagisce con un utente umano e tutto ci&ograve; che viene visualizzato viene confrontato ";
	echo "con il risultato atteso: attenetevi alle specifiche).</li></ul>";
	echo "<li>Scegli il linguaggio che preferisci tra quelli consentiti (attualmente solo il <a href='../team/language.php?id=c'>linguaggio C</a>)</li>";
	echo "<li>Scrivi e collauda algoritmo e codice usando anche i <a href='#testcases'>casi di test</a> proposti (fai molta attenzione agli ingressi ed alle uscite e non introdurre stampe non richieste)</li>";
	echo "<li>Invia il codice seguendo il <a href='../team/websubmit.php'>link</a> (&egrave; necessario essere utenti registrati ed autenticati)</li>";
	echo "<li>Verifica il risultato della compilazione del tuo programma e l'esecuzione con i casi di test (quelli qua riportati oltre ad altri). Il significato del risultato &egrave; spiegato qui: <a href='../public/result.php'>link</a></li></ol>";
	echo "</div>"; /* clear top bottom */
	echo "<p class='clear'></p>";
	echo "<div class='boxinthemiddle'>"; 
	echo "<div class='challenge_info left'><p>". fixedDigitFormat($nparts,3) ."</p><h4>partecipanti</h4></div>";
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
	echo "<div class='description'><a name='problemdescription'></a>\n";
	
	// problem description
	if($nprobs == 1){	
		$pid = $prarray[0]['probid'];
		renderProblem($prarray[0], $tdata[$pid], TRUE, $static);
	} else {
		echo "\n<ul class='problemlist'>\n";
		for($i = 0; $i < $nprobs; $i++){
			$pid = $prarray[$i]['probid'];
			echo "\n<li>";
			renderProblem($prarray[$i], $tdata[$pid], FALSE, $static);
			echo "</li>";
		}
		echo "</ul>";
	}
	
	echo "</div>"; /* description */
	echo "</div>"; /* coding */
	echo "</div>"; /* content_box */	
	echo "</div>"; /* main */
	return;
}

/**
 * Specification for the single problem
 *
 */
function renderProblem($pdata, $tedata, $unique =  FALSE, $static = FALSE) {

	$probid = $pdata['probid'];
	$problemtitle = $pdata['name'];
	$problemdescription = $pdata['longdescription'];
	
	echo "\n<div class='problem'>";
	echo "<h2><a name=\"pd$probid\"></a>" . $problemtitle . "</h2>";
	echo "<h3  class='black'>Descrizione</h3>";
	echo "<div class='coding'>" . $problemdescription . "</div>\n";
	
	if(count($tedata) > 0){
		if($unique == TRUE){
			echo "<a name='testcases'></a><h3 class='black'>Casi di test del problema</h3>";
		} else {
			echo "<a name='testcases_$probid'></a><h3 class='black'>Casi di test del problema</h3>";
		}
		echo "<p>Sono disponibili alcuni casi di test per poter collaudare il proprio algoritmo e programma prima di sottometterlo.</p>";
		echo "<p>Esegui il tuo programma utilizzando i dati in ingresso e verifica che l'uscita prodotta sia quella indicata.</p>";
		echo "<p>L'esecuzione di collaudo viene effettuata nel seguente modo: i dati in ingresso vengono salvati 
		in un file (ad esempio <tt>datiin.txt</tt>) e viene chiamato il programma cos&igrave;:<br/>
		<pre class='brush;'>./programma &lt; datiin.txt</pre></p>";
		echo "\n<table class='testcases'>"; 
		echo "<thead><tr><th style='text-align: left;'>ingresso</th></tr></thead>"; 
		$i = 0;
		foreach($tedata as $rank => $row){
				if($i > 0)
					echo "<tr><th style='text-align: left;'>ingresso</th></tr>";
				if($i % 2)
					echo "<tr class='testcase-odd'>"; 
				else
					echo "<tr class='testcase-even'>";
				echo "<td class='vtop wmax50perc'>";
				if($row['ifile'] == NULL || $row['ifile'] == "")
					echo "<pre class='brush;'>" . htmlspecialchars($row['input']) . "</pre>";
				else
					echo "<pre><a href='../public/debug/p" . $probid . "/" . $row['ifile'] . "'>" . $row['ifile'] . "</a></pre>";
				echo "</td></tr>";
			
				$strout = "<tr><th style='text-align: left;'>uscita attesa</th></tr>";
				$strout = $strout . "<tr><td class='vtop'>";
				if($row['ofile'] == NULL || $row['ofile'] == "")
					$strout = $strout . "<pre class='brush;'>" . htmlspecialchars($row['output']) . "</pre>";
				else
					$strout = $strout . "<a href='./debug/p" . $row['probid'] . "/" . $row['ofile'] . "'>" . $row['ofile'] . "</a>"; 
				
				echo $strout . "</td></tr>\n";
				$i = $i + 1;
		}
		echo "</table>\n"; 
	}
	echo "</div><!-- problem -->\n";
}


/**
 * Short description of the problem
 *
 */
function renderProblemShort($pdata, $static = FALSE) {
	$pid = $pdata['probid'];
	$problemtitle = $pdata['name'];
	$problemdescription = $pdata['shortdescription'];
	
	echo "\n<div class='shortproblem'>\n";
	echo "<a name='sd" . $pid . "'></a><h3>" . $problemtitle . "</h3>\n";
	echo "<p>" . $problemdescription . "</p>\n";
	echo "<p><a href=\"#pd" . $pid . "\">dettagli</a></p>\n";
	echo "</div>\n";
	echo "<div class='clear'>&nbsp;</div>\n";
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

	$nparts = 0;
	while($part = $teams->next()){
		$nparts = $nparts + $part['numpartecipants'];
	}

	// Show final scores if contest is over and unfreezetime has been
	// reached, or if contest is over and no freezetime had been set.
	// We can compare $now and the dbfields stringwise.
	$now = now();
	$rowprob = $probs->next();
	$problemtitle = $rowprob['name'];

	// page heading with contestname and start/endtimes
	echo "<div class='content_box'>";
	echo "<h2 class='hblue'>Specifica problema</h2>";
	echo "<h2 class='challenges_wd black'>" . $problemtitle . "</h2>";
	echo "<div class='clear top bottom'>";
	echo "<div class='description'>";
	echo "<a name='problemdescription'></a><h3  class='black'>Descrizione</h3>";
	echo "<div class='coding'>" . substr($rowprob['longdescription'],0,500) . " ...</div>";
	echo "</div>"; /* description */
	echo "<p class='clear'></p>";
	echo "<h2 class='hblue'>Partecipazione</h2>";
	echo "<div class='boxinthemiddle'>"; 
	echo "<div class='challenge_info left'><p>". fixedDigitFormat($nparts,3) ."</p><h4>partecipanti</h4></div>";
	echo "<div class='challenge_info follow left'><p>". fixedDigitFormat($subs['number'],3) ."</p><h4>soluzioni inviate</h4></div>";
	echo "<div class='clear'></div>";
	echo "<img src='../images/look/2col_block_shadow_bottom.png' width='320px;' class='img_right_block_shadow_bottom'>";
	echo "<div class='clear'></div>";
	echo "</div>"; /* boxinthemiddle */
	echo "<hr class='points_line bottom clear'/>";
	echo "</div>"; /* content_box */
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
	if((strtotime($end) - strtotime($now)) > 0)
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

function getContestFromProblem($probid)
{
	global $DB;
	$strSQL = "VALUE SELECT cid
				FROM problem
				WHERE probid = " . $probid . ";";
	$cid = $DB->q($strSQL);
	return $cid;
}

function putProblemAsViewed($probid)
{
	global $DB;
	$cid = getContestFromProblem($probid);
	putContestFull($cid, NULL, TRUE);
	return;
}