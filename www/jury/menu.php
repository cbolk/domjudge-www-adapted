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
</ul>
</div>

<?php putClock(); ?>
</div>

<?php
	echo "<div id='main'>";


