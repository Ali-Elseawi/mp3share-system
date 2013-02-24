<?php
/****************************************************/
/* mp3share system v 1.1 ~ Developer : Ali Elselawi */
/******************** 24/2/2013 *********************/
/****************************************************/


/****************************************************/
/* Mp3 Files uploader.. with editng the xml file ****/
/****************************************************/
$upload_folder = "assets/mp3";
if(count($_FILES)>0) {

  $haystack=$_FILES['upload']['name'];
	$needle=".mp3";
	$length = strlen($needle);
	$result= substr($haystack, -$length);
	if ($result!==$needle) {$read = "false";} else {$read="true";}
	if ($read=="false") {die();}
	if( move_uploaded_file( $_FILES['upload']['tmp_name'] , $upload_folder.'/'.$_FILES['upload']['name'] ) ) {

		//add the data to the xml file
		$newlink="assets/mp3/".$_FILES['upload']['name'];
		$newtitle=$_FILES['upload']['name'];


		$playlistxml = simplexml_load_file('playlist.xml');
		$filelink = $playlistxml->addChild('file');
		$filelink->addChild('link', $newlink);
		$filelink->addChild('title', $newtitle);
		file_put_contents('playlist.xml', $playlistxml->asXml());
	}
	exit();
} 

else if(isset($_GET['up'])) {

	if(isset($_GET['base64'])) {
		$content = base64_decode(file_get_contents('php://input'));
	} else {
		$content = file_get_contents('php://input');
	}

	$headers = getallheaders();
	$headers = array_change_key_case($headers, CASE_UPPER);
	
	$haystack=$headers['UP-FILENAME'];
	$needle=".mp3";
	$length = strlen($needle);
	$result= substr($haystack, -$length);
	if ($result!==$needle) {$read = "false";} else {$read="true";}
	if ($read=="false") {die();}
	if(file_put_contents($upload_folder.'/'.$headers['UP-FILENAME'], $content)) {
		echo 'done';
	}
			//add the data to the xml file
		$newlink="assets/mp3/".$headers['UP-FILENAME'];
		$newtitle=$headers['UP-FILENAME'];


		$playlistxml = simplexml_load_file('playlist.xml');
		$filelink = $playlistxml->addChild('file');
		$filelink->addChild('link', $newlink);
		$filelink->addChild('title', $newtitle);
		file_put_contents('playlist.xml', $playlistxml->asXml());
	exit();
}

/****************************************************/
/* reading data from XML file named playlist.xml ****/
/****************************************************/
$xml = simplexml_load_file('playlist.xml');

$counted=0;
foreach ($xml->file as $file) {$counted=$counted+1;}
$counted=$counted-1;


$currentnumber=0;

if (isset($_GET['next'])) {
	if ($_GET['past'] !=  $counted) {
		$currentnumber=$_GET['past']+1;
	}
}

if (isset($_GET['prev'])) {
	if ($_GET['past'] !=  0) {
		$currentnumber=$_GET['past']-1;
	}
}

$currentlink = $xml->file[$currentnumber]->link;
$currenttitle= $xml->file[$currentnumber]->title;

/****************************************************/
/* Getting the Song meta data (mp3 file tags) *******/
/****************************************************/
$mp3 = $currentlink ; /*here is the location of the MP3 File*/
$filesize = filesize($mp3);
$file = fopen($mp3, "r");
fseek($file, -128, SEEK_END);
$tag = fread($file, 3);
if($tag == "TAG") {
	// the data will be saved in array called $data and the values are like this
 	$data["song"] = trim(fread($file, 30));
 	$data["artist"] = trim(fread($file, 30));
 	$data["album"] = trim(fread($file, 30));
 	$data["year"] = trim(fread($file, 4));
 	$data["comment"] = trim(fread($file, 30));
	$data["genre"] = trim(fread($file, 1));}
else { die("MP3 file does not have any ID3 tag!"); }
fclose($file);
/****************************************************/
/* Getting the albumArt from albumart.org ***********/
/****************************************************/
$songname=$data["album"];
$songname= str_replace(" ","+",$songname);
$albumartorg1="http://www.albumart.org/index.php?skey=";
$albumartorg2="&itempage=1&newsearch=1&searchindex=Music";
$albumartorgpagelink=$albumartorg1.$songname.$albumartorg2;
$pagetostring = file_get_contents($albumartorgpagelink);
$begin=stripos($pagetostring,"http://ecx.");
$end=stripos($pagetostring,"_.jpg")+5;
$length=$end-$begin;
$imagelink=substr($pagetostring,$begin,$length);
?>
<!doctype html>
<html>
<head>
	<script src="assets/scripts/html5uploader.js"></script>
	<link rel="stylesheet" type="text/css" href="assets/styles/styles.css">
	<title>MP3Share System V1.1</title>
</head>
<body onload="new uploader('drop', 'status', 'player.php', 'list');">
	<div class="audioplayer">
		<span class="artist"><?php echo $data['artist']; ?></span><span class="artist"><?php echo $data['artist']; ?></span> <!-- has been duplicated just to get bold text, i like it that way -->
		<span class="songname"><?php echo $data['song']; ?></span><span class="songname"><?php echo $data['song']; ?></span> <!-- has been duplicated just to get bold text, i like it that way -->
		<img class="cover" alt="" src="<?php echo $imagelink;?>" />
		<audio src="<?php echo $currentlink; ?>" id="audioplayer"></audio>
		<form action="player.php" method="GET">
			<input class="prevbutton" type="submit" value=" " name="prev"<?php if ($currentnumber==0) {echo "disabled";}?>>
			<input class="nextbutton" type="submit" value=" " name="next"<?php if ($currentnumber==$counted) {echo "disabled";}?>>
			<input type="hidden" name="past" value="<?php echo $currentnumber ?>">
		</form>
		<button class="showplaylist" name="showplaylist">show playlist</button>
		<button class="uploadfile" name="uploadfile">upload File</button>
	</div>
	<div class="playslistbox">
		<?php
		$cc=1;
		$xml = simplexml_load_file('playlist.xml');
		foreach ($xml->file as $file) {
			echo '<div class="playlistitem">'.$cc.". ".$file->title."</div>";
			$cc=$cc+1;
		}
		?>
	</div>
	<div id="box">
		<div id="drop"></div>
		<div id="status">Drop Here</div>
	</div>
<div class="developer">MP3Share System v1.1 - Developer: Ali Elselawi</div>
	<script type="text/javascript" src="assets/scripts/jquery.js"></script>
	<script type="text/javascript" src="assets/scripts/mediaelement-and-player.js"></script>
	<script type="text/javascript" src="assets/scripts/script.js"></script>

</body>
</html>
