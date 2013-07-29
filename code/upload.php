<?
//
//  Copyright (c) 2013 Mr. Gecko's Media (James Coleman). http://mrgeckosmedia.com/
//
//  Permission to use, copy, modify, and/or distribute this software for any purpose
//  with or without fee is hereby granted, provided that the above copyright notice
//  and this permission notice appear in all copies.
//
//  THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
//  REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND
//  FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT,
//  OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE,
//  DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS
//  ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
//
if ($_MGM['path'][1]=="complete") {
	if ($_MGM['path'][2]=="process") {
		$file = (isset($_REQUEST['file']) ? $_REQUEST['file'] : "");
		$filename = pathinfo($file, PATHINFO_FILENAME);
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		echo "Processing ".basename($file)."<br />\n";
		if (!file_exists($file)) {
			echo "Error: <span style=\"color: #ff0000;\">File does not exist.</span>";
			exit();
		}
		$allowedExtensions = array("png", "jpg", "jpeg", "gif", "tif", "tiff", "bmp");
		if (!in_array($extension, $allowedExtensions)) {
			echo "Error: <span style=\"color: #ff0000;\">Extension is not allowed.</span>";
			unlink($file);
			exit();
		}
		
		$fileSize = filesize($file);
		echo "Size: ".$fileSize."<br />\n";
		
		list($width, $height, $type, $attr) = getimagesize($file);
		if (!isset($width) || !isset($height)) {
			echo "Error: <span style=\"color: #ff0000;\">Cannot read image.</span>";
			unlink($file);
			exit();
		}
		echo "Width: ".$width." Height: ".$height."<br />\n";
		
		$hash = md5_file($file);
		if ($hash==NULL) {
			echo "Error: <span style=\"color: #ff0000;\">Unable to hash image.</span>";
			unlink($file);
			exit();
		}
		echo "Hash: ".$hash."<br />\n";
		
		$newFile = "./data/".$hash.".".$extension;
		if (file_exists($newFile)) {
			echo "Error: <span style=\"color: #ff0000;\">File already uploaded.</span>";
			unlink($file);
			exit();
		}
		
		$ocr = "";
		if (file_exists("./ocr")) {
			$descriptorspec = array(
				0 => array("pipe", "r"),
				1 => array("pipe", "w"),
				2 => array("pipe", "w")
			);
			$process = proc_open("./ocr \"".$file."\"", $descriptorspec, $pipes, getcwd());
		
			fclose($pipes[0]);
			while (is_resource($process)) {
				$read = $pipes;
				$write = null;
				$except = null;
				$result = stream_select($read, $write, $except, 30);
			
				if ($result==0) {
					fclose($pipes[1]);
					fclose($pipes[2]);
					proc_terminate($process,9);
					break;
				} else if ($result>0) {
					$line = fread($pipes[1], 8192);
					if (strlen($line)==0) {
						fclose($pipes[1]);
						fclose($pipes[2]);
						proc_close($process);
						break;
					}
					$ocr .= $line;
				}
			}	
			echo "OCR: ".htmlspecialchars($ocr, ENT_COMPAT | ENT_HTML401, 'UTF-8', true)."<br />\n";
		}
		
		$tags = array();
		$external_data = "";
		
		$plugins = glob("./external_data_plugins/*.php");
		for ($pluginIndex=0; $pluginIndex<count($plugins); $pluginIndex++) {
			require($plugins[$pluginIndex]);
		}
		
		echo "External Data: ".htmlspecialchars($external_data, ENT_COMPAT | ENT_HTML401, 'UTF-8', true)."<br />\n";
		
		$thumbFile = "./thumbs/".$hash.".".$extension;
		$target = 150;
		$newWidth = $width;
		$newHeight = $height;
		if ($width>$target || $height>$target) {
			$widthFactor = $target/$width;
			$heightFactor = $target/$height;
			$scaleFactor = 1;
	
			if ($widthFactor<$heightFactor)
				$scaleFactor = $widthFactor;
			else
				$scaleFactor = $heightFactor;
	
			$newWidth = round($width*$scaleFactor);
			$newHeight = round($height*$scaleFactor);
		}
		if ($type==IMAGETYPE_GIF) {
			$tmp = "./thumbs/coalesce".rand().".gif";
			system($_MGM['imagemagick']."convert \"".$file."\" -coalesce \"".$tmp."\"");
			system($_MGM['imagemagick']."convert -size ".$width."x".$height." \"".$tmp."\" -resize ".$newWidth."x".$newHeight." \"".$thumbFile."\"");
			unlink($tmp);
		} else {
			system($_MGM['imagemagick']."convert -size ".$width."x".$height." \"".$file."\" -resize ".$newWidth."x".$newHeight." \"".$thumbFile."\"");
		}
		chmod($thumbFile, 0666);
		echo "Saved thumbnail.<br />\n";
		
		rename($file, $newFile);
		echo "Moved Original.<br />\n";
		
		databaseQuery("INSERT INTO images (user_id,hash,extension,name,file_size,width,height,thumb_file_size,thumb_width,thumb_height,tags,external_data,ocr,time) VALUES (%s,%s,%s,%s,%d,%d,%d,%d,%d,%d,%s,%s,%s,%d)", $_MGM['user']['docid'], $hash, $extension, $filename, $fileSize, $width, $height, filesize($thumbFile), $newWidth, $newHeight, implode(" ", $tags), $external_data, $ocr, filemtime($newFile));
		echo "Complete.<br />\n";
		exit();
	}
	$files = glob("./load/*");
	require_once("header.php");
	?>
	Processing...<br />
	<div id="result"></div>
	<script type="text/javascript">
	var files = new Array(
		<?
		$array = "";
		for ($i=0; $i<count($files); $i++) {
			//if (in_array(pathinfo($files[$i], PATHINFO_EXTENSION), $allowedExtensions)) {
			if ($array!="")
				$array .= ",\n\t\t";
			$array .= "\"".str_replace("\"", "\\\"", $files[$i])."\"";
		}
		echo $array;
	?>

	);
	var i=0;
	function processFiles() {
		if (i<files.length) {
			var status = document.createElement("p");
			status.innerHTML = "Processing "+(i+1)+" of "+files.length+" files.";
			document.getElementById("result").appendChild(status);
			
			var request = new XMLHttpRequest;
			request.onreadystatechange = function() {
				if (request.readyState==4) {
					var status = document.createElement("p");
					status.innerHTML = request.responseText;
					document.getElementById("result").appendChild(status);
					processFiles();
				}
			}
			request.open("post", "<?=generateURL("upload/complete/process")?>", true);
			request.setRequestHeader("Cache-Control", "no-cache");
			request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			request.send("file="+encodeURIComponent(files[i]));
			i++;
		} else {
			var completed = document.createElement("p");
			completed.innerHTML = "Processing Completed.";
			document.getElementById("result").appendChild(completed);
		}
	}
	
	processFiles();
	</script>
	<?
	require_once("footer.php");
	exit();
}

$file = $_SERVER['HTTP_X_FILENAME'];
if (isset($file)) {
	$input = fopen("php://input", "r");
	if (file_exists("./load/".$file))
		unlink("./load/".$file);
	$output = fopen("./load/".$file, "w");
	
	while ($data = fread($input, 1024))
		fwrite($output, $data);
	
	fclose($output);
	fclose($input);
	echo "uploaded";
	exit();
}
require_once("header.php");
?>
<script type="text/javascript">
function upload() {
	var files = document.getElementById("files").files;
	document.getElementById("files").setAttribute("disabled", "true");
	document.getElementById("uploadButton").setAttribute("disabled", "true");
	var i=0;
	function setupFile() {
		if (i<files.length) {
			document.getElementById("progress").innerHTML = "Uploading "+(i+1)+" of "+files.length+" files.";
			var file = files[i];
			if (file.name==undefined) {
				document.getElementById("progress").innerHTML = "Error: Browser unsupported.";
				return;
			}
			var request = new XMLHttpRequest;
			request.onreadystatechange = function() {
				if (request.readyState==4)
					setupFile();
			}
			request.open("post", "<?=generateURL("upload")?>", true);
			request.setRequestHeader("Cache-Control", "no-cache");
			request.setRequestHeader("X-FILENAME", file.name);
			request.setRequestHeader("Content-Type", "multipart/form-data");
			request.send(file);
			i++;
		} else {
			document.getElementById("progress").innerHTML = "Upload Complete.";
			document.getElementById("files").removeAttribute("disabled");
			document.getElementById("uploadButton").removeAttribute("disabled");
			document.getElementById("files").form.reset();
			window.location = "<?=generateURL("upload/complete")?>";
		}
	}
	setupFile();
}
</script>
<form>
<input type="file" multiple="true" id="files" />
<input type="button" id="uploadButton" onclick="upload()" value="Upload" class="btn" />
</form>
<div id="progress"></div>
<?
require_once("footer.php");
exit();
?>