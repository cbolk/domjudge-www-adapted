<?php
/**
 * View of one contest.
 *
 * $Id: contest.php 3209 2010-06-12 00:13:43Z eldering $
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

	require(LIBWWWDIR . '/forms.php');

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
<tr><th class='spec' scope='row'><label for="data_0__activatetime_">Activate time:</label></th>
<td><?php echo addInput('data[0][activatetime]', @$row['activatetime'], 20, 19)?> (yyyy-mm-dd hh:mm:ss)</td></tr>

<tr><th class='spec' scope='row'><label for="data_0__starttime_">Start time:</label></th>
<td><?php echo addInput('data[0][starttime]', @$row['starttime'], 20, 19)?> (yyyy-mm-dd hh:mm:ss)</td></tr>

<tr><th class='spec' scope='row'><label for="data_0__freezetime_">Scoreboard freeze time:</label></th>
<td><?php echo addInput('data[0][freezetime]', @$row['freezetime'], 20, 19)?> (yyyy-mm-dd hh:mm:ss)</td></tr>

<tr><th class='spec' scope='row'><label for="data_0__endtime_">End time:</label></th>
<td><?php echo addInput('data[0][endtime]', @$row['endtime'], 20, 19)?> (yyyy-mm-dd hh:mm:ss)</td></tr>

<tr><th class='spec' scope='row'><label for="data_0__unfreezetime_">Scoreboard unfreeze time:</label></th>
<td><?php echo addInput('data[0][unfreezetime]', @$row['unfreezetime'], 20, 19)?> (yyyy-mm-dd hh:mm:ss)</td></tr>

<tr><th class='spec' scope='row'>Enabled:</th><td>
<?php echo addRadioButton('data[0][enabled]', (!isset($row['enabled']) ||  $row['enabled']), 1)?> <label for="data_0__enabled_1">yes</label>
<?php echo addRadioButton('data[0][enabled]', ( isset($row['enabled']) && !$row['enabled']), 0)?> <label for="data_0__enabled_0">no</label></td></tr>

</table>

<?php
echo addHidden('cmd', $cmd) .
	addHidden('table','contest') .
	addHidden('referrer', @$_GET['referrer']) .
	addSubmit('Save') .
	addSubmit('Cancel', 'cancel') .
	addEndForm();

require(LIBWWWDIR . '/footer.php');
exit;

endif;

if ( ! $id ) error("Missing or invalid contest id");


$data = $DB->q('TUPLE SELECT * FROM contest WHERE cid = %i', $id);

echo "<h1>Contest: ".htmlspecialchars($data['contestname'])."</h1>\n\n";

if ( $cid == $data['cid'] ) {
	echo "<p><em>This is the current contest.</em></p>\n\n";
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
	htmlspecialchars(@$data['activatetime']) .
	"</td></tr>\n";
echo '<tr><th class="spec" scope="row">Start time:</th><td>' .
	htmlspecialchars($data['starttime']) .
	"</td></tr>\n";
echo '<tr><th class="spec" scope="row">Scoreboard freeze:</th><td>' .
	(empty($data['freezetime']) ? "-" : htmlspecialchars(@$data['freezetime'])) .
	"</td></tr>\n";
echo '<tr><th class="spec" scope="row">End time:</th><td>' .
	htmlspecialchars($data['endtime']) .
	"</td></tr>\n";
echo '<tr><th class="spec" scope="row">Scoreboard unfreeze:</th><td>' .
	(empty($data['unfreezetime']) ? "-" : htmlspecialchars(@$data['unfreezetime'])) .
	"</td></tr>\n";
echo "</table>\n\n";

if ( IS_ADMIN ) {
	echo "<p>" .
		editLink('contest',$data['cid']) . "\n" .
		delLink('contest','cid',$data['cid']) ."</p>\n\n";
}

require(LIBWWWDIR . '/footer.php');
