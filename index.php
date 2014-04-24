<html>
<head>
<style> 
form {
	display: inline;
}
</style>
<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
</head>
<body>
<?php

ini_set('display_errors', 1);
$db = new SQLite3('songs.sqlite');

// get all songs without CCLI number
$sql = 'SELECT * FROM songs WHERE ccli_number=\'\';';
$res = $db->query($sql);

while ($row = $res->fetchArray()) $rows[] = $row;

if (count($rows)) {
	echo '<ul>';
	foreach ($rows as $row) {
		
		// get authors for this song
		$sql = 'SELECT * FROM authors_songs WHERE song_id='.$row['id'];
		$res = $db->query($sql);
		$aa = array();
		while ($a = $res->fetchArray()) {
			$sql = 'SELECT * FROM authors WHERE id='.$a['author_id'];
			$res2 = $db->query($sql);
			while ($author = $res2->fetchArray()) $aa[] = utf8_decode($author['display_name']);
		}
		$row['authors'] = join(', ', $aa);
		
		
		echo '<li id="song-preview-'.$row['id'].'"><span class="song" data-id="'.$row['id'].'" data-uenc="'
			.rawurlencode($row['search_lyrics'])
			.'">'.utf8_decode($row['title']).' ('.$row['authors'].')'
			.'&nbsp;<span class="preview" style="display: block; border: solid 1px gray; background-color: lightyellow;">'.utf8_decode($row['search_lyrics']).'</span><span class="response"><img src="throbber.gif" /></span></span></li>';
	}
	echo '</ul>';
	
	echo '<hr />'.count($rows).' Lieder ohne CCLI-Nummer';
}


if (!$_GET['stop']) {
?>
<script>
// now for the AJAX part ...

$('span.preview').hide();

$('li').each(function(index){
	$(this).find('span.song').each(function(index){
		$.ajax('ajax.php?id='+$(this).data('id')+'&s='+$(this).data('uenc'), {context: this}
		).done(function(data){
			var json = $.parseJSON(data);
			if (json.success)
				if (json.single)
					$(this).find('span.response').text(json.no);
				else {
					$(this).find('span.preview').show();
					$(this).find('span.response').html(json.form);
				}
			else
				$(this).find('span.response').html('<span style="color:red">nicht gefunden</span>');
		});
	});
});

</script>
<?php } ?>
</body></html>