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
if ($_MGM['path'][1]=="process") {
	$file = (isset($_REQUEST['file']) ? $_REQUEST['file'] : "");
	$hash = pathinfo($file, PATHINFO_FILENAME);
	
	echo "Hash: ".$hash."<br />\n";
	
	$descriptorspec = array(
		0 => array("pipe", "r"),
		1 => array("pipe", "w"),
		2 => array("pipe", "w")
	);
	$process = proc_open("./ocr \"".$file."\"", $descriptorspec, $pipes, getcwd());
	
	fclose($pipes[0]);
	$ocr = "";
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
	echo "OCR: ".$ocr."<br />\n";
	
	databaseQuery("UPDATE images SET ocr=%s WHERE hash=%s", $ocr, $hash);
	exit();
}
$files = glob("./data/*");
require_once("header.php");
?>
Processing...<br />
<div id="result"></div>
<script type="text/javascript">
var files = new Array(<?
	$array = "";
	for ($i=0; $i<count($files); $i++) {
		//if (in_array(pathinfo($files[$i], PATHINFO_EXTENSION), $allowedExtensions)) {
		if ($array!="")
			$array .= ",";
		$array .= "\"".str_replace("\"", "\\\"", $files[$i])."\"";
	}
	echo $array;
?>);
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
		request.open("post", "<?=generateURL("re-ocr/process")?>", true);
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
?>