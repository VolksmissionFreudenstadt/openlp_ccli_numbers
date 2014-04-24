<?php
if ($_POST['id'] && $_POST['no']) {
	echo 'This sets CCLI no '.$_POST['no'].' for song #'.$_POST['id'].'<br />';
	
	$db = new SQLite3('songs.sqlite');
	echo 'open: '.$db->lastErrorMsg().'<br />';
	$sql = 'UPDATE songs SET ccli_number=\''.$_POST['no'].'\' WHERE id='.$_POST['id'];
	echo $sql.'<br />';
	$db->query($sql);
	echo 'update query: '.$db->lastErrorMsg().'<br />';
//	$db->close();
//	echo 'close: '.$db->lastErrorMsg().'<br />';
	
}
?>
<script>
window.close();
</script>