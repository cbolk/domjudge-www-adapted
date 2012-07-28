<?php
/**
 * View a problem
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

$pagename = basename($_SERVER['PHP_SELF']);

require('init.php');

$id = @$_REQUEST['id'];
$title = 'Problem '.htmlspecialchars(@$id);

if ( ! preg_match('/^' . IDENTIFIER_CHARS . '*$/', $id) ) error("Invalid problem id");

if ( isset($_POST['cmd']) ) {
	$pcmd = $_POST['cmd'];
} elseif ( isset($_GET['cmd'] ) ) {
	$cmd = $_GET['cmd'];
} else {
	$refresh = '30;url='.$pagename.'?id='.urlencode($id);
}

if ( !empty($pcmd) ) {

	if ( empty($id) ) error("Missing problem id");

	if ( isset($pcmd['toggle_submit']) ) {
		$DB->q('UPDATE problem SET allow_submit = %i WHERE probid = %s',
			   $_POST['val']['toggle_submit'], $id);
			auditlog('problem', $id, 'set allow submit', $_POST['val']['toggle_submit']);
	}

	if ( isset($pcmd['toggle_judge']) ) {
		$DB->q('UPDATE problem SET allow_judge = %i WHERE probid = %s',
			   $_POST['val']['toggle_judge'], $id);
			auditlog('problem', $id, 'set allow judge', $_POST['val']['toggle_judge']);
	}
}
if ( isset($_POST['upload']) ) {
	if ( !empty($_FILES['problem_archive']['name']) ) {
		checkFileUpload( $_FILES['problem_archive']['error'] );
		$zip = openZipFile($_FILES['problem_archive']['tmp_name']);
		$id = importZippedProblem($zip, empty($id) ? NULL : $id);
		$zip->close();
		auditlog('problem', $id, 'upload zip', $_FILES['problem_archive']['name']);
		header('Location: '.$pagename.'?id='.urlencode($id));
	} else {
		error("Missing filename for problem upload");
	}
}

$jscolor=true;

require(LIBWWWDIR . '/header.php');

if ( IS_ADMIN && !empty($cmd) ):

	echo "<h2>" .  htmlspecialchars(ucfirst($cmd)) . " problem</h2>\n\n";

	echo addForm('edit.php', 'post', null, 'multipart/form-data');

	echo "<table id='tablespec'>\n";
	echo "<tr><th></th><th></th></tr>";
	if ( $cmd == 'edit' ) {
		echo "<tr><th class='spec' scope='row'>Problem ID:</th><td class=\"probid\">";
		$row = $DB->q('TUPLE SELECT p.*, COUNT(testcaseid) AS testcases
		               FROM problem p
		               LEFT JOIN testcase USING (probid)
		               WHERE probid = %s GROUP BY probid', $id);
		echo addHidden('keydata[0][probid]', $row['probid']);
		echo htmlspecialchars($row['probid']);
	} else {
		echo "<tr><th class='spec' scope='row'><label for=\"data_0__probid_\">Problem ID:</label></th><td>";
		echo addInput('data[0][probid]', null, 8, 10);
		echo " (alphanumerics only)";
	}
	echo "</td></tr>\n";

?>
<tr><th class='spec' scope='row'><label for="data_0__cid_">Contest:</label></th>
<td><?php
$cmap = $DB->q("KEYVALUETABLE SELECT cid,contestname FROM contest ORDER BY cid");
echo addSelect('data[0][cid]', $cmap, @$row['cid'], true);
?>
</td></tr>

<tr><th class='spec' scope='row'><label for="data_0__name_">Problem name:</label></th>
<td><?php echo addInput('data[0][name]', @$row['name'], 30, 255)?></td></tr>

<tr><th class='spec' scope='row'>Allow submit:</th>
<td><?php echo addRadioButton('data[0][allow_submit]', (!isset($row['allow_submit']) || $row['allow_submit']), 1)?> <label for="data_0__allow_submit_1">yes</label>
<?php echo addRadioButton('data[0][allow_submit]', (isset($row['allow_submit']) && !$row['allow_submit']), 0)?> <label for="data_0__allow_submit_0">no</label></td></tr>

<tr><th class='spec' scope='row'>Allow judge:</th>
<td><?php echo addRadioButton('data[0][allow_judge]', (!isset($row['allow_judge']) || $row['allow_judge']), 1)?> <label for="data_0__allow_judge_1">yes</label>
<?php echo addRadioButton('data[0][allow_judge]', (isset($row['allow_judge']) && !$row['allow_judge']), 0)?> <label for="data_0__allow_judge_0">no</label></td></tr>
<?php
    if ( !empty($row['probid']) ) {
		echo '<tr><th class="spec" scope="row">Testcases:</th><td>' .
			$row['testcases'] . ' <a href="testcase.php?probid=' .
			urlencode($row['probid']) . "\">details/edit</a></td></tr>\n";
	}
?>
<tr><th class='spec' scope='row'><label for="data_0__timelimit_">Timelimit:</label></th>
<td><?php echo addInput('data[0][timelimit]', @$row['timelimit'], 5, 5)?> sec</td></tr>

<tr><th class='spec' scope='row'><label for="data_0__color_">Balloon colour:</label></th>
<td><?php echo addInputField('text','data[0][color]', @$row['color'],
	' size="8" maxlength="25" class="color {required:false,adjust:false,hash:true,caps:false}"')?>
<a target="_blank"
href="http://www.w3schools.com/css/css_colornames.asp"><img
src="../images/b_help.png" class="smallpicto" alt="?" /></a></td></tr>

<tr><th class='spec' scope='row'><label for="data_0__special_run_">Special run script:</label></th>
<td><?php echo addInput('data[0][special_run]', @$row['special_run'], 30, 25)?></td></tr>

<tr><th class='spec' scope='row'><label for="data_0__special_compare_">Special compare script:</label></th>
<td><?php echo addInput('data[0][special_compare]', @$row['special_compare'], 30, 25)?></td></tr>

<tr><th class='spec' scope='row'><label for="data_0__longdescription_">Problem description:</label></th>
<td><?php echo addTextArea('data[0][longdescription]', @$row['longdescription'], 70, 25)?></td></tr>

</table>

<?php
echo addHidden('cmd', $cmd) .
	addHidden('table','problem') .
	addHidden('referrer', @$_GET['referrer']) .
	addSubmit('Save') .
	addSubmit('Cancel', 'cancel') .
	addEndForm();


if ( class_exists("ZipArchive") ) {
	echo "<br /><span style=\"font-style:italic;\">or</span><br /><br />\n" .
	addForm('problem.php', 'post', null, 'multipart/form-data') .
	addHidden('id', @$row['probid']) .
	'<label for="problem_archive">Upload problem archive:</label>' .
	addFileField('problem_archive') .
	addSubmit('Upload', 'upload') .
	addEndForm();
}

require(LIBWWWDIR . '/footer.php');
exit;

endif;

$data = $DB->q('TUPLE SELECT p.*, c.contestname, count(rank) AS ntestcases
                FROM problem p
                NATURAL JOIN contest c
                LEFT JOIN testcase USING (probid)
                WHERE probid = %s GROUP BY probid', $id);

if ( ! $data ) error("Missing or invalid problem id");

echo "<div class='right'><a href='./problem_detail.php?id=$id'>show formatted</a></div>";
echo "<h1>Problem ".htmlspecialchars($id)."</h1>\n\n";

echo addForm($pagename, 'post', null, 'multipart/form-data') . "<p>\n" .
	addHidden('id', $id) .
	addHidden('val[toggle_judge]',  !$data['allow_judge']) .
	addHidden('val[toggle_submit]', !$data['allow_submit']).
	"</p>\n";
?>
<table id="tablespec" cellspacing="0">
<tr><th scope='col'></th><th scope='col'></th></tr>
<tr><th class='spec' scope="row">ID:</th><td class="probid"><?php echo htmlspecialchars($data['probid'])?></td></tr>
<tr><th class='spec' scope="row">Name:</th><td><?php echo htmlspecialchars($data['name'])?></td></tr>
<tr><th class='spec' scope="row">Contest:</th><td><?php echo htmlspecialchars($data['contestname']) .
									' (c' . htmlspecialchars($data['cid']) .')'?></td></tr>
<tr><th class='spec' scope="row">Allow submit:</th><td class="nobreak"><?php echo printyn($data['allow_submit']) . ' '.
	addSubmit('toggle', 'cmd[toggle_submit]',
		"return confirm('" . ($data['allow_submit'] ? 'Disallow' : 'Allow') .
		" submissions for this problem?')"); ?>
</td></tr>
<tr><th class='spec' scope="row">Allow judge: </th><td><?php echo printyn($data['allow_judge']) . ' '.
	addSubmit('toggle', 'cmd[toggle_judge]',
		"return confirm('" . ($data['allow_judge'] ? 'Disallow' : 'Allow') .
		" judging for this problem?')"); ?>
</td></tr>
<tr><th class='spec' scope="row">Testcases:   </th><td><?php
    if ( $data['ntestcases']==0 ) {
		echo '<em>no testcases</em>';
	} else {
		echo (int)$data['ntestcases'];
	}
	echo ' <a href="testcase.php?probid='.urlencode($data['probid']).'">details/edit</a>';
?></td></tr>
<tr><th class='spec' scope="row">Timelimit:   </th><td><?php echo (int)$data['timelimit']?> sec</td></tr>
<?php
if ( !empty($data['color']) ) {
	echo '<tr><th class=\'spec\' scope="row">Colour:</th><td><img style="background-color: ' .
		htmlspecialchars($data['color']) .
		';" alt="problem colour ' . htmlspecialchars($data['color']) .
		'" src="../images/circle.png" /> ' . htmlspecialchars($data['color']) .
		"</td></tr>\n";
}
if ( !empty($data['special_run']) ) {
	echo '<tr><th class=\'spec\' scope="row">Special run script:</th><td class="filename">' .
		htmlspecialchars($data['special_run']) . "</td></tr>\n";
}
if ( !empty($data['special_compare']) ) {
	echo '<tr><th class=\'spec\' scope="row">Special compare script:</th><td class="filename">' .
		htmlspecialchars($data['special_compare']) . "</td></tr>\n";
}

echo '<tr><th class=\'spec\' scope="row">Problem description:</th><td class="filename">' .
	($data['longdescription']) . "</td></tr>\n";


if ( IS_ADMIN && class_exists("ZipArchive") ) {
	echo '<tr>' .
		'<td scope="row" class="spec">Problem archive:</td>' .
		'<td>' . addFileField('problem_archive') .
		addSubmit('Upload', 'upload') . '</td>' .
		"</tr>\n";
}

echo "</table>\n" . addEndForm();

echo "<br />\n" . rejudgeForm('problem', $id) . "\n\n";

if ( IS_ADMIN ) {
	echo "<p>" . editLink('problem',$id) . "\n" .
		delLink('problem','probid', $id) . "</p>\n\n";
}

echo "<h2>Submissions for " . htmlspecialchars($id) . "</h2>\n\n";

$restrictions = array( 'probid' => $id );
putSubmissions($cdata, $restrictions);

require(LIBWWWDIR . '/footer.php');
