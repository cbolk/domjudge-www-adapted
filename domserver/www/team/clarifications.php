<?php
/**
 * Display the clarification responses
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 *
 * CB: not existing in the original new version
 */

require('init.php');
$refresh = '30;url=clarifications.php';
$ajaxtitle = 'Clarifications';
$title = $ajaxtitle.($nunread_clars ? ' ('.$nunread_clars.' new)' : '');
require(LIBWWWDIR . '/header.php');


echo "<h1>clarifications for ".htmlspecialchars($teamdata['name']). "<div class='fright'><a href=\"clarification.php\" class=\"minibutton btn-editprofile\">Request New Clarification</a></div></h1>\n\n";

// Put overview of team submissions (like scoreboard)
echo "<div id=\"teamscoresummary\">\n";
putTeamRow($cdata, $login);
echo "</div>\n";

//echo "<li><a href=\"#clarifications\" class=\"minibutton btn-editprofile\">Answers</a></li>\n";
//echo "<li><a href=\"#requests\" class=\"minibutton btn-editprofile\">Requests</a></li></ul>\n\n";

$requests = $DB->q('SELECT * FROM clarification
                    WHERE cid = %i AND sender = %s
                    ORDER BY submittime DESC, clarid DESC', $cid, $login);

$clarifications = $DB->q('SELECT c.*, u.type AS unread FROM clarification c
                          LEFT JOIN team_unread u ON
                          (c.clarid=u.mesgid AND u.type="clarification" AND u.teamid = %s)
                          WHERE c.cid = %i AND c.sender IS NULL
                          AND ( c.recipient IS NULL OR c.recipient = %s )
                          ORDER BY c.submittime DESC, c.clarid DESC',
                          $login, $cid, $login);

echo '<h3><a name="clarifications"></a>' .
	"received clarifications:</h3>\n";
if ( $clarifications->count() == 0 ) {
	echo "<p class=\"nodata\">No clarifications.</p>\n\n";
} else {
	putClarificationList($clarifications,$login);
}

echo '<h3><a name="requests"></a>' .
	"requested clarifications:</h3>\n";
if ( $requests->count() == 0 ) {
	echo "<p class=\"nodata\">No clarification requests.</p>\n\n";
} else {
	putClarificationList($requests,$login);
}

require(LIBWWWDIR . '/footer.php');
