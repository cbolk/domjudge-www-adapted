<?php
/**
 * View the teams
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require('init.php');
$title = 'Teams';

$teams = $DB->q('SELECT t.*,c.name AS catname,a.name AS affname
                 FROM team t
                 LEFT JOIN team_category c USING (categoryid)
                 LEFT JOIN team_affiliation a ON (t.affilid = a.affilid)
                 ORDER BY c.sortorder, t.name COLLATE utf8_general_ci');

$nsubmits = $DB->q('KEYTABLE SELECT teamid AS ARRAYKEY, COUNT(teamid) AS cnt
                    FROM submission s
                    WHERE cid = %i GROUP BY teamid', $cid);

$ncorrect = $DB->q('KEYTABLE SELECT teamid AS ARRAYKEY, COUNT(teamid) AS cnt
                    FROM submission s
                    LEFT JOIN judging j USING (submitid)
                    WHERE j.valid = 1 AND j.result = "correct" AND s.cid = %i
                    GROUP BY teamid', $cid);

require(LIBWWWDIR . '/header.php');

echo "<h1><div class='fleft'>List of teams (<a href='teamsdetailed.php'>details</a>)</div>&nbsp;\n";
if ( IS_ADMIN ) {
	echo "<div class='fright'>" . addLink('team') ."</div>\n\n";
}
echo "</h1>";

if( $teams->count() == 0 ) {
	echo "<p class=\"nodata\">No teams defined</p>\n\n";
} else {
	echo "<table class=\"list sortable\" border='1'>\n" .
		"<tr><th class='theader' scope=\"col\">login</th><th class='theader' scope=\"col\">name</th>" .
		"<th class='theader' scope=\"col\">category</th><th class='theader' scope=\"col\">affiliation</th>" .
		"<th class='theader' scope=\"col\">host</th>" .
		"<th class=\"sorttable_nosort theader\"></th><th class='theader' align=\"left\" " .
		"scope=\"col\">status</th>" .
	  "<th class='theader' scope=\"col\">&nbsp;edit&nbsp;</th>" .
	  "<th class='theader' scope=\"col\">delete</th>" .
		"</tr>\n";

	$iseven = 0;
	while( $row = $teams->next() ) {
		$trow = ( $iseven ? 'roweven': 'rowodd' );;
		$iseven = !$iseven;
		$status = $numsub = $numcor = 0;
		if ( isset($row['teampage_first_visited']) ) $status = 1;
		if ( isset($nsubmits[$row['login']]) &&
			 $nsubmits[$row['login']]['cnt']>0 ) {
			$status = 2;
			$numsub = (int)$nsubmits[$row['login']]['cnt'];
		}
		if ( isset($ncorrect[$row['login']]) &&
			 $ncorrect[$row['login']]['cnt']>0 ) {
			$status = 3;
			$numcor = (int)$ncorrect[$row['login']]['cnt'];
		}
		$link = '<a href="team.php?id='.urlencode($row['login']) . '">';
		$affillogo = "../images/affiliations/" . urlencode($row['affilid']) . ".png";
		echo "<tr class=\"category" . (int)$row['categoryid'] . " " . $trow . "\">".
			"<td class=\"teamid\">" . $link .
				htmlspecialchars($row['login'])."</a></td>".
			"<td>" . $link .
				htmlspecialchars($row['name'])."</a></td>".
			"<td>" . $link .
				htmlspecialchars($row['catname'])."</a></td>";
			echo '<td title="' .htmlspecialchars($row['affname']). '">' . $link;
			if ( is_readable($affillogo) ) {
				echo '<img src="' . $affillogo . '" alt="' .
					htmlspecialchars($row['affilid']) . '" /> ';
			} else {
				echo htmlspecialchars($row['affname']);
			}
			echo '</a></td>';
			echo "<td title=\"";

		if ( @$row['hostname'] ) {
			echo htmlspecialchars($row['hostname']) . "\">" . $link .
			    printhost($row['hostname']);
		} else {
			echo "\">" . $link . "-";
		}
		echo "</a></td>";
		echo "<td class=\"";
		switch ( $status ) {
		case 0: echo 'team-nocon acenter" title="no connections made"><img class="smallpic" src="../images/team-neverlogged.png" />';
			break;
		case 1: echo 'team-nosub acenter" title="teampage viewed, no submissions"><img class="smallpic" src="../images/team-nosub.png" />';
			break;
		case 2: echo 'team-nocor acenter" title="submitted, none correct"><img class="smallpic" src="../images/team-nook.png" />';
			break;
		case 3: echo 'team-ok acenter" title="correct submission(s)"><img class="smallpic" src="../images/team-ok.png" />';
			break;
		}
		// TODO? want to link this symbol aswell? need to do some css magic to retain color
		echo "</td>";
		echo "<td class='acenter' title=\"$numcor correct / $numsub submitted\">$numcor / $numsub</td>";
		if ( IS_ADMIN ) {
			echo "<td class=\"editdel tacenter\">" .
				editLink('team', $row['login']) . "</td><td class='editdel tacenter'>" .
				delLink('team','login',$row['login']) . "</td>";
		}
		echo "</tr>\n";
	}
	echo "</table>\n\n";
}

if ( IS_ADMIN ) {
	echo "<p>" .addLink('team') . "</p>\n";
}

require(LIBWWWDIR . '/footer.php');
