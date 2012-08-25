<?php
/**
 * Include required files.
 *
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require_once('../configure.php');

define('IS_JURY', false);

if ( ! defined('NONINTERACTIVE') ) define('NONINTERACTIVE', false);

if( DEBUG & DEBUG_TIMINGS ) {
	require_once(LIBDIR . '/lib.timer.php');
}

require_once(LIBDIR . '/lib.error.php');
require_once(LIBDIR . '/lib.misc.php');
require_once(LIBDIR . '/lib.dbconfig.php');
require_once(LIBDIR . '/use_db.php');

set_exception_handler('exception_handler');
setup_database_connection('team');

require_once(LIBWWWDIR . '/common.php');
require_once(LIBWWWDIR . '/print.php');
require_once(LIBWWWDIR . '/clarification.php');
require_once(LIBWWWDIR . '/scoreboard.php');
require_once(LIBWWWDIR . '/auth.team.php');
require_once(LIBWWWDIR . '/team.cb.php');

// The functions do_login and show_loginpage, if called, do not return.
if ( @$_POST['cmd']=='edit' ) do_changename();

if ( isset($_GET['cmd'] ) ) 
	$cmd = $_GET['cmd'];
else if ( isset($_POST['cmd'] ) ) 
	$cmd = $_POST['cmd'];

if ( logged_in() ) 
	$title = 'Team details';
	$menu = true;
	$login = $_SESSION['teamid'];
	$row = $DB->q('TUPLE SELECT * FROM team WHERE login = %s', $login);
	$affillogo   = "../images/affiliations/" . urlencode($row['affilid']) . ".png";
	$countryflag = "../images/countries/"    . urlencode($row['country']) . ".png";
	$teamimage   = "../images/teams/"        . urlencode($row['login'])   . ".jpg";
	$restrictions['teamid'] = $login;
	include(LIBWWWDIR . '/header.php');


	echo "<h1><div class='fleft'>" . htmlspecialchars($row['name']) ;
	echo "</div>&nbsp;";
	
	if(empty($cmd)){
		echo "<div class='fright'><a href='" . $_SERVER['PHP_SELF'] ."?cmd=edit' class='minibutton btn-editprofile'>Edit Your Name</a></div>";
		echo "</h1>\n";
			echo "<table class='userbox'>\n";
			echo "<tr><td rowspan='3' valign='top'>";
			echo "<span class='tooltipped downwards' title='Change your avatar at gravatar.com'><a href='http://gravatar.com/emails/'><img height='64' src='https://secure.gravatar.com/avatar/c68beff5124239fff1d81b2167d9a7b6?s=140&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-140.png' width='64' /></a></span>";
			echo "</td>";
			echo "<td class='theader'>Login:</td>";
			echo "<td class=\"teamid\">" . htmlspecialchars($row['login']);
			echo "</td></tr>";
			echo "<tr><td class=\"theader\">Affiliation:</td><td>";
			if (is_readable($affillogo)) {
				echo buildimage($affillogo);
			} else {
				text2image(urlencode($row['affilid']));
			}
			echo "</td><td></td></tr>";
			echo "<tr><td class=\"theader\">Host:</td><td>" . $row['hostname'] . "</td>";
			echo "</td><td></td></tr>";
			echo "</table>";
			echo "<div class='clean'>&nbsp;</div>";	
			putTeamStats($row['login']);
			putSubmissions($cdata, $restrictions);
		} 	else {
				echo "</h1>";
				if($cmd == 'edit'){
					echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
					echo '<input type="hidden" name="cmd" value="edit" />';
					echo "<div class='boxed-group'><h3>Change Username</h3>";
					echo "<dl><dt>&nbsp;</dt>";
					echo '<dd><input type="text" id="name" name="name" value="' . htmlspecialchars($row['name']) . '" size="15" maxlength="15" /></dd>';
					echo '<input type="hidden" id="login" name="login" value="' . htmlspecialchars($row['login']) . '" size="15" maxlength="25" accesskey="p" />';
					echo "<dt>&nbsp;</dt>";
					echo '<input type="submit" name="cancel" id="cancel" value="Cancel" class="button classy" />&nbsp;&nbsp;';
					echo '<input type="submit" value="Save"  class="button classy"/>';
				}
	}

require(LIBWWWDIR . '/footer.php');
