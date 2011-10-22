<?php
echo "<div id=\"header\">";
echo "<div id=\"menutop\">\n";
echo "<ul class=\"nav\">";
echo "<li><a target=\"_top\" href=\"problem_detail.php\" accesskey=\"p\">problem</a></li>\n";
echo "<li><a target=\"_top\" href=\"index.php\" accesskey=\"s\">submissions</a></li>\n";

if ( $nunread_clars > 0 ) {
	echo '<li><a target="_top" class="new" href="clarifications.php" ' .
		'accesskey="c" id="menu_clarifications">clarifications (' .
		$nunread_clars . " new)</a></li>\n";
} else {
	echo '<li><a target="_top" href="clarifications.php" ' .
		"accesskey=\"c\" id=\"menu_clarifications\">clarifications</a></li>\n";
}

echo "<li><a target=\"_top\" href=\"scoreboard.php\" accesskey=\"b\">scoreboard</a></li>\n";

if ( ENABLE_WEBSUBMIT_SERVER ) {
	echo "<li><a target=\"_top\" href=\"websubmit.php\" accesskey=\"u\">submit</a></li>\n";
}

echo "<li><a target=\"_top\" href=\"account.php\" accesskey=\"a\">account</a></li>\n";

if ( have_logout() ) {
	echo "<li><a target=\"_top\" href=\"logout.php\" accesskey=\"l\">logout</a></li>\n";
}
echo "</ul>";
echo "\n</div>\n\n";

putClock();

echo "</div>";
echo "<div id='main'>";
