<?php
/**
 * View of one contest.
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

$id = (int)@$_GET['id'];

require('init.php');
$title = "Contest: " .htmlspecialchars(@$id);

require(LIBWWWDIR . '/header.php');

if ( IS_ADMIN && !empty($_GET['cmd']) ):
	$cmd = $_GET['cmd'];

	echo "<h2>" . htmlspecialchars(ucfirst($cmd)) . " contest</h2>\n\n";

	echo addForm('edit.php');

	echo "<table id='tablespec'>\n";
	echo "<tr><th></th><th></th></tr>";

	if ( $cmd == 'edit' ) {
		echo "<tr><th class='spec' scope='row'>Contest ID:</th><td>";
		$row = $DB->q('TUPLE SELECT * FROM contest WHERE cid = %s',
			$_GET['id']);
		echo addHidden('keydata[0][cid]', $row['cid']) .
			'c' . htmlspecialchars($row['cid']) .
			"</td></tr>\n";
	}

?>

<tr><th class='spec' scope='row'><label for="data_0__contestname_">Contest name:</label></th>
<td><?php echo addInput('data[0][contestname]', @$row['contestname'], 40, 255)?></td></tr>
<tr><th class='spec' scope='row'><label for="data_0__activatetime_string_">Activate time:</label></td>
<td><?php echo addInput('data[0][activatetime_string]', @$row['activatetime_string'], 20, 19)?> (yyyy-mm-dd hh:mm:ss <i>or</i> -hh:mm)</td></tr>

<tr><th class='spec' scope='row'><label for="data_0__starttime_">Start time:</label></th>
<td><?php echo addInput('data[0][starttime]', @$row['starttime'], 20, 19)?> (yyyy-mm-dd hh:mm:ss)</td></tr>

<tr><th class='spec' scope='row'><label for="data_0__freezetime_string_">Scoreboard freeze time:</label></th>
<td><?php echo addInput('data[0][freezetime_string]', @$row['freezetime_string'], 20, 19)?> (yyyy-mm-dd hh:mm:ss <i>or</i> +hh:mm)</td></tr>

<tr><th class='spec' scope='row'><label for="data_0__endtime_string_">End time:</label></th>
<td><?php echo addInput('data[0][endtime_string]', @$row['endtime_string'], 20, 19)?> (yyyy-mm-dd hh:mm:ss <i>or</i> +hh:mm)</td></tr>

<tr><th class='spec' scope='row'><label for="data_0__unfreezetime_string_">Scoreboard unfreeze time:</label></th>
<td><?php echo addInput('data[0][unfreezetime_string]', @$row['unfreezetime_string'], 20, 19)?> (yyyy-mm-dd hh:mm:ss <i>or</i> +hh:mm)</td></tr>

<tr><th class='spec' scope='row'>Enabled:</th><td>
<?php echo addRadioButton('data[0][enabled]', (!isset($row['enabled']) ||  $row['enabled']), 1)?> <label for="data_0__enabled_1">yes</label>
<?php echo addRadioButton('data[0][enabled]', ( isset($row['enabled']) && !$row['enabled']), 0)?> <label for="data_0__enabled_0">no</label></td></tr>

</table>

<?php
echo addHidden('cmd', $cmd) .
	addHidden('table','contest') .
	addHidden('referrer', @$_GET['referrer'] . ( $cmd == 'edit'?(strstr(@$_GET['referrer'],'?') === FALSE?'?edited=1':'&edited=1'):'')) .
	addSubmit('Save') .
	addSubmit('Cancel', 'cancel') .
	addEndForm();

require(LIBWWWDIR . '/footer.php');
exit;

endif;

if ( ! $id ) error("Missing or invalid contest id");

if ( isset($_GET['edited']) ) {

	echo addForm('refresh_cache.php') .
            msgbox (
                "Warning: Refresh scoreboard cache",
		"If the contest start time was changed, it may be necessary to recalculate any cached scoreboards.<br /><br />" .
		addSubmit('recalculate caches now', 'refresh') 
		) .
		addEndForm();

}


$data = $DB->q('TUPLE SELECT * FROM contest WHERE cid = %i', $id);

echo "<h1>Contest: ".htmlspecialchars($data['contestname'])."</h1>\n\n";

if ( $cid == $data['cid'] ) {
	echo "<p><em>This is the active contest.</em></p>\n\n";
}
if ( !$data['enabled'] ) {
	echo "<p><em>This contest is disabled.</em></p>\n\n";
}

echo "<table id='tablespec'>\n";
echo "<tr><th></th><th></th></tr>";
echo '<tr><th class="spec" scope="row">CID:</th><td>c' .
	(int)$data['cid'] . "</td></tr>\n";
echo '<tr><th class="spec" scope="row">Name:</th><td>' .
	htmlspecialchars($data['contestname']) .
	"</td></tr>\n";
echo '<tr><th class="spec" scope="row">Activate time:</th><td>' .
	htmlspecialchars(@$data['activatetime_string']) .
	"</td></tr>\n";
echo '<tr><th class="spec" scope="row">Start time:</th><td>' .
	htmlspecialchars($data['starttime']) .
	"</td></tr>\n";
echo '<tr><th class="spec" scope="row">Scoreboard freeze:</th><td>' .
	(empty($data['freezetime_string']) ? "-" : htmlspecialchars(@$data['freezetime_string'])) .
	"</td></tr>\n";
echo '<tr><th class="spec" scope="row">End time:</th><td>' .
	htmlspecialchars($data['endtime_string']) .
	"</td></tr>\n";
echo '<tr><th class="spec" scope="row">Scoreboard unfreeze:</th><td>' .
	(empty($data['unfreezetime_string']) ? "-" : htmlspecialchars(@$data['unfreezetime_string'])) .
	"</td></tr>\n";
echo "</table>\n\n";

if ( IS_ADMIN ) {
	if ( $cid == $data['cid'] ) {
		echo "<p>". rejudgeForm('contest', $data['cid']) . "</p>\n\n";
	}
	echo "<p>" .
		editLink('contest',$data['cid']) . "\n" .
		delLink('contest','cid',$data['cid']) ."</p>\n\n";
}

require(LIBWWWDIR . '/footer.php');
