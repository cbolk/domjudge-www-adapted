<?php
/**
 * Show source code from the database for all submissions to a problem.
 *
 * $Id: show_sources_problem.php 3577 2011-07-30 20:00:45Z eldering $
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 *
 * Added by CBolk
 */

function getSourceFile($sourcecode, $sourcefile){
	// Download was requested
		header("Content-Type: text/plain; name=\"$sourcefile\"; charset=" . DJ_CHARACTER_SET);
		header("Content-Disposition: inline; filename=\"$sourcefile\"");
		header("Content-Length: " . strlen($sourcecode));

		echo $sourcecode;
		exit;	
}

require('init.php');

$id = (int)$_GET['id'];

$strSQL = 'KEYTABLE SELECT s.submitid AS ARRAYKEY, t.name, t.login, s.langid, sourcecode FROM submission s
				INNER JOIN team t ON s.teamid = t.login
				 WHERE probid = ' . $id . ' ORDER BY s.submitid';
$sdata = $DB->q($strSQL);
if ( empty($sdata) ) error ("No submissions for problem $id");


$title = 'Sources for problem $id';
// Download was requested
if ( isset($_GET['fetch']) ) {
	$thecode = $sdata[$_GET['fetch']]['sourcecode'];
	header("Content-Type: text/plain; name=\"$sourcefile\"; charset=" . DJ_CHARACTER_SET);
	header("Content-Disposition: inline; filename=\"$sourcefile\"");
	header("Content-Length: " . strlen($thecode));

	echo $thecode;
	exit;
}

require(LIBWWWDIR . '/header.subs.php');


echo '<h1 class="filename"><a name="source"></a>Submissions for problem ' . $id .' <a>show all</a></h1>';
echo '<div class="submissionslist">';
foreach($sdata as $submitid => $sub){
	echo '<div id="sub_result_' . $submitid . '" class="sub_result" rel="' . $submitid . '">', PHP_EOL;
	echo '	<div id="sub_summary_' . $submitid . '" class="sub_summary" onclick="toggle_result(' . $submitid . ')">', PHP_EOL;
	echo '		<div class="title"><span class="arrow"></span>#' . $submitid  . ' by ' . $sub['login'] . ' ('. $sub['name'] . ')</div>', PHP_EOL;
	echo '<a href="' . $PHP_SELF .'?id=' .$id . '&fetch=' . $submitid.  '">download</a>', PHP_EOL;
	echo '	</div> <!-- sub_summary_ -->', PHP_EOL;
	echo '	<div id="sub_detail_' . $submitid . '" class="sub_detail" style="display:none;">', PHP_EOL;
	echo '		<pre class="brush: ' . $sub['langid'] . '">', PHP_EOL;
	echo htmlspecialchars($sub['sourcecode']);
	echo '		</pre>';
	echo '	</div> <!-- sub_detail_ -->', PHP_EOL;
	echo '<div class="footer"></div>';
	echo '</div> <!-- sub_ -->', PHP_EOL;
}
echo '</div> <!-- codelist -->', PHP_EOL;

echo '<script type="text/javascript">SyntaxHighlighter.all()</script>';
require(LIBWWWDIR . '/footer.php');
