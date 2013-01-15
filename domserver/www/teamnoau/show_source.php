<?php
/**
 * Show source code from the database.
 *
 * $Id: show_source.php 3577 2011-07-30 20:00:45Z eldering $
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 * Added and adapted by CBolk
 */

require('init.php');

$id = (int)$_GET['id'];

$source = $DB->q('MAYBETUPLE SELECT sourcecode, probid, langid, submittime 
				  FROM submission_file LEFT JOIN submission USING(submitid)
                  LEFT JOIN language USING(langid)
                  WHERE submitid = %i',$id);
if ( empty($source) ) error ("Submission $id not found");
$oldsource = $DB->q('MAYBETUPLE SELECT sourcecode, submission.langid 
					FROM submission_file LEFT JOIN submission USING(submitid)
                     LEFT JOIN language USING(langid)
                     WHERE teamid = %s AND probid = %s AND langid = %s AND
                     submittime < %s ORDER BY submittime DESC LIMIT 1',
                    $login,$source['probid'],$source['langid'],
                    $source['submittime']);


$thecode = $source['sourcecode'];
$oldcode = $oldsource['sourcecode'];

$title = 'Source: ' . $id;
require(LIBWWWDIR . '/header.php');
require(LIBWWWDIR . '/diff.php');


echo '<h2 class="filename"><a name="source"></a>Your submission ' . 
	"<a href=\"submission_details.php?id=$id\">s$id</a></h2>\n\n";

if ( strlen($thecode)==0 ) {
	// Someone submitted an empty file. Cope gracefully.
	echo "<p class=\"nodata\">empty file</p>\n\n";
} elseif ( strlen($thecode) < 10 * 1024 ) {
	echo '<pre class="brush: ' . $source['langid'] . '">';
	echo htmlspecialchars($thecode) ;
	echo '</pre>';
	echo '<p></p>';
} else {
	echo '<pre class="brush: ' . $source['langid'] . '">';
	echo htmlspecialchars($thecode) ;
	echo '</pre>';
	echo '<p></p>';
}
echo "<hr>&lt; <a href='submission_details.php?id=$id'>back</a>";
echo '<script type="text/javascript">SyntaxHighlighter.all()</script>';
require(LIBWWWDIR . '/footer.php');
