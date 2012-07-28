<?php
/**
 * View the judgehosts
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require('init.php');
$title = 'Judgehosts';

require(LIBWWWDIR . '/header.php');

echo "<h1>List of judgehosts</h1>\n\n";

@$cmd = @$_REQUEST['cmd'];
if ( IS_ADMIN && (isset($_POST['cmd-activate']) || isset($_POST['cmd-deactivate']) ) ) {
	$DB->q('UPDATE judgehost SET active = %i',
	       (isset($_POST['cmd-activate']) ? 1:0));
	auditlog('judgehost', null, 'marked all ' . (isset($_POST['cmd-activate'])?'active':'inactive'));
}
if ( IS_ADMIN && ($cmd == 'add' || $cmd == 'edit') ) {
	require ( LIBWWWDIR . '/forms.php' ) ;
	echo addForm('edit.php');
	echo "\n<table id='tablespec'>\n" .
		"<tr><th>Hostname</th><th>Active</th><th>Edit</th></tr>\n";
	if ( $cmd == 'add' ) {
		for ($i=0; $i<10; ++$i) {
			echo "<tr><td>" .
				addInput("data[$i][hostname]", null, 20, 50) .
				"</td><td>" .
				addSelect("data[$i][active]",
					array(1=>'yes',0=>'no'), '1', true) .
				"</td></tr>\n";
		}
	} else {
		$res = $DB->q('SELECT * FROM judgehost ORDER BY hostname');
		$i = 0;
		while ( $row = $res->next() ) {
			echo "<tr><td>" .
				addHidden("keydata[$i][hostname]", $row['hostname']) .
				printhost($row['hostname']) .
				"</td><td>" .
				addSelect("data[$i][active]",
					array(1=>'yes',0=>'no'), $row['active'], true) .
				"</td></tr>\n";
			++$i;
		}
	}
	echo "</table>\n\n<br /><br />\n";
	echo addHidden('cmd', $cmd) .
		( $cmd == 'add' ? addHidden('skipwhenempty', 'hostname') : '' ) .
		addHidden('table','judgehost') .
		addSubmit('Save Judgehosts') .
		addEndForm();

	require(LIBWWWDIR . '/footer.php');
	exit;

}

$res = $DB->q('SELECT * FROM judgehost ORDER BY hostname');


if( $res->count() == 0 ) {
	echo "<p class=\"nodata\">No judgehosts defined</p>\n\n";
} else {
	echo "<table  id='tablespec' class=\"list sortable\">\n<thead>\n" .
	     "<tr><th scope=\"col\">hostname</th>" .
		 "<th scope=\"col\">active</th>" .
		 "<th class=\"sorttable_nosort\">status</th><th>delete</th></tr>\n" .
		 "</thead>\n<tbody>\n";
	while($row = $res->next()) {
		$link = '<a href="judgehost.php?id=' . urlencode($row['hostname']) . '">';
		echo "<tr".( $row['active'] ? '': ' class="disabled"').
			"><td>" . $link . printhost($row['hostname']) . '</a>' .
			"</td><td align=\"center\">" . $link . printyn($row['active']) .
			"</a></td>";
		echo "<td align=\"center\" class=\'imgmiddle'>";
		if ( empty($row['polltime'] ) ) {
			echo "<img width='22px' src='../images/judgehost-neverlogged.png' title ='never checked in' />";
		} else {
			$reltime = time() - strtotime($row['polltime']);
			if ( $reltime < 30 ) {
				echo "<img width='22px' src='../images/judgehost-ok.png' title='judgehost-ok: last checked in $reltime seconds ago' />";
			} else if ( $reltime < 120 ) {
				echo "<img width='22px' src='../images/judgehost-nook.png' title='judgehost-warn: last checked in $reltime seconds ago' />";
			} else {
				echo "<img width='22px' src='../images/judgehost-err.png' title='judgehost-err: last checked in $reltime seconds ago' />";
			}
		}
		echo "</td>";
		if ( IS_ADMIN ) {
			echo "<td class='acenter'>" . delLink('judgehost','hostname',$row['hostname']) ."</td>";
		}
		echo "</tr>\n";
	}
	echo "</tbody>\n</table>\n\n";
}

if ( IS_ADMIN ) {
	echo addForm('judgehosts.php') .
		"<p>" .
		addSubmit('Start all judgehosts', 'cmd-activate') .
		addSubmit('Stop all judgehosts', 'cmd-deactivate') .
		"<br /><br />\n\n" .
		addLink('judgehosts', true) . "\n" .
		editLink('judgehosts', null, true) .
		"</p>\n" .
		addEndForm();

}

require(LIBWWWDIR . '/footer.php');
