<?php
/**
 * View the languages
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 *
 * Modified by CBolk
 */
require('init.php');
$title = 'Languages';

require(LIBWWWDIR . '/header.php');

echo "<h1>List of languages</h1>\n\n";

$res = $DB->q('SELECT * FROM language ORDER BY name');

if( $res->count() == 0 ) {
	echo "<p class=\"nodata\">No languages defined</p>\n\n";
} else {
	echo "<table class=\"list sortable\">\n<thead>\n" .
		"<tr><th scope=\"col\">ID</th><th scope=\"col\">name</th>" .
		"<th scope=\"col\">extension</th>" .
		"<th scope=\"col\">&nbsp;icon&nbsp;</th>" .
		"<th scope=\"col\">allow<br />submit&nbsp;</th>" .
		"<th scope=\"col\">allow<br />judge</th>&nbsp;<th scope=\"col\">&nbsp;timefactor&nbsp;</th>" .
		"<th>&nbsp;edit&nbsp;</th>" .
		"<th>delete</th>" .
		"</tr>\n<tbody>\n";

	while($row = $res->next()) {
		$link = '<a href="language.php?id=' . urlencode($row['langid']) . '">';
		echo "<tr".
			( $row['allow_submit'] ? '': ' class="disabled"').
			"><td>" . $link . htmlspecialchars($row['langid'])."</a>".
			"</td><td>" . $link . htmlspecialchars($row['name'])."</a>".
			"</td>" .
			"<td class=\"filename\">" . $link . "." . 
				htmlspecialchars($row['extension']) . "</a>" .
			"</td>"
			.
			"<td>"; 
		$languageicon = strtolower('../images/languages/' . $row['name'] . '_file.png');
		if ( is_readable($languageicon) ) {
			echo '<img class="imgmiddle" src="' . $languageicon . '" />'; 
		} 
		echo "</td>" .
		"<td align=\"center\">" . $link .
				printyn($row['allow_submit']) . "</a>" .
			"</td><td align=\"center\">" . $link .
				printyn($row['allow_judge']) . "</a>" .
			"</td><td>" . $link . htmlspecialchars($row['time_factor']) . "</a>";
			if ( IS_ADMIN ) {
				echo "</td><td class=\"editdel acenter\">" .
					editLink('language', $row['langid']) . "</td><td class=\"editdel acenter\">" .
					delLink('language','langid',$row['langid']);
			}
		echo "</td></tr>\n";
	}
	echo "</tbody>\n</table>\n\n";
}

if ( IS_ADMIN ) {
	echo "<p>" . addLink('language') . "</p>\n\n";
}


require(LIBWWWDIR . '/footer.php');
