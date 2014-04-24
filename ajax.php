<?php
ini_set('display_errors', 1);
define ('SEARCH_URL', 'https://de.songselect.com/search/results?AllowRedirect=true&PageSize=1000&SearchTerm=');
define ('SEARCH_STRING', '<p class="media-info-heading">CCLI-Liednummer: ');
define ('ERROR_STRING', 'Zu Ihrer Suchanfrage wurden keine Lieder gefunden.');
define ('SONG_URL', 'https://de.songselect.com/songs/');

require_once('simple_html_dom.php');


function manualForm($s, $id) {
	$fid = uniqid('form-manual-');
	$s = '<form '.$fid.'" method="post" action="choose.php" target="song-submit">'
		.'<input type="hidden" name="id" value="'.$id.'" />'
		.'<input type="text" name="no" />'
		.'<input type="submit" onclick="javascript:$(\'#song-preview-'.$_GET['id'].'\').hide();" value="Manuell eintragen">'
		.'</form>'
		.'<a href="'.SEARCH_URL.$s.'" target="song-submit">Selbst suchen</a>'
		.'<form '.$fid.'" method="post" action="choose.php" target="song-submit">'
		.'<input type="hidden" name="id" value="'.$id.'" />'
		.'<input type="hidden" name="no" value="--" />'
		.'<input type="submit" onclick="javascript:$(\'#song-preview-'.$_GET['id'].'\').hide();" value="Dauerhaft als nicht gefunden markieren">'
		.'</form>';
	return $s;
}


//$s = urlencode(str_replace(($_GET['s'],'+', '%20'));
$s = rawurlencode($_GET['s']);


//echo SEARCH_URL.$s.'<br /><pre>';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, SEARCH_URL.$s);
curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_MAXREDIRS, 100);
$ss_raw = trim(curl_exec($ch));
curl_close($ch);

//die ('Raw: '.$ss_raw);

if (($x=strpos($ss_raw, SEARCH_STRING)) !== false) {
	$no = substr($ss_raw, $x+strlen(SEARCH_STRING));
	$no = substr($no, 0, strpos($no, '<'));
	$success = true;
	$single = true;
	
	// add this to the db 
	if ($_GET['id'] >0) { 
		$db = new SQLite3('songs.sqlite');
		$sql = 'UPDATE songs SET ccli_number=\''.$no.'\' WHERE id='.$_GET['id'];
		$db->query($sql);
		$db->close();
	}
} else {
	$success = (strpos($ss_raw, ERROR_STRING) === false);
	if ($success) {
		// we have multiple results!
		$html = str_get_html($ss_raw);
		$tbl = $html->find('table.song-listing');
		if (count($tbl)) $tbl = $tbl[0];
		foreach ($tbl->find('tr') as $tr) {
			$tds = $tr->find('td');
			if (count($tds)) {
				$td = $tds[0];
				$song['title'] = $td->find('a', 0)->plaintext;
				$tmp = str_replace('/songs/', '', $td->find('a', 0)->href);
				// format no:
				$tmp = explode('/', $tmp);
				$song['no'] = $tmp[0];
				
				$urlTitle = str_replace(' ', '-', strtolower($song['title']));
				$song['url'] = SONG_URL.$song['no'].'/'.$urlTitle;
				
				// find authors
				$a = array();
				foreach ($td->find('ul.authors', 0)->find('li') as $li) $a[] = $li->plaintext;
				$song['authors'] = join(', ', $a);

				// find catalogs
				$a = array();
				foreach ($td->find('ul.catalogs', 0)->find('li') as $li) $a[] = $li->plaintext;
				$song['catalogs'] = join(', ', $a);
				
				
				$songs[] = $song;
			}
		}
		
		// build form
		$id = uniqid('form-');
		$first = true;
		$form = '<form id="'.$id.'" method="post" action="choose.php" target="song-submit">'
				.'<input type="hidden" name="id" value="'.$_GET['id'].'" />'
				.'<table><tr>';
		foreach ($songs as $song) {
			$form .= '<td valign="top"><input type="radio" name="no" value="'.$song['no'].($first ? '" checked' : '"').'></td>'
					.'<td valign="top"><b><a href="'.$song['url'].'/viewlyrics" target="song-submit">'.$song['title'].'</a></b><br />'
					.$song['authors'].'<br /><i>'.$song['catalogs'].'</i></td>';
			$first = false;
		}
		$form .= '</tr></table><hr /><input type="submit" onclick="javascript:$(\'#song-preview-'.$_GET['id'].'\').hide();" value="Ausw&auml;hlen">'
				.'<input type="button" onclick="javascript:$(\'#song-preview-'.$_GET['id'].'\').hide();" value="Verbergen" />'
				.'</form>'.manualForm($s, $_GET['id']).'<hr />';
	}
	$single = false;
	$no = 0;
}
echo json_encode(array('success' => $success, 'single' => $single, 'no' => $no, 'form' => $form));