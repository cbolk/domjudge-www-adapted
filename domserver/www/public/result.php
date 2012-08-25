<?php
/**
 * View a language
 *
 * $Id: language.php 3209 2010-06-12 00:13:43Z eldering $
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 * Modified by CBolk
 */

$pagename = basename($_SERVER['PHP_SELF']);

require('init.php');

$id = @$_REQUEST['id'];
$title = 'Result Explanation';

if ( ! preg_match('/^\w*$/', $id) ) error("Invalid language id");

require(LIBWWWDIR . '/header.php');
?>

<h1>Output Explanation</h1>
<center>
<table width="800">
<tr><th scope="column" class="theader acenter"colspan="2">Result</th><th scope="column" class="theader acenter">Explanation</td></tr>
<tr class="roweven"><td style="vertical-align: top;"><img class="vmiddle" src="../images/correct.png" alt="correct"/></td><td style="vertical-align: top;"><span class="sol sol_correct">correct</span></td>
	<td>Il programma &egrave; stato compilato ed eseguito con successo e ha fornito la risposta corretta per tutti i casi di test.</td></tr>
<tr><td style="vertical-align: top;"><img class="vmiddle" src="../images/timelimit.png" alt="time limit exceeded"/></td><td style="vertical-align: top;"><span class="sol sol_incorrect">time limit</span></td>
	<td>Il programma &egrave; stato compilato con successo ma una volta eseguito non ha completato l'elaborazione entro il tempo limite fissato.<br/>
	Prova a verificare che non ci siano cicli infiniti e prova ad ottimizzare l'algoritmo.</td></tr>
<tr class="roweven"><td style="vertical-align: top;"><img class="vmiddle" src="../images/presentation-error.png" alt="presentation error"/></td><td style="vertical-align: top;"><span class="sol sol_incorrect">presentation error</span></td>
	<td>Il programma &egrave; stato compilato ed eseguito con successo ma il risultato prodotto non &egrave; <strong>identico</strong> a quello atteso 
	in termini di presentazione.<br/>
	Spesso non sono stati rispettati i vincoli sul modo di visualizzare il risultato (ad esempio, presenza di eccessivi spazi o mancanza di un "a capo").</td></tr>
<tr><td style="vertical-align: top;"><img class="vmiddle" src="../images/wrong-answer.png" alt="wrong answer"/></td><td style="vertical-align: top;"><span class="sol sol_incorrect">wrong answer</span></td>
	<td>Il programma &egrave; stato compilato ed eseguito con successo ma il risultato prodotto non &egrave; quello atteso per almeno uno dei 
	casi di test.</td></tr>
<tr class="roweven"><td style="vertical-align: top;"><img class="vmiddle" src="../images/run-error.png" alt="runtime error"/></td><td style="vertical-align: top;"><span class="sol sol_incorrect">run error</span></td>
	<td>Il programma &egrave; stato compilato con successo ma durante l'esecuzione &egrave; andato in errore.<br/>
	Questo si verifica di solito per problemi di allocazione di memoria, di accessi a indirizzi sbagliati, di divisioni per zero.</td></tr>
<tr><td style="vertical-align: top;"><img class="vmiddle" src="../images/compiler-error.png" alt="compilation error"/></td><td style="vertical-align: top;"><span class="sol sol_incorrect">compilation error</span></td>
	<td>Non &egrave; stato possibile compilare il programma.<br/>
	Controlla con attenzione i messaggi generati dal compilatore (facendo distinzione tra avvertimenti - Warning - ed errori - Error).</td></tr>
<tr><td colspan="3">&lt; <a href='index.php'>back</a></td></tr>
</table>
</center>
<div class='clear'></div>
<?php
require(LIBWWWDIR . '/footer.php');
?>