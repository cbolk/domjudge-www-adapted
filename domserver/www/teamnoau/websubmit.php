<?php
/**
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 *
 * CB: not existing in the original new version
 */

require('init.php');

$title = 'Submit';
require(LIBWWWDIR . '/header.php');
require(LIBWWWDIR . '/forms.php');
$refreshtime = 120;

$fdata = calcFreezeData($cdata);

echo "<script type=\"text/javascript\">\n<!--\n";

if ( ENABLE_WEBSUBMIT_SERVER && $fdata['cstarted'] ) {
	$probdata = $DB->q('KEYVALUETABLE SELECT probid, CONCAT(probid,": ",name) as name FROM problem
			 WHERE cid = %i AND allow_submit = 1
			 ORDER BY probid', $cid);

	echo "function getMainExtension(ext)\n{\n";
	echo "\tswitch(ext) {\n";
	foreach($langexts as $ext => $langid) {
		echo "\t\tcase '" . $ext . "': return '" . $langid . "';\n";
	}
	echo "\t\tdefault: return '';\n\t}\n}\n\n";

	echo "function getProbDescription(probid)\n{\n";
	echo "\tswitch(probid) {\n";
	foreach($probdata as $probid => $probname) {
		echo "\t\tcase '" . htmlspecialchars($probid) . "': return '" . htmlspecialchars($probname) . "';\n";
	}
	echo "\t\tdefault: return '';\n\t}\n}\n\n";
}

echo "initReload(" . $refreshtime . ");\n";
echo "// -->\n</script>\n";

echo "<h1>new submission</h1>";

// Put overview of team submissions (like scoreboard)
echo "<div id=\"teamscoresummary\">\n";
putTeamRow($cdata, $login);
echo "</div>\n";

if ( ENABLE_WEBSUBMIT_SERVER && $fdata['cstarted'] ) {
	if ( $submitted ) {
		echo "<p class=\"submissiondone\">submission done <a href=\"./\" style=\"color: red\">x</a></p>\n\n";
	} else {
		echo addForm('upload.php','post',null,'multipart/form-data', null, ' onreset="resetUploadForm('.$refreshtime .');"') .
		"<p id=\"submitform\">\n\n" .
		"<span class=\"fileinputs\">\n\t" .
		"<input type=\"file\" name=\"code[]\" id=\"maincode\" size=\"15\" /> " .
		"\n</span>\n";

		echo "<script type=\"text/javascript\">initFileUploads();</script>\n\n";


		$probs = array();
		foreach($probdata as $probid => $probname) {
			$probs[$probid]= $probname;
		}
		$probs[''] = 'problem';
		echo addSelect('probid', $probs, '', true);
		
		$langs = $DB->q('KEYVALUETABLE SELECT langid, name FROM language
				 WHERE allow_submit = 1 ORDER BY name');
		$langs[''] = 'language';
		echo addSelect('langid', $langs, '', true);

		echo addSubmit('submit', 'submit',
			       "return checkUploadForm();");

		echo addReset('cancel');

		if ( dbconfig_get('sourcefiles_limit',100) > 1 ) {
			echo "<br /><span id=\"auxfiles\"></span>\n" .
			    "<input type=\"button\" name=\"addfile\" id=\"addfile\" " .
			    "value=\"Add another file\" onclick=\"addFileUpload();\" " .
			    "disabled=\"disabled\" />\n";
		}

		echo "</p>\n</form>\n\n";
	}
}

require(LIBWWWDIR . '/footer.php');
