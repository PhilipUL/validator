README.txt
----------

Creation Date: 23 March 2012
Author: Ian O'Keeffe

Modification History:
--------------------------
23-03-2012
Creation

This template/collection is to be used in creating components to interact with the locConnect server in the Solas architecture.

Contents:
---------
index.html
pollingSolas.html
manualStandalone.html
solas_api.php
SolasAPI.class.php
upload.php
doStuffToXLIFF_file.php


Structure:
----------

index.html______________
|						|
pollingSolas.html		manualStandalone.html
|						|
solas_api.php			upload.php
|	|					|
|	SolasAPI.class.php	|
|						|
|						|
doStuffToXLIFF_file.php


must change permission of the uploads folder or change the owner

index.html
--------------------------
This is the top-level index.html entry point for creating a Solas Component.
It permits branching to either a LocConnect-controlled workflow (pollingSolas.html)
or a standalone instantiation (manualStandalone.html) which processes a file directly via an embedded form

pollingSolas.html
--------------------------
This page controls calling LocConnect to see if a job is available, and if so, to process it.
pollingSolas.html -> solas_api.php (API to the Project Management User Interface of LocConnect)
It polls LocConnect every second

manualStandalone.html
--------------------------
This page controls a standalone instantiation of a component:
manualStandalone.html -> upload.php (uploads required files manually rather than via HTTP messaging)
This processes a file directly via an embedded form

solas_api.php
--------------------------
This page is a template for creating new components that interact with locConnect via PEAR and the locConnect API.
LocConnect uses Pear for its calls, so you need to include it: require_once 'HTTP\Request2.php';
If Request2.php is not found, and you get:
Warning: require_once(HTTP/Request2.php) [function.require-once]: failed to open stream: No such file or directory in E:\www\ct\1.php on line 2
Fatal error: require_once() [function.require]: Failed opening required 'HTTP/Request2.php' (include_path='.;C:\php5\pear') in E:\www\ct\1.php on line 2
you should type:
  pear install http_request2
at the # prompt.

solas_api.php performs the following:
* Sets IP address for LocConnect
* Sets Component Name
* First step, call LocConnect to fetch a list of available jobs for this component, ComponentName
		$jobs = $solasApi->solas_fetch_jobs($componentName, $locConnect);
* Parse returned XML/XLIFF list for status, and possible jobs
* Any error messages?
* Any jobs?
* If yes, Tell locConnect I will process this jobId
		$response = $solasApi->solas_set_status_processing($componentName, $jobId, $locConnect);
* 	Get job
		$file = $solasApi->solas_get_job($componentName, $jobId, $locConnect);
* 	Do stuff to the XLIFF file
* 	Send the updated XLIFF file back to locConnect
		$response = $solasApi->solas_send_output($componentName, $jobId, $data, $locConnect);
* 	Say what you did to the XLIFF file
		$response = $solasApi->solas_send_feedback($componentName, $jobId, 'did stuff to XLIFF file', $locConnect);
* If no, jobId is not set, so wait until called again

SolasAPI.class.php
--------------------------
This is a locConnect Solas API Class
It wraps the API calls to make component creation easier

upload.php
--------------------------
This code is written for MS Windows (see paths defined below)
It uploads a file for processing manually, rather than using locConnect, for testing purposes

doStuffToXLIFF_file.php
--------------------------
This code 'does stuff' to the received XLIFF file. It is quite basic. 
It simply loads the file into a DOMDocument object, echoes the source and target lang tags if found, and re-packages the DOMDocument to XML before returning it
Feel free to add your real processing here


--------------------------
