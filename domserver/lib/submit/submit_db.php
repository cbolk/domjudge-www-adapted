#!/usr/bin/env php
<?php
/**
 * Called by commandline submitdaemon; wrapper around
 * 'submit_solution' to add the submission to the database and store a
 * copy of the source in SUBMITDIR.
 *
 * Called: submit_db.php <team> <ip> <problem> <langext> [<tempfile> <filename>]...
 * Returns exitcode != 0 on failure.
 *
 * @configure_input@
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */
if ( isset($_SERVER['REMOTE_ADDR']) ) die ("Commandline use only");

require('/usr/local/data/sites/poseidon/htdocs/contests/etc/domserver-static.php');
require(ETCDIR . '/domserver-config.php');

define ('SCRIPT_ID', 'submit_db');
define ('LOGFILE', LOGDIR.'/submit.log');

require(LIBDIR.'/init.php');

//setup_database_connection('jury');
setup_database_connection();

// Get commandline vars and case-normalize them
$argv = $_SERVER['argv'];

$team    = strtolower(@$argv[1]);
$ip      = @$argv[2];
$prob    = strtolower(@$argv[3]);
$langext = strtolower(@$argv[4]);
// $file    = @$argv[5];

$files = array();
$filenames = array();
for($i=5; $i<count($argv); $i+=2) {
	$files[] = $argv[$i];
	if ( $i+1>=count($argv) ) error("Non-matching number of tempfiles and filenames.");
	$filenames[] = $argv[$i+1];
}

if ( count($filenames) > dbconfig_get('sourcefiles_limit',100) ) {
	error("Tried to submit more than the allowed number of source files.");
}

logmsg(LOG_DEBUG, "arguments: '$team' '$ip' '$prob' '$langext' and " .
       count($files) . " files specified");

$cdata = getCurContest(TRUE);
$cid = $cdata['cid'];

if ( AUTH_METHOD!='IPADDRESS' ) {
	error("Only IP-based authentication is supported.");
}

// Verify and register IP address here, as other authentication
// methods might conflict with this.
$teamdata = $DB->q('MAYBETUPLE SELECT * FROM team WHERE login = %s', $team);

if ( $teamdata['enabled'] != 1 ) {
	error("Team '$team' is disabled.");
}

if( ! compareipaddr($teamdata['authtoken'],$ip) ) {
	if ( $teamdata['authtoken'] == NULL && ! STRICTIPCHECK ) {
		// Before registering team, check that IP address is unique
		$DB->q('START TRANSACTION');
		$res = $DB->q('MAYBEVALUE SELECT login FROM team WHERE authtoken = %s', $ip);

		if ( !empty($res) ) {
			$DB->q('COMMIT');
			error("Team '$res' already registered at IP address '$ip'.");
		}

		$res = $DB->q('RETURNAFFECTED UPDATE team SET authtoken = %s
		               WHERE login = %s', $ip, $team);
		$DB->q('COMMIT');

		if ( !$res ) error("Failed to register team '$team' at address '$ip'.");

		logmsg(LOG_NOTICE, "Registered team '$team' at address '$ip'.");
	} else {
		error("Team '$team' not registered at this IP address.");
	}
}

$sid = submit_solution($team, $prob, $langext, $files, $filenames);

logmsg(LOG_NOTICE, "submitted $team/$prob/$langext, id s$sid/c$cid");

exit;
