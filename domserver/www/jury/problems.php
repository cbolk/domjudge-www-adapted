<?php
/**
 * View the problems
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require('init.php');
$title = 'Problems';

require(LIBWWWDIR . '/header.php');

echo "<h1><div class='fleft'>List of problems</div>&nbsp;\n";
if ( IS_ADMIN ) {
	echo "<div class='fright'>" . addLink('problem');
	if ( class_exists("ZipArchive") ) {
		echo "\n" . addForm('problem.php', 'post', null, 'multipart/form-data') .
	 		addHidden('id', @$data['probid']) .
	 		'Problem archive:' .
	 		addFileField('problem_archive') .
	 		addSubmit('Upload', 'upload') .
	 		addEndForm() . "\n";
	}
       	echo "</div>\n\n";
}
echo "</h1>";

$res = $DB->q('SELECT p.*, c.*, COUNT(testcaseid) AS testcases
               FROM problem p
               NATURAL JOIN contest c
               LEFT JOIN testcase USING (probid)
               GROUP BY probid ORDER BY (p.cid = %i) DESC, p.cid, probid', $cid);

if( $res->count() == 0 ) {
	echo "<p class=\"nodata\">No problems defined</p>\n\n";
} else {
	echo "<table id='tablespec' class=\"list sortable\">\n<thead>\n" .
		"<tr><th scope=\"col\">ID</th><th scope=\"col\">name</th>" .
		"<th scope=\"col\">contest</th><th scope=\"col\">allow<br />submit</th>" .
		"<th scope=\"col\">allow<br />judge</th>" .
		"<th scope=\"col\">time<br />limit</th>" .
		"<th class=\"sorttable_nosort\" scope=\"col\">col</th>" .
		"<th scope=\"col\">subs</th>" .
	  "<th scope=\"col\">test<br />cases</th>" .
	  "<th scope=\"col\">edit</th>" .
	  "<th scope=\"col\">delete</th>" .
		"</tr></thead>\n<tbody>\n";

	$lastcid = -1;

	while($row = $res->next()) {
		$classes = array();
		if ( $row['cid'] != $cid ) $classes[] = 'disabled';
		if ( $row['cid'] != $lastcid ) {
			if ( $lastcid != -1 ) $classes[] = 'contestswitch';
			$lastcid = $row['cid'];
		}
		$link = '<a href="problem.php?id=' . urlencode($row['probid']) . '">';

		echo "<tr class=\"" . implode(' ',$classes) .
		    "\"><td class=\"probid\">" . $link .
				htmlspecialchars($row['probid'])."</a>".
			"</td><td>" . $link . htmlspecialchars($row['name'])."</a>".
			"</td><td title=\"".htmlspecialchars($row['contestname'])."\">".
			$link .htmlspecialchars($row['contestname']) . ' (c' . htmlspecialchars($row['cid']) . ")</a>" .
			"</td><td align=\"center\">" . $link .
			printyn($row['allow_submit']) . "</a>" .
			"</td><td align=\"center\">" . $link .
			printyn($row['allow_judge']) . "</a>" .
			"</td><td align='right'>" . $link . (int)$row['timelimit'] . "</a>" .
			"</td>".
			( !empty($row['color'])
			? '<td align="center" title="' . htmlspecialchars($row['color']) .
		      '">' . $link . '<img class="imgmiddle" style="vertical-align: middle; background-color: ' .
			htmlspecialchars($row['color']) .
		      ';" alt="problem colour ' . htmlspecialchars($row['color']) .
		      '" src="../images/circle.png" /></a>'
			: '<td align="center" >' . $link . '&nbsp;</a>' );
			if ( IS_ADMIN ) {
				echo "</td><td align='center' ><a href='show_sources_problem.php?id=" . urlencode($row['probid']) . "'>list</a>";
				echo "</td><td class='aright'><a href=\"testcase.php?probid=" . $row['probid'] .
				    "\">" . $row['testcases'] . "</a></td class='acenter'>" .
				    "<td class=\"editdel  acenter\">" .
					editLink('problem', $row['probid']) . "</td><td class='editdel acenter'>" .
					delLink('problem','probid',$row['probid']);
			}
			echo "</td></tr>\n";
	}
	echo "</tbody>\n</table>\n\n";
}

require(LIBWWWDIR . '/footer.php');
