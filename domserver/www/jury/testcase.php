<?php
/**
 * View/edit testcases
 *
 * $Id: testcase.php 3209 2010-06-12 00:13:43Z eldering $
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 *
 * Modified by CBolk
 */

$pagename = basename($_SERVER['PHP_SELF']);

$probid = @$_REQUEST['probid'];

require('init.php');

$INOROUT = array('input','output');

// Download testcase
if ( isset ($_GET['fetch']) && in_array($_GET['fetch'], $INOROUT)) {
	$rank  = $_GET['rank'];
	$fetch = $_GET['fetch'];
	$filename = $probid . $rank . "." . substr($fetch,0,-3);

	$inoutfile = substr($fetch, 0, 1) . "file";

	$fioname = $DB->q("MAYBEVALUE SELECT $inoutfile
	                FROM testcase WHERE probid = %s AND rank = %i",
	               $probid, $rank);

	$size = $DB->q("MAYBEVALUE SELECT OCTET_LENGTH($fetch)
	                FROM testcase WHERE probid = %s AND rank = %i",
	               $probid, $rank);

	// sanity check before we start to output headers
	if ( $size===NULL || !is_numeric($size)) error("Problem while fetching testcase");

	//CBolk: if there is an input file, download that file
	if($fioname != NULL && $fioname != "") $filename = $fioname;

	header("Content-Type: application/octet-stream; name=\"$filename\"");
	header("Content-Disposition: inline; filename=\"$filename\"");
	header("Content-Length: $size");

	// This may not be good enough for large testsets, but streaming them
	// directly from the database query result seems overkill to implement.
	echo $DB->q("VALUE SELECT $fetch FROM testcase
	             WHERE probid = %s AND rank = %i", $probid, $rank);

	exit(0);
} else if ( isset($_POST['cmd']) ) {
	$pcmd = $_POST['cmd'];
	$probid = $_POST['probid'];

	if(isset($pcmd['toggle_public'])){
		$ntests = $DB->q("MAYBEVALUE SELECT count(*) FROM testcase WHERE probid = %s", $probid);
		$stop = 0;
	    while($ntests >= 0 && !$stop){
	    	if($pcmd['toggle_public'][$ntests] == "toggle")
	    		$stop = 1;
	    	else 
				$ntests--;
	    }
	    $strSQL = "UPDATE testcase SET public = NOT public WHERE probid = " . $probid . " AND rank = " . $ntests;
	    $DB->q($strSQL);
//	    exit (0);
	}
}

//CBolk: added input/output from/to file
$data = $DB->q('KEYTABLE SELECT rank AS ARRAYKEY, testcaseid, rank, description,
                OCTET_LENGTH(input)  AS size_input,  md5sum_input, ifile,
                OCTET_LENGTH(output) AS size_output, md5sum_output, ofile,
                public
                FROM testcase WHERE probid = %s ORDER BY rank', $probid);

// Reorder testcases
if ( isset ($_GET['move']) ) {
	$move = $_GET['move'];
	$rank = (int)$_GET['rank'];

	// First find testcase to switch with
	$last = NULL;
	$other = NULL;
	foreach( $data as $curr => $row ) {
		if ( $curr==$rank && $move=='up' ) {
			$other = $last;
			break;
		}
		if ( $rank==$last && $move=='down' && $last!==NULL ) {
			$other = $curr;
			break;
		}
		$last = $curr;
	}

	if ( $other!==NULL ) {
		// (probid, rank) is a unique key, so we must switch via a
		// temporary rank, and use a transaction.
		$tmprank = 999999;
//		$DB->q('START TRANSACTION');
		$DB->q('UPDATE testcase SET rank = %i
		        WHERE probid = %s AND rank = %i', $tmprank, $probid, $other);
		$DB->q('UPDATE testcase SET rank = %i
		        WHERE probid = %s AND rank = %i', $other, $probid, $rank);
		$DB->q('UPDATE testcase SET rank = %i
		        WHERE probid = %s AND rank = %i', $rank, $probid, $tmprank);
//		$DB->q('COMMIT');
	}

	// Redirect to the original page to prevent accidental redo's
	header('Location: testcase.php?probid=' . urlencode($probid));
	return;
}

$title = 'Testcases for problem '.htmlspecialchars(@$probid);

require(LIBWWWDIR . '/header.php');
require(LIBWWWDIR . '/forms.php');

requireAdmin();

if ( ! $probid ) error("Missing or invalid problem id");

echo "<h1>" . $title ."</h1>\n\n";
$result = '';
if ( isset($_POST['probid']) ) {

	$maxrank = 0;
	foreach($data as $rank => $row) {
		$result .= "<br>" . $rank;
		foreach($INOROUT as $inout) {
	
			if ( $rank>$maxrank ) $maxrank = $rank;
	
			$fileid = 'update_'.$inout;
			$iofilename = substr($inout, 0, 1) . "file";
			$fileioid = 'update_' .$iofilename;
			if ( !empty($_FILES[$fileid]['name'][$rank]) ) {
	
				// Check for upload errors:
				checkFileUpload ( $_FILES[$fileid]['error'][$rank] );
	
				$content = file_get_contents($_FILES[$fileid]['tmp_name'][$rank]);
				if ( $DB->q("VALUE SELECT count(testcaseid)
							 FROM testcase WHERE probid = %s AND rank = %i",
							$probid, $rank) ) {
					$DB->q("UPDATE testcase SET md5sum_$inout = %s, $inout = %s
							WHERE probid = %s AND rank = %i",
						   md5($content), $content, $probid, $rank);
				} else {
					$DB->q("INSERT INTO testcase (probid,rank,md5sum_$inout,$inout)
							VALUES (%s,%i,%s,%s)",
						   $probid, $rank, md5($content), $content);
				}
				$result .= "<li>Updated $inout for testcase $rank from " .
					htmlspecialchars($_FILES[$fileid]['name'][$rank]) .
					" (" . htmlspecialchars($_FILES[$fileid]['size'][$rank]) .
					" B)</li>\n";
			} else if (!empty($_FILES[$fileioid]['name'][$rank])) {
				// Check for upload errors:
				checkFileUpload ( $_FILES[$fileioid]['error'][$rank] );
				$content = file_get_contents($_FILES[$fileioid]['tmp_name'][$rank]);
				if ( $DB->q("VALUE SELECT count(testcaseid)
							 FROM testcase WHERE probid = %s AND rank = %i",
							$probid, $rank) ) {
					$DB->q("UPDATE testcase SET md5sum_$inout = %s, $inout = %s, $iofilename = %s
							WHERE probid = %s AND rank = %i",
						   md5($content), $content, $_FILES[$fileioid]['name'][$rank], $probid, $rank);
				} else {
					$DB->q("INSERT INTO testcase (probid,rank,md5sum_$inout,$inout, $iofilename)
							VALUES (%s,%i,%s,%s,%s)",
						   $probid, $rank, md5($content), $content, $_FILES[$fileioid]['name'][$rank]);
				}
				$result .= "<li>Updated $iofilename for testcase $rank from " .
					htmlspecialchars($_FILES[$fileioid]['name'][$rank]) .
					" (" . htmlspecialchars($_FILES[$fileioid]['size'][$rank]) .
					" B)</li>\n";
				
			}
		}
	
		if ( isset($_POST['description'][$rank]) ) {
			$DB->q('UPDATE testcase SET description = %s WHERE probid = %s
					AND rank = %i', $_POST['description'][$rank], $probid, $rank);
	
			$result .= "<li>Updated description for testcase $rank</li>\n";
		}

	} // end: foreach $data
	if ( !empty($_FILES['add_input']['name']) ||
		 !empty($_FILES['add_ifile']['name']) || 
		 !empty($_FILES['add_output']['name']) ||
		 !empty($_FILES['add_ofile']['name'])) {
		$result .= "<br>new!";
		$content = array();
		$rank = $maxrank + 1;
		$src = "";
		foreach($INOROUT as $inout) {
			$iofile = substr($inout, 0, 1) . "file";
			if ( empty($_FILES['add_'.$inout]['name']) ) {
				if (empty($_FILES['add_'.$iofile]['name'])) 
					warning("No $inout file specified for new testcase, ignoring.");
				else {
					checkFileUpload ( $_FILES['add_'.$iofile]['error'] );	
					$content[$inout] = file_get_contents($_FILES['add_'.$iofile]['tmp_name']);
					$src .= "file for <strong>file " . $inout . "</strong> " . htmlspecialchars($_FILES['add_'.$iofile]['name']);
					$src .= " (" . htmlspecialchars($_FILES['add_'.$iofile]['size']) . " B)<br/>";
				}
			} else {
				checkFileUpload ( $_FILES['add_'.$inout]['error'] );
				$content[$inout] = file_get_contents($_FILES['add_'.$inout]['tmp_name']);
				$src .= "file for <strong>std " . $inout . "</strong> " . htmlspecialchars($_FILES['add_'.$inout]['name']);
				$src .= " (" . htmlspecialchars($_FILES['add_'.$inout]['size']) . " B)<br/>";
			}
		}

		if ( !empty($content['input']) && !empty($content['output']) ) {
			$strSQL = "INSERT INTO testcase
			        (probid,rank,md5sum_input,md5sum_output,input,output,ifile,ofile,description, public)
			        VALUES ('" . $probid . "'," . $rank . ",'". md5(@$content['input']) ."','". md5(@$content['output']) .
			        "','".@$content['input']."','".@$content['output']."','".$_FILES['add_ifile']['name']."','" . 
			        $_FILES['add_ofile']['name'] . "','" .  @$_POST['add_desc'] . "'," . @$_POST['add_public'].")";
			$DB->q($strSQL);
			$result .= $strSQL;
			$result .= "<li>Added new testcase $rank from:<br/>" . $src . "</li>\n";
		}
	}

}
if ( !empty($result) ) {
	echo "<ul>\n$result</ul>\n\n";

	// Reload testcase data after updates
	$data = $DB->q('KEYTABLE SELECT rank AS ARRAYKEY, testcaseid, rank, description,
	                OCTET_LENGTH(input)  AS size_input,  md5sum_input, ifile, 
	                OCTET_LENGTH(output) AS size_output, md5sum_output, ofile, public
	                FROM testcase WHERE probid = %s ORDER BY rank', $probid);
}

echo "<p><a href=\"problem.php?id=" . urlencode($probid) . "\">back to problem " .
	htmlspecialchars($probid) . "</a></p>\n\n";

echo addForm('', 'post', null, 'multipart/form-data') .
    addHidden('probid', $probid);

    

if ( count($data)==0 ) {
	echo "<p class=\"nodata\">No testcase(s) yet.</p>\n";
} else {
	?>
<table class="list testcases">
        <!--
<colgroup>
<col id="testrank" /><col class="filename" /><col id="testupload" />
<col id="testsize" /><col id="testmd5" /><col id="testdesc" />
</colgroup>
        -->
<thead><tr>
<th scope="col">#</th><th scope="col">download</th>
<th scope="col">size</th>
<!--th scope="col">md5</th -->
<th scope="col">upload new data (stdin/stdout)</th><th scope="col">upload new file in/out</th><th scope="col">description</th>
<th scope="col">public</th><th scope="col">delete</th>
</tr></thead>
<tbody>
<?php
}

foreach( $data as $rank => $row ) {
	foreach($INOROUT as $inout) {
		$iofilename = substr($inout, 0, 1) . "file";
		$iof = "";
		if(($row[$iofilename] != NULL) && ($row[$iofilename] != ""))
			$iof = "<acronym class='tred small' title='". $row[$iofilename] . "' >&#10058;</acronym>";
		echo "<tr>";
		if ( $inout=='input' ) {
			echo "<td rowspan=\"2\" class=\"testrank\">" .
			    "<a href=\"./testcase.php?probid=" . urlencode($probid) .
			    "&amp;rank=$rank&amp;move=up\">&uarr;</a>$rank" .
			    "<a href=\"./testcase.php?probid=" . urlencode($probid) .
			    "&amp;rank=$rank&amp;move=down\">&darr;</a></td>";
		}
		echo "<td class=\"filename\"><a href=\"./testcase.php?probid=" .
		    urlencode($probid) . "&amp;rank=$rank&amp;fetch=" . $inout . "\">" .
		    htmlspecialchars($probid) . $rank . "." . substr($inout,0,-3) . "&nbsp;". $iof . "</a></td>" .
		    "<td class=\"testsize\">" . htmlspecialchars($row["size_$inout"]) . "&nbsp;B</td>" .
//		    "<td class=\"testmd5\">" . htmlspecialchars($row["md5sum_$inout"]) . "</td>" .
		    "<td class=\"testdesc\">" . addFileField("update_".$inout."[$rank]") . "</td>";
		    echo "<td class=\"testdesc\">" . addFileField("update_".$iofilename."[$rank]") . "</td>";
		if ( $inout=='input' ) {
			echo "<td rowspan=\"2\" class=\"testdesc\" onclick=\"editTcDesc($rank)\">" .
			    "<textarea id=\"tcdesc_$rank\" name=\"description[$rank]\" cols=\"50\" rows=\"2\">" .
			    htmlspecialchars($row['description']) . "</textarea></td>";
			echo "<td rowspan=\"2\" class=\"testdesc\" >" . printyn($row['public']) . ' ' .  
				addSubmit('toggle', 'cmd[toggle_public]['.$rank.']',
				"return confirm('Make this testcase #" . $rank . " " . ($row['public'] ? 'HIDDEN' : 'PUBLIC') . "?')") . "</td>";
			echo "<td rowspan=\"2\" class=\"testdesc tcenter editdel\">" .
			    "<a href=\"delete.php?table=testcase&amp;testcaseid=$row[testcaseid]&amp;referrer=" .
			    urlencode('testcase.php?probid='.$probid) . "\">" .
			    "<img src=\"../images/delete.png\" alt=\"delete\"" .
			    " title=\"delete this testcase\" class=\"picto\" /></a></td>";

			    // hide edit field if javascript is enabled
			    echo "<script type=\"text/javascript\" language=\"JavaScript\">" .
			    	"hideTcDescEdit($rank);</script>";
		}
		echo "</tr>\n";
	}
}

if ( count($data)!=0 ) echo "</tbody>\n</table>\n";

?>
<h3>Create new testcase</h3>

<table>
<tr><td>Input testdata: </td><td><?php echo addFileField('add_input')  ?></td></tr>
<tr><td>File in input <span class='tred'>&#10058;</span>: </td><td><?php echo addFileField('add_ifile')  ?></td></tr>
<tr><td>Output testdata:</td><td><?php echo addFileField('add_output') ?></td></tr>
<tr><td>File in output <span class='tred'>&#10058;</span>: </td><td><?php echo addFileField('add_ofile')  ?></td></tr>
<tr><td>Description:    </td><td><?php echo addInput('add_desc','',30); ?></td></tr>
<tr><td>Public or hidden:</td><td><?php echo addRadioButton('add_public',true,1); ?>Public <?php echo addRadioButton('add_public',false,0); ?>Hidden</td></tr>
</table>
<?php

echo "<br />" . addSubmit('Submit all changes') . addEndForm();

require(LIBWWWDIR . '/footer.php');
