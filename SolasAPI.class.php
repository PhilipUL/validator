<?php
/*
Creation Date: 21 March 2012
Author: Ian O'Keeffe

Modification History:
--------------------------
21-03-2012
This is a locConnect Solas API Class
It wraps the API calls to make component creation easier

*/
class SolasAPI {

	//locConnect Solas API public functions

	//***********************************************************************************************************
	//Calls LocConnect to fetch a list of available jobs for a given ComponentName
	public function solas_fetch_jobs($ComponentName, $locConnect){
		$request = new HTTP_Request2($locConnect.'fetch_job.php',HTTP_Request2::METHOD_GET);

		$request->setHeader('Accept-Charset', 'utf-8');
		$url = $request->getUrl();

		// set your component name here
		$url->setQueryVariable('com', $ComponentName); // com = component ComponentName = LCM, MT, LKR, WFR etc... should be uppercase ASCII string (max 6 chars)

		// This will get a list of pending jobs from the CNLF server and store them $jobs variable;
		$response=$request->send()->getBody();

		return $response;
	}

	//***********************************************************************************************************
	//Calls LocConnect to set the status of a job via JobId and ComponentName
	public function solas_set_status($ComponentName, $jobId, $status, $locConnect){
		$request = new HTTP_Request2($locConnect.'set_status.php',HTTP_Request2::METHOD_GET);
		
		$request->setHeader('Accept-Charset', 'utf-8');
		$url = $request->getUrl();
		
		// set request variables here
		$url->setQueryVariable('com', $ComponentName); 	// com = component. ComponentName = LCM, MT, LKR, WFR etc... should be uppercase ASCII string (max 6 chars)
		$url->setQueryVariable('id', $jobId); 			// set jobId here
		$url->setQueryVariable('msg', $status); 		// set message to $status (processing, complete or pending)
		
		// This will get a list of pending jobs from the CNLF server and store them $jobs variable;
		$response=$request->send()->getBody();

		return $response;
	}

	//***********************************************************************************************************
	//Calls LocConnect to set the status of a job directly to 'processing' via JobId and ComponentName
	public function solas_set_status_processing($ComponentName, $jobId, $locConnect){
		$response = $this->solas_set_status($ComponentName, $jobId, 'processing', $locConnect); //call solas_set_status() within this class, and pass the required status
		return $response;
	}

	//***********************************************************************************************************
	//Calls LocConnect to set the status of a job directly to 'complete' via JobId and ComponentName
	public function solas_set_status_complete($ComponentName, $jobId, $locConnect){
		$response = $this->solas_set_status($ComponentName, $jobId, 'complete', $locConnect); //call solas_set_status() within this class, and pass the required status
		return $response;
	}

	//***********************************************************************************************************
	//Calls LocConnect to set the status of a job directly to 'pending' via JobId and ComponentName
	public function solas_set_status_pending($ComponentName, $jobId, $locConnect){
		$response = $this->solas_set_status($ComponentName, $jobId, 'pending', $locConnect); //call solas_set_status() within this class, and pass the required status
		return $response;
	}

	
	//***********************************************************************************************************
	//Calls LocConnect to request a file to be processed via JobId and ComponentName
	public function solas_get_job($ComponentName, $jobId, $locConnect){
		$request = new HTTP_Request2($locConnect.'get_job.php', HTTP_Request2::METHOD_GET);

		$request->setHeader('Accept-Charset', 'utf-8');
		$url = $request->getUrl();

		// set request variables here
		$url->setQueryVariable('com', $ComponentName); // com = component. ComponentName = LCM, MT, LKR, WFR etc... should be uppercase ASCII string (max 6 chars)
		$url->setQueryVariable('id', $jobId); 	// set jobId here
		
		// This will fetch the given job from the CNLF server and store content in $file variable;
		$response=$request->send()->getBody();
		return $response;
	}

	//***********************************************************************************************************
	//Calls LocConnect to send feedback msg relating to the current JobId and ComponentName
	public function solas_send_feedback($ComponentName, $jobId, $msg, $locConnect){
		$request = new HTTP_Request2($locConnect.'send_feedback.php', HTTP_Request2::METHOD_GET);

		$request->setHeader('Accept-Charset', 'utf-8');
		$url = $request->getUrl();

		// set request variables here
		$url->setQueryVariable('com', $ComponentName); // com = component. ComponentName = LCM, MT, LKR, WFR etc... should be uppercase ASCII string (max 6 chars)
		$url->setQueryVariable('id', $jobId); 	// set jobId here
		$url->setQueryVariable('msg', $msg); 	// set message text here, not more than 250 chars
		
		// This will fetch the given job from the CNLF server and store content in $file variable;
		$response=$request->send()->getBody();
		return $response;
	}

	//***********************************************************************************************************
	//Calls LocConnect to send output data back to locConnect using current JobId and ComponentName
	public function solas_send_output($ComponentName, $jobId, $data, $locConnect){
		$request = new HTTP_Request2($locConnect.'send_output.php');
		
		$request->setMethod(HTTP_Request2::METHOD_POST)
			->addPostParameter('id', $jobId)
			->addPostParameter('com', $ComponentName)
			->addPostParameter('data', $data);

		try {
			$response = $request->send();
			if (200 == $response->getStatus()) {
				$response = $response->getBody();
			} else {
				$response = 'Unexpected HTTP status: '.$response->getStatus().' '.$response->getReasonPhrase();
			}
		} catch (HTTP_Request2_Exception $e) {
			$response = 'Error: '.$e->getMessage();
		}
		return $response;	
	}		

	//***********************************************************************************************************
	private function imPrivate() { //IOK Just for my notes
		return "Hello World";
	}
}
