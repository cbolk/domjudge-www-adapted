<?php
/**
 * Include required files.
 *
 * $Id: account.php 3146 2011-09-25 20:35:28Z eldering $
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

// The functions do_login and show_loginpage, if called, do not return.
if ( @$_POST['cmd']=='changepass' ) do_changepassword();
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
	include(LIBWWWDIR . '/header.php');
	echo "<h1>Information details for team " . $login . "</h1>";
	if(!empty($cmd))
		if($cmd == 'edit'){
			echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
			echo '<input type="hidden" name="cmd" value="edit" />';
		} else if ($cmd == 'changepass') //changepass
			show_changepassword();
		
	echo "<table class=''>\n";
	echo "<tr><td class='theader'>Login:</td>";
	echo "<td class=\"teamid\">" . htmlspecialchars($row['login']);
	if(!empty($cmd)){
		echo '<input type="hidden" id="login" name="login" value="' . htmlspecialchars($row['login']) . '" size="15" maxlength="25" accesskey="p" />';
	}
	echo "</td><td></td></tr>";
	echo "<tr><td class='theader'>Name:</td>";
	echo "<td class=\"name\">";
	if(empty($cmd)){
		echo htmlspecialchars($row['name']) ;
	}else{	
		echo '<input type="text" id="name" name="name" value="' . htmlspecialchars($row['name']) . '" size="15" maxlength="15" />';
	}
	echo "</td><td>";
	if(empty($cmd))
		echo "&nbsp;&nbsp;<a href='" . $_SERVER['PHP_SELF'] ."?cmd=edit'>edit your name</a></td><td align='left'>" ;	
	echo "</td></tr>";
	echo "<tr><td class=\"theader\">Affiliation:</td><td>";
	if (is_readable($affillogo)) {
		echo buildimage($affillogo);
	} else {
		text2image(urlencode($row['affilid']));
	}
	echo "</td></tr>";
	echo "<tr><td class=\"theader\">Host:</td><td>";
	@$row['hostname'] ? printhost($row['hostname'], TRUE):'-';
	echo "</td></tr>";
	echo "<tr><td class='theader'>Password:</td>";
	echo "<td class=\"password\">";
	if(empty($cmd)){
		echo "*********" ;
		echo "</td><td>&nbsp;&nbsp;<a href='" . $_SERVER['PHP_SELF'] ."?cmd=changepass'>change your password</a></td>" ;

	}else{	
		echo '<input type="password" id="passwd" name="passwd" value="" size="15" maxlength="15" accesskey="p" /></td><td>';
	}
	echo "</td></tr>";
	echo "<tr><td align='right'>";
	if(!empty($cmd)){	
		echo '<input type="submit" name="cancel" id="cancel" value="Cancel" /></td><td align="left">';
		echo '<input type="submit" value="Save" /></td><td></td>';
	}
	echo "</tr>";
	echo "</table>";	


$restrictions['teamid'] = $id;
putSubmissions($cdata, $restrictions);

require(LIBWWWDIR . '/footer.php');
