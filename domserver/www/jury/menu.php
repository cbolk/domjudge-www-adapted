<!--
  Modified by CBolk
-->


<div id="header">
<div id="menutop">
<ul class="nav">
<li><a href="index.php" accesskey="h">home</a></li>
<li><a href="contests.php" accesskey="k">contests</a></li>
<li><a href="problems.php" accesskey="p">problems</a></li>
<?php	if ( IS_ADMIN ) { ?>
<li><a href="judgehosts.php" accesskey="j">judgehosts</a></li>
<?php   } ?>
<li><a href="teams.php" accesskey="t">teams</a></li>
<?php	if ( $nunread_clars > 0 ) { ?>
<li><a class="new" href="clarifications.php" accesskey="c" id="menu_clarifications">clarifications (<?php echo $nunread_clars?> new)</a></li>
<?php	} else { ?>
<li><a href="clarifications.php" accesskey="c" id="menu_clarifications">clarifications</a></li>
<?php	} ?>
<li><a href="submissions.php" accesskey="s">submissions</a></li>
<li><a href="scoreboard.php" accesskey="b">scoreboard</a></li>
<li><a href="participation.php" accesskey="p">participation</a></li>
<li><a href="upload4student.php" accesskey="u">upload</a></li>
<li><a href="eval_laboratory.php" accesskey="l">lab</a></li>
<li><a href="eval_homework.php" accesskey="e">exs</a></li>
</ul>
</div>

<?php 

putClock(); 
$refresh_flag = !isset($_COOKIE["domjudge_refresh"]) || (bool)$_COOKIE["domjudge_refresh"];

if ( isset($refresh) ) {
	echo "<div id=\"refresh\">\n" .
	    addForm('toggle_refresh.php', 'get') .
	    addHidden('enable', ($refresh_flag ? 0 : 1)) .
	    addSubmit(($refresh_flag ? 'Dis' : 'En' ) . 'able refresh', 'submit') .
	    addEndForm() . "</div>\n";
}

?>
</div>

<?php
	echo "<div id='main'>";


