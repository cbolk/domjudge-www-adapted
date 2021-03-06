<?php
/**
 * Handle web submissions
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

/* for easy parsing of the status by the commandline websubmit client */
define('NONINTERACTIVE', isset($_REQUEST['noninteractive']));

require('init.php');
$title = 'Submit for ' . $login . ' and problem ' . $probid;

/** helper to output an error message. */
function err($string)
{
	// Annoying PHP: we need to import global variables here...
	global $title;
	if (NONINTERACTIVE) error($string);

	require(LIBWWWDIR . '/header.php');

	echo "<h2>Submit - error</h2>\n\n";

	echo '<div id="uploadstatus">';
	logmsg(LOG_WARNING, $string);
	echo '</div>';

	require(LIBWWWDIR . '/footer.php');
	exit;
}

if ( count($_FILES['code']['tmp_name']) > dbconfig_get('sourcefiles_limit',100) ) {
	err("Tried to submit more than the allowed number of source files.");
}

ini_set("upload_max_filesize", dbconfig_get('sourcesize_limit') * 1024);
// rebuild array of filenames, paths to get rid of empty upload fields
$FILEPATHS = $FILENAMES = array();
foreach($_FILES['code']['tmp_name'] as $fileid => $tmpname ) {
	if ( !empty($tmpname) ) {
		checkFileUpload($_FILES['code']['error'][$fileid]);
		$FILEPATHS[] = $_FILES['code']['tmp_name'][$fileid];
		$FILENAMES[] = $_FILES['code']['name'][$fileid];
	}
}

/* Determine the problem */
$probid = @$_POST['probid'];
$prob = $DB->q('MAYBETUPLE SELECT probid, name FROM problem
                WHERE allow_submit = 1 AND probid = %s', $probid);

if ( ! isset($prob) ) err("Unable to find problem $probid");
$probid = $prob['probid'];

/* Determine the student */
$login = @$_POST['login'];
$team = $DB->q('MAYBETUPLE SELECT login, name FROM team
                WHERE login = %s', $login);

/* Determine the language */
$langid = @$_POST['langid'];
$lang = $DB->q('MAYBETUPLE SELECT langid, name FROM language
                WHERE langid = %s AND allow_submit = 1', $langid);

if ( ! isset($lang) ) err("Unable to find language '$langid'");
$langid = $lang['langid'];

$sid = submit_solution_byjury($login, $probid, $langid, $FILEPATHS, $FILENAMES);

auditlog('submission', $sid, 'added by staff', NONINTERACTIVE?'noninteractive':null);

// Redirect back to index page when interactively used.
if ( !NONINTERACTIVE ) {
	header('Location: ' . $_SERVER['HTTP_REFERER'] );
}

require(LIBWWWDIR . '/header.php');

echo '<div id="uploadstatus">';
if (NONINTERACTIVE) echo '<!-- noninteractive-upload-successful -->';
echo "<p><a href=\"index.php?submitted=" . urlencode($sid) . "\">Submission successful.</a></p>";
echo "</div>\n";

require(LIBWWWDIR . '/footer.php');