<?php

/*
Creation Date: 21 March 2012
Author: Ian O'Keeffe

Modification History:
--------------------------
21-03-2012
This code is written for MS Windows (see paths defined below)
It uploads a file for processing manually, rather than using locConnect, for testing purposes

*/


$option=$_POST['select'];

require 'doStuffToXLIFF_file.php';

echo $option;

$allowed_filetypes = array('.xml', '.xlf', '.txt'); // These will be the types of file that will pass the validation.	  
$max_filesize = 10485760; // Maximum filesize in BYTES (this is equivalent to 10 MB in bytes)
$upload_path = 'c:\uploads\\'; // The place the files will be uploaded to. second \ in path is because of escape character issues for windows
$filename = $_FILES['userfile']['name']; // Get the name of the file (including file extension).

echo '<h2>Solas Component Template - Processing file selected manually</h2><br>';
echo '<hr /><br><h3>File to be processed: '.$filename.'</h3>';


//File error checking
	$ext = substr($filename, strpos($filename,'.'), strlen($filename)-1); // Get the extension from the filename.

	// Check if the filetype is allowed, if not DIE and inform the user.
   	if(!in_array($ext,$allowed_filetypes))
		die('The file you attempted to upload is not allowed.');
	
/*
	// Now check the filesize, if it is too large then DIE and inform the user.
	if(filesize($filename) > $max_filesize) //filesize only seems to work for local files and needs to have the full file path
		  die('The file you attempted to upload is too large.');
*/
	 
	// Check if we can upload to the specified path, if not DIE and inform the user.
	if(!is_writable($upload_path))
		  die('You cannot upload to the specified directory, please CHMOD it to 777.');

		  

// Upload the file to the specified path. $content will have the content of the file!
copy ($_FILES['userfile']['tmp_name'], $upload_path.$_FILES['userfile']['name']) or die ('Could not upload'); //copies file to c:\uploads\... with the same filename
	$fp      = fopen($upload_path.$filename, 'r'); //open this local c:\uploads\... file
	$fileContent = fread($fp, filesize($upload_path.$filename)); // read this file into the $fileContent variable. You may wish to simply upload the file into this variable rather than saving it locally
	fclose($fp);

echo '<br>Content of '.$filename.' :<br><hr />'.$fileContent.'<br><hr />'; //IOK for testing



//Do stuff to the XLIFF file
$updatedXliffFile = doStuffToXLIFF($fileContent);
echo '<br><br>Content of $updatedXliffFile: <br><hr />'.$updatedXliffFile.'<br><hr />'; //IOK for testing



//create a new file in c:\uploads\... with the name 'u_'.filename to mark it as updated
	$fp      = fopen($upload_path.'u_'.$filename, 'w'); //open this local c:\uploads\... file
	$fileContent = fwrite($fp, $updatedXliffFile); // write the updated XLIFF
	fclose($fp);


?>


