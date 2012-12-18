<?php
/*
Creation Date: 21 March 2012
Author: Ian O'Keeffe

Modification History:
--------------------------
21-03-2012
This code 'does stuff' to the received XLIFF file. It is quite basic. 
It simply loads the file into a DOMDocument object, echoes the source and target lang tags if found, and re-packages the DOMDocument to XML before returning it
Feel free to add your real processing here
*/

function doStuffToXLIFF($xliffFile){

//Open XLIFF file using DOMDocument to access the tag information
$xliffDom = new DOMDocument(); 		//To handle XML format files (such as XLIFF!)
$xliffDom->loadXML($xliffFile);		//Open $xliffFile as a DOMDocument XML file object





//Example tag processing: Capture source and target languages
$xliffFiles = $xliffDom->getElementsByTagName('file');
foreach ($xliffFiles as $xliffFile) {
	$sourceLang = $xliffFile->getAttribute('source-language');
	$targetLang = $xliffFile->getAttribute('target-language');
}
echo 'source: '.$sourceLang.'<br>';
echo 'target: '.$targetLang;







//Save amended XLIFF DOMDocument object to file
$updatedXliffFile = $xliffDom->saveXML(); // Save amended XLIFF DOMDocument object to file 					
					
return $updatedXliffFile;
}


/*
Example XLIFF file

<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xliff PUBLIC "-//XLIFF//DTD XLIFF//EN"
"http://www.oasis-open.org/committees/xliff/documents/xliff.dtd" >
<xliff version="1.0" xml:lang="en">
<file datatype="plaintext" original="MultiLingualContent.fla" source-language="en-IE" target-language="fr-FR">
		<header></header>
		<body>
			<trans-unit id="001" resname="IDS_GREETINGS">
				<source xml:lang="en-US">Welcome to our web site!</source>
				<target xml:lang="fr-FR">Bienvenue sur notre site web!</target>
			</trans-unit>
			<trans-unit id="002" resname="IDS_TEST">
				<source></source>
			</trans-unit>
		</body>
	</file>
</xliff>
*/
?>