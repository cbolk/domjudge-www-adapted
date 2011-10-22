<?php
/**
 * View current, past and future contests
 *
 * $Id: contests.php 3209 2010-06-12 00:13:43Z eldering $
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 * Modified CBolk
 */

require('init.php');
$title = 'Contests';
require(LIBWWWDIR . '/header.php');

echo "<h1>List of contests</h1>\n\n";

if ( isset($_POST['unfreeze']) ) {
	$docid = array_pop(array_keys($_POST['unfreeze']));
	if ( $docid != $cid ) {
		error("Can only unfreeze for current contest");
	}
	$DB->q('UPDATE contest SET unfreezetime = %s WHERE cid = %i', now(), $docid);
}

// Get data. Starttime seems most logical sort criterion.
$res = $DB->q('TABLE SELECT * FROM contest ORDER BY starttime DESC');

if( count($res) == 0 ) {
	echo "<p class=\"nodata\">No contests defined</p>\n\n";
} else {
	echo "<form action=\"contests.php\" method=\"post\">\n";
	echo "<table id='tablespec' class=\"list sortable\">\n<thead>\n" .
	     "<tr><th scope=\"col\">CID</th><th scope=\"col\">active</th>" .
	     "<th scope=\"col\">starts</th><th scope=\"col\">ends</th>" .
	     "<th scope=\"col\">freeze<br />scores</th>" .
	     "<th scope=\"col\">unfreeze<br />scores</th>" .
	     "<th scope=\"col\">name</th>" .
	     "<th>edit</th><th>del</th><th></th></tr>\n</thead>\n<tbody>\n";

	$iseven = false;
	foreach($res as $row) {

		$link = '<a href="contest.php?id=' . urlencode($row['cid']) . '">';

		echo '<tr class="' .
			( $iseven ? 'roweven': 'rowodd' ) .
			(!$row['enabled']    ? ' disabled' :'') .
			($row['cid'] == $cid ? ' highlight':'') . '">' .
			"<td align=\"right\">" . $link .
			"c" . (int)$row['cid'] . "</a></td>\n" .
			"<td title=\"".htmlspecialchars(@$row['activatetime']) . "\">" .
				$link . strftime("%d-%m-%y %R", strtotime($row['activatetime']))  . "</a></td>\n" .
			"<td title=\"" . htmlspecialchars($row['starttime']) . "\">" .
				$link . strftime("%d-%m-%y %R", strtotime($row['starttime']))."</a></td>\n".
			"<td title=\"".htmlspecialchars($row['endtime']) . "\">" .
				$link . strftime("%d-%m-%y %R", strtotime($row['endtime'])) ."</a></td>\n".
			"<td title=\"".htmlspecialchars(@$row['freezetime']) . "\">" .
				$link . ( isset($row['freezetime']) ?
			  strftime("%d-%m-%y %R", strtotime($row['freezetime'])) : '-' ) . "</a></td>\n" .
			"<td title=\"".htmlspecialchars(@$row['unfreezetime']) . "\">" .
				$link . ( isset($row['unfreezetime']) ?
			  strftime("%d-%m-%y %R", strtotime($row['unfreezetime'])) : '-' ) . "</a></td>\n" .
			"<td>" . $link . htmlspecialchars($row['contestname']) . "</a></td>\n";
		$iseven = ! $iseven;

		if ( IS_ADMIN ) {
			echo "<td class=\"editdel acenter\">" .
				editLink('contest', $row['cid']) . "</td><td class='editdel acenter'> " .
				delLink('contest','cid',$row['cid']) . "</td>\n";
		}

		// display an unfreeze scoreboard button, only for the current
		// contest (unfreezing undisplayed scores makes no sense) and
		// only if the contest has already finished, and the scores have
		// not already been unfrozen.
		echo "<td>";
		if ( $row['cid'] == $cid && isset($row['freezetime']) ) {
			echo "<input type=\"submit\" name=\"unfreeze[" . $row['cid'] .
				"]\" value=\"unfreeze now\"" ;
			$now = now();
			if ( difftime($row['endtime'],$now) > 0 ||
				(isset($row['unfreezetime']) && difftime($row['unfreezetime'], $now) <= 0)
				) {
				echo " disabled=\"disabled\"";
			}
			echo " />";
		}
		echo "</td>\n";
		echo "</tr>\n";
	}
	echo "</tbody>\n</table>\n</form>\n\n";
}

if ( IS_ADMIN ) {
	echo "<p>" . addLink('contest') . "</p>\n\n";
}

require(LIBWWWDIR . '/footer.php');
