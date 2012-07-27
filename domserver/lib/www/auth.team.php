<?php
/**
 * This file provides all functionality for authenticating teams. The
 * authentication method used is configured with the AUTH_METHOD
 * variable. When a team is succesfully authenticated, $login is set
 * to the team ID and $teamdata contains the corresponding row from
 * the database. $ip is set to the remote IP address used.
 *
 * $Id: auth.team.php 3550 2011-06-26 15:27:16Z eldering $
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 * Modified by CBolk
 * UPDATE July 26, 2012 - support for Unique Authentication via shib
 */

$ip = $_SERVER['REMOTE_ADDR'];

$login = NULL;
$teamdata = NULL;

// Returns whether the connected user is logged in, sets $login, $teamdata
function logged_in()
{
	global $DB, $ip, $login, $teamdata;

	if ( !empty($login) && !empty($teamdata) ) return TRUE;

	// Retrieve teamdata for given AUTH_METHOD, assume not logged in
	// when teamdata is empty:
	switch ( AUTH_METHOD ) {
	case 'FIXED':
		$login = FIXED_TEAM;
		$teamdata = $DB->q('MAYBETUPLE SELECT * FROM team WHERE login = %s', $login);
		break;

	case 'IPADDRESS':
		$teamdata = $DB->q('MAYBETUPLE SELECT * FROM team WHERE authtoken = %s', $ip);
		break;

	case 'PHP_SESSIONS':
		session_start();
		if ( isset($_SESSION['teamid']) ) {
			$teamdata = $DB->q('MAYBETUPLE SELECT * FROM team WHERE login = %s',
			                   $_SESSION['teamid']);
		}
		break;

	default:
		error("Unknown authentication method '" . AUTH_METHOD . "' requested.");
	}

	if ( !empty($teamdata) ) {
		$login = $teamdata['login'];
		// is this the first visit? record that in the team table
		if ( empty($row['teampage_first_visited']) ) {
			$hostname = gethostbyaddr($ip);
			$DB->q('UPDATE team SET teampage_first_visited = %s, hostname = %s
			        WHERE login = %s',
			       now(), $hostname, $login);
		}
	}

	return $login!==NULL;
}

// Returns whether the active authentication method has logout functionality.
function have_logout()
{
	switch ( AUTH_METHOD ) {
	case 'FIXED':        return FALSE;
	case 'IPADDRESS':    return FALSE;
	case 'PHP_SESSIONS': return TRUE;
	}
	return FALSE;
}

// Generate a page stating that login has failed with $msg and exit.
function show_failed_login($msg)
{
	$title = 'Login failed';
	$menu = false;
	require(LIBWWWDIR . '/header.php');
	echo "<h1>Not Authenticated</h1>\n\n<p>$msg</p>\n\n";
	require(LIBWWWDIR . '/footer.php');
	exit;
}

// This function presents some kind of login page, which should e.g.
// have a POST form to supply login credentials. This function does
// not return.
function show_loginpage()
{
	global $ip;

	switch ( AUTH_METHOD ) {
	case 'IPADDRESS':
	case 'PHP_SESSIONS':
		if ( NONINTERACTIVE ) error("Not authenticated");
		$title = 'Not Authenticated';
		$menu = false;

		include(LIBWWWDIR . '/header.php');
		?>
<h1>Not Authenticated</h1>

<p>Sorry, we are unable to identify you as a valid team
(IP <?php echo htmlspecialchars($ip); ?>).</p>

<p>
Please contact a staff member for assistance.
</p>

<?php
		putDOMjudgeVersion();
		include(LIBWWWDIR . '/footer.php');
		break;

	default:
		error("Unknown authentication method '" . AUTH_METHOD .
		      "' requested, or login not supported.");
	}

	exit;
}

// Try to login a team with e.g. authentication data POST-ed. Function
// does not return and should generate e.g. a redirect back to the
// referring page.
function do_login()
{
	global $DB, $ip, $login, $teamdata;

	switch ( AUTH_METHOD ) {
	// Generic authentication code for IPADDRESS and PHP_SESSIONS;
	// some specializations are handled by if-statements.
	case 'IPADDRESS':
	case 'PHP_SESSIONS':
		$user = trim($_POST['login']);
		$pass = trim($_POST['passwd']);

		$title = 'Authenticate user';
		$menu = false;

		if ( empty($user) || empty($pass) ) {
			show_failed_login("Please supply a username and password.");
		}

		$teamdata = $DB->q('MAYBETUPLE SELECT * FROM team
		                    WHERE login = %s AND authtoken = %s',
		                   $user, md5($user."#".$pass));

		if ( !$teamdata ) {
			sleep(3);
			show_failed_login("Invalid username or password supplied. " .
			                  "Please try again or contact a staff member.");
		}

		$login = $teamdata['login'];

		if ( AUTH_METHOD=='IPADDRESS' ) {
			$cnt = $DB->q('RETURNAFFECTED UPDATE team SET authtoken = %s
			               WHERE login = %s', $ip, $login);
			if ( $cnt != 1 ) error("cannot set IP for team '$login'");
		}
		if ( AUTH_METHOD=='PHP_SESSIONS' ) {
			session_start();
			$_SESSION['teamid'] = $login;
		}
		break;

	default:
		error("Unknown authentication method '" . AUTH_METHOD .
		      "' requested, or login not supported.");
	}

	require(LIBWWWDIR . '/header.php');
	echo "<h1>Authenticated</h1>\n\n<p>Successfully authenticated as team " .
	    htmlspecialchars($login) . " on " . htmlspecialchars($ip) . ".</p>\n" .
	    "<p><a href=\"$_SERVER[PHP_SELF]\">Continue to your team page</a>, " .
	    "and good luck!</p>\n\n";
	require(LIBWWWDIR . '/footer.php');

	exit;
}

// Logout a team. Function does not return and should generate a page
// showing logout and optionally refer to a login page.
function do_logout()
{
	global $DB, $ip, $login, $teamdata;

	switch ( AUTH_METHOD ) {
	case 'PHP_SESSIONS':
		// Unset all of the session variables.
		$_SESSION = array();

		// Also delete the session cookie.
		if ( ini_get("session.use_cookies") ) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
			          $params["path"], $params["domain"],
			          $params["secure"], $params["httponly"]);
		}

		// Finally, destroy the session.
		if ( !session_destroy() ) error("PHP session not successfully destroyed.");
		break;

	default:
		error("Unknown authentication method '" . AUTH_METHOD .
		      "' requested, or logout not supported.");
	}

	$title = 'Logout';
	$menu = FALSE;

	require(LIBWWWDIR . '/header.php');
	echo "<h1>Logged out</h1>\n\n<p>Successfully logged out as team " .
	    htmlspecialchars($login) . ".</p>\n" .
	    "<p><a href=\"./\">Click here to login again.</a></p>\n\n";
	require(LIBWWWDIR . '/footer.php');
	exit;
}



//Added function to change password
function show_changepassword()
{
	global $login;
?>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<table>
<input type="hidden" name="cmd" value="changepass" />

<input type="hidden" name="login" value="<?php echo $login; ?>" />
<tr><td><label for="passwd">Old Password:</label></td><td><input type="password" id="oldpasswd" name="oldpasswd" value="" size="15" maxlength="255" accesskey="p" /></td></tr>
<tr><td><label for="passwd">New Password:</label></td><td><input type="password" id="newpasswd" name="newpasswd" value="" size="15" maxlength="255" accesskey="p" /></td></tr>
<tr><td><label for="passwd">Retype New Password:</label></td><td><input type="password" id="newpasswd2" name="newpasswd2" value="" size="15" maxlength="255" accesskey="p" /></td></tr>
<tr><td colspan="2" align="center">
<input type="submit" value="Save" />
<input type="submit" name="cancel" id="cancel" value="Cancel" />
</td></tr>
</table>
</form>

<?php
	putDOMjudgeVersion();
	require(LIBWWWDIR . '/footer.php');
	exit;

}


// Try to change password a team with e.g. authentication data POST-ed. Function
// does not return and should generate e.g. a redirect back to the
// referring page.
function do_changepassword()
{
	global $DB, $login;

	$user = trim($_POST['login']);
	$oldpass = trim($_POST['oldpasswd']);
	$newpass = trim($_POST['newpasswd']);
	$newpass2 = trim($_POST['newpasswd2']);

	$title = 'Updating user password';
	$menu = false;

	if (empty($user) || empty($oldpass) || empty($newpass) || empty($newpass2)) {
		show_failed_passwordupdate("Please supply username, old password and new password", $user);
	} else if ( $newpass != $newpass2) {
		show_failed_passwordupdate("The new password and the re-typed one do not match.", $user);
	}
	$sqlq = "MAYBETUPLE SELECT * FROM team WHERE login = '" . $user . "' AND authtoken = '" . md5($user."#".$oldpass) ."'";

	$teamdata = $DB->q($sqlq);

	if ( !$teamdata ) {
		sleep(3);
		show_failed_passwordupdate("Invalid old password (" . $sqlq . "). " .
						  "Please try again or contact a staff member.", $user);
	} else {
		$login = $teamdata['login'];
		$sqlq = "RETURNAFFECTED UPDATE team set authtoken = '" . md5($user."#".$newpass) . "' WHERE login = ". $user;
		$cnt = $DB->q($sqlq);
		if($cnt != 1){
			sleep(3);
			show_failed_passwordupdate("Impossible to change your password. " .
						  "Please try again or contact a staff member.", $user);				
		} 
	}

	require(LIBWWWDIR . '/header.php');
	echo "<h1>Password changed</h1>\n<p>&nbsp;</p>"  .
	    "<p><a href='./index.php'>Continue to your team page</a>, " .
	    "and good luck!</p>\n\n";
	require(LIBWWWDIR . '/footer.php');

	exit;
}

// Generate a page stating that no password change has been performed.
function show_failed_passwordupdate($msg, $user)
{
	global $login;

	$title = 'Change password';
	$menu = true;
	$login = $user;
	include(LIBWWWDIR . '/header.php');
	echo "<h1>Password change</h1><p>&nbsp;</p>";

	echo "<h2>Password not modified</h2>\n\n<p>$msg</p>\n\n";
	show_changepassword();
}

function do_changename()
{
	global $DB, $login;

	$user = trim($_POST['login']);
	$name = trim($_POST['name']);

	$title = 'Updating user name';
	$menu = false;

	if (empty($user) || empty($name)) {
		show_failed_nameupdate("Please supply login and new name", $user);
	}
	
	$sqlq = "RETURNAFFECTED UPDATE team set name = '" . $name . "' WHERE login = '". $user . "'";
	$cnt = $DB->q($sqlq);
	if($cnt != 1){
		show_failed_nameupdate("Impossible to change your name. " .
				  "Please try again or contact a staff member.", $user);				
	} 


	require(LIBWWWDIR . '/header.php');
	echo "<h1>Name changed</h1>\n<p>&nbsp;</p>"  .
	    "<p><a href='" . $_SERVER['PHP_SELF'] . "'>Continue to your team page</a>.</p>\n\n";
	require(LIBWWWDIR . '/footer.php');

	exit;
}

// Generate a page stating that no password change has been performed.
function show_failed_nameupdate($msg, $user)
{
	global $login;

	$title = 'Change name';
	$menu = true;
	$login = $user;
	include(LIBWWWDIR . '/header.php');
	echo "<h1>Name change</h1><p>&nbsp;</p>";

	echo "<h2>Name not modified</h2>\n\n<p>$msg</p>\n\n";
	exit;
}

//Get the user id from the shibbolet authentication token
// Try to login a team with e.g. authentication data POST-ed. Function
// does not return and should generate e.g. a redirect back to the
// referring page.
function do_auth()
{
	global $DB, $ip, $login, $teamdata;

	switch ( AUTH_METHOD ) {
	// Generic authentication code for IPADDRESS and PHP_SESSIONS;
	// some specializations are handled by if-statements.
	case 'PHP_SESSIONS':
		$user = extract_user($_SERVER['REMOTE_USER']);
		
		$title = 'Authenticate user';
		$menu = false;

		if ( empty($user) ) {
			show_failed_login("No user has been specified");
		}

		$teamdata = $DB->q('MAYBETUPLE SELECT * FROM team
		                    WHERE login = %s', $user);

		if ( !$teamdata ) {
			sleep(3);
			show_failed_login("This user has no access to this application " .
			                  "Please contact a staff member.");
		}

		$login = $teamdata['login'];

		session_start();
		$_SESSION['teamid'] = $login;

		$sqlq = "RETURNAFFECTED UPDATE team set hostname = '" . $ip . "' WHERE login = '". $user . "'";
		$cnt = $DB->q($sqlq);
		if($cnt != 1)
			show_failed_nameupdate("Impossible to update the hostname you are connecting from");				
		
		break;

	default:
		error("Unknown authentication method '" . AUTH_METHOD .
		      "' requested, or login not supported.");
	}

	require(LIBWWWDIR . '/header.php');
	echo "<h1>Authenticated</h1>\n\n<p>Successfully authenticated as team " .
	    htmlspecialchars($teamdata['name']) . " on " . htmlspecialchars($ip) . ".</p>\n" .
	    "<p><a href=\"$_SERVER[PHP_SELF]\">Continue to your team page</a>, " .
	    "and good luck!</p>\n\n";
	require(LIBWWWDIR . '/footer.php');

	exit;
}

// This function presents some kind of message to redirect to the
// Authentication page
function show_authpage()
{
	global $ip;

	switch ( AUTH_METHOD ) {
	case 'PHP_SESSIONS':
		if ( NONINTERACTIVE ) error("Not authenticated");
		$title = 'Not Authenticated';
		$menu = false;

		include(LIBWWWDIR . '/header.php');
		?>
<h1>Not Authenticated</h1>

<p>Sorry, we are unable to identify you as a valid user
(IP <?php echo htmlspecialchars($ip); ?>).</p>

<p>
Please contact a staff member for assistance.
</p>

<?php
		putDOMjudgeVersion();
		include(LIBWWWDIR . '/footer.php');
		break;

	default:
		error("Unknown authentication method '" . AUTH_METHOD .
		      "' requested, or login not supported.");
	}

	exit;
}

function extract_user($value){
	
	$isep = strpos($value, '@');
	$codp = substr($value, 0, $isep);
	return $codp;
}

