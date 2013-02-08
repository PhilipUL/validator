<!--<!DOCTYPE html>-->
<html>
<head>
<title>Solas API Template Page</title>
<!--
Creation Date: 21 February 2012
Author: Ian O'Keeffe

Modification History:
--------------------------
21-02-2012
This page is a template for creating new components that interact with locConnect via PEAR and the locConnect API
It performs the following:
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
* 
* If no, jobId is not set, so wait until called again
* 
--------------------------

-->

</head>
<body>
<br>
<b>Test API page is being called at:  </b>


<?php
echo strftime('%c');
echo '<br>';
  
    //If Request2.php is not found, and you get:
    // Warning: require_once(HTTP/Request2.php) [function.require-once]: failed to open stream: No such file or directory in E:\www\ct\1.php on line 2
    // Fatal error: require_once() [function.require]: Failed opening required 'HTTP/Request2.php' (include_path='.;C:\php5\pear') in E:\www\ct\1.php on line 2
    //you should type:
    // pear install http_request2
    //at the # prompt

    
 
// Set IP address for LocConnect (better if this is all in a config.ini file)

// Set Component Name here
require_once 'HTTP/Request2.php';
require_once 'SolasAPI.class.php';
require_once 'doStuffToXLIFF_file.php';
require_once 'settings.class.php';
require_once 'Validator/validator.php';

function  processJobs ($componentName){
    $urlSettings = new Settings();
    $locConnect= $urlSettings->get('general.BASE_URL');
    // LocConnect uses Pear for its calls
  

    $solasApi = new SolasAPI; // set up SolasAPI class for function calling

    //First step, call LocConnect to fetch a list of available jobs for this component, ComponentName
    $jobs = $solasApi->solas_fetch_jobs($componentName, $locConnect);
    echo '<br> $jobs: '.$jobs; //IOK for testing


    //Now parse returned XML/XLIFF list for status, and possible jobs
    $doc = new DOMDocument();
    $doc->loadXML($jobs);
    $xpath = new DOMXPath($doc);


    //Any error messages?
    $solas_errors = $doc->getElementsByTagName("error");
    foreach ($solas_errors as $solas_error) {
            echo '<br> Tag: '.$solas_error->nodeName.' - '.$solas_error->nodeValue; //IOK for testing
            $solas_msgs = $solas_error->getElementsByTagName("msg");
            foreach($solas_msgs as $solas_msg) {
                    echo '<br> Tag: '.$solas_msg->nodeName.' - '.$solas_msg->nodeValue; //IOK for testing
            }
    }

    //Any jobs?
    $solas_alljobs = $doc->getElementsByTagName("jobs");
    foreach ($solas_alljobs as $solas_alljob) {
            echo '<br> Tag: '.$solas_alljob->nodeName.' - '.$solas_alljob->nodeValue; //IOK for testing
            $solas_jobs = $solas_alljob->getElementsByTagName("job");

            foreach($solas_jobs as $solas_job) { //list all jobs - not strictly necessary!
                    echo '<br> Tag: '.$solas_job->nodeName.' - '.$solas_job->nodeValue; //IOK for testing
            }
            
            
            $jobId = $solas_jobs->item(0)->nodeValue; // select the jobId of the first job in the list (may be the only job in the list!)
            echo '<br> Job ID to be processed: '.$jobId; //IOK for testing


            //Tell locConnect I will process this jobId
            $response = $solasApi->solas_set_status_processing($componentName, $jobId, $locConnect);
            echo '<br> solas_set_status_processing Response: '.$response; //IOK for testing
            //may need to insert error handling here in case solas_set_status is unsuccessful

    }


    
    //Get job if jobId is set
    if(isset($jobId)) {
            $file = $solasApi->solas_get_job($componentName, $jobId, $locConnect); // 

            $rulesFile = $solasApi->solas_get_resource($jobId, $locConnect, "ITS");
            $data=trim(preg_replace('/<\?xml version.*;?>/i', "", $rulesFile));
            $pos = strpos($data, "<content>");
            if($pos !== false)
            {
                $data = substr_replace($data, "", $pos, strlen("<content>"));
            }

            $pos = strrpos($data, "</content>");
            if($pos !== false)
            {
                $data = substr_replace($data, "", $pos, strlen("</content>"));
            }
            
            
            //Do stuff to the XLIFF file
            //$updatedXliffFile = Extractor::doStuffToXLIFF($file, $jobId, $componentName);

            // have to validate against all the datacategories  
            
            
            $tabDelimitedOutput = validator::validate($file, $jobId, "domain",$data);                
            echo '<br> $tabDelimitedOutput: '.$tabDelimitedOutput; //IOK for testing	



            //Send the updated XLIFF file back to locConnect
            $response = $solasApi->solas_send_output($componentName, $jobId, $tabDelimitedOutput, $locConnect);
            echo '<br> solas_send_output Response: '.$response; //IOK for testing	


            //Say what you did to the XLIFF file
            $response = $solasApi->solas_send_feedback($componentName, $jobId, 'did stuff to XLIFF file', $locConnect);
            echo '<br> solas_send_feedback Response: '.$response; //IOK for testing
            
            $response = $solasApi->solas_set_status_complete($componentName, $jobId, $locConnect);
            echo '<br> solas_set_status_complete: '.$response; //IOK for testing

    } else { //jobId is not set, so wait until called again
            echo '<br> Waiting for a job for '.$componentName; //IOK for testing
    }

}

processJobs("ITS");

?>



</body>
</html>