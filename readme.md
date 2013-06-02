#Requirements
1: [pdo sqlite](http://php.net/manual/en/ref.pdo-sqlite.php)

2: [gd](http://php.net/manual/en/book.image.php)

3: Due to my coding style, you must allow [short tags](http://php.net/manual/en/ini.core.php) \("<?"\) in php.

4: Installation of [ImageMagick](http://www.imagemagick.org/script/index.php). You can test this by typing "convert" in terminal. If needed, you can change configuration in index.php for the bin path.

#Installation
1: Copy all files to your server.

2: Change permissions to folders databases, load, data, and thumbs so that php can write to them. If you do not know the correct permissions, 777 will work but is inscure on shared servers.

3: Visit the database on your server and the setup process will commence.

4: Once you have finished the setup process. You may delete the setup file in the code folder if you do not want to allow anyone to setup a new database if for some reason the file gets currupted.

#Setting up your server
##Nginx
Here is the configuration you need to add to your nginx server.
```text
location ~* /(databases|load|code|external_data_plugins) {
	rewrite ^ /index.php?$args;
}
location ~* /(data|thumbs)/.*(?<\!\.gif|\.png|\.jpg|\.jpeg|\.tif|\.tiff)$ {
	rewrite ^ /index.php?$args;
}
location / {
	try_files $uri $uri/ /index.php?$args;
}
```
It is best to placet the ones with "rewrite" above your php include.

You can test by visiting /code/index.php to see if it loads the index file or if it shows a 404 error.

##Apache
For Apache, i've already included the .htaccess files needed. Just make sure your server is configured to allow htaccess and mod_rewrite. Best test by visiting /code/index.php.

#Getting OCR to work.
I've included code to OCR images for text. It's not perfect, but it provides great power.

In-order to use it, you must first install [OpenCV](http://opencv.org/) and [Tesseract-OCR]([https://code.google.com/p/tesseract-ocr/). Then you should just be able to run ```./ocr.sh``` in terminal with the image database directory as your current working directory. Only tested on OS X.

#Writing an External Data Plugin
External Data plugins allows you to write plugins which grabs data from another site about an image and put it into the metadata to make search better. For an example, if there is an image tag site which contains your image and good tags, you could write your plugin to grab the tags and store them so when you search for say green, your image will popup as the tag green was on that image via the site.

Writing a plugin is simple. Make a new file in the folder external_data_plugins and then put your code inside of it to grab the data and fill the database.
```php
<?
$ch = curl_init();
curl_setopt_array($ch, array(
	CURLOPT_URL => "http://mrgeckosmedia.com/some-api",
	CURLOPT_POSTFIELDS => array(
		"md5" => $hash
	),
	CURLOPT_RETURNTRANSFER => true
));
$received = json_decode(curl_exec($ch), true);
curl_close($ch);
if (isset($received['result']['tags'])) {
	if (!empty($external_data))
		$external_data .= " ";
	//Append to the "$external_data" variable with the description to provide rich content.
	$external_data .= $received['result']['description'];
	
	//Prefill Tags in database.
	$dtags = explode(" ", $received['result']['tags']);
	for ($i=0; $i<count($dtags); $i++) {
		if (!in_array($dtags[$i], $tags))
			array_push($tags, $dtags[$i]);
	}
}
?>
```
You have access to many variables about the file being processed including it's name and extension. Just look at code/upload.php to see what is available.

#Known Problems
There is going to be issues with people who add tags or other user fields that contains a quotation mark or anything else which could interrupt the HTML code (not an sql injection). I was too lazy and didn't want to look up my code for preventing these sorts of issues. So for now, just use it as a personal database. There isn't a public registration module anyway. Maybe when I get time, I'll fix these possible issues.

There isn't any error reporting in the API and there isn't anyway for the user to know that such an error such as network issues or database issues occured.