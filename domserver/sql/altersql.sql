CREATE USER 'domjudge_admin'@'localhost' IDENTIFIED BY 'oqs4fpmkifhr';
GRANT ALL PRIVILEGES ON *.* TO 'domjudge_admin'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;

ALTER TABLE  `problem` ADD  `longdescription` LONGTEXT NOT NULL;

ALTER TABLE  `testcase` ADD  `public` BOOL( 1 ) NOT NULL DEFAULT TRUE;

GRANT SELECT (probid, name, color, longdescription, cid, allow_submit) on domjudge.problem to 'domjudge_team'@'localhost';
GRANT SELECT (testcaseid, input, output, rank, public) on domjudge.testcase to 'domjudge_team'@'localhost';

ALTER TABLE `testcase` ADD  `ifile` VARCHAR(200), `ofile` VARCHAR(200);

GRANT SELECT (testcaseid, input, output, ifile, ofile, rank, public) on domjudge.testcase to 'domjudge_public'@'localhost';
GRANT SELECT (testcaseid, input, output, ifile, ofile, rank, public) on domjudge.testcase to 'domjudge_team'@'localhost';
