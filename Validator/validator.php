<?php

require_once file_exists('lib/tonic.php')? 'lib/tonic.php':"Validator/lib/tonic.php";
require_once file_exists('../settings.class.php')? '../settings.class.php':"settings.class.php";


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of validator
 *
 * @author philip
 * @uri v0/validator/{$jobid}/{$dataCategory}/
 */
class validator extends Resource {
//$version = 0;

// called by either solas.api or apache
static public function validate($data, $jobid, $dataCategory)
{
    
//    $versionImport =null;
//	if(isset($_GET['version']))	$versionImport =$_GET['version'];
//
//
//	if ($versionImport == Null || trim($versionImport) == '') // if $versionImport is null
//	{
//		$version = 1.2;
//	} else if ($versionImport != 2.0)
//	{
//		$version = 1.2;
//	}
        $itsSettings = new Settings();
        $ITS_Path = $itsSettings->get('general.ITS_PATH');
        
        //$dataCategories = 'translate';
        
        
        shell_exec("mkdir ".$ITS_Path."uploads/$jobid");
        shell_exec("chmod 777 -R ".$ITS_Path."uploads");
        $data=trim(preg_replace('/<\?xml version.*;?>/i', "", $data));
        if(strpos($data, "<content>")){
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
        }
        $output = $data;
        
////       
        shell_exec("cp ".$ITS_Path."tools/datacategories-2-xsl.xsl ".$ITS_Path."uploads/$jobid/");
        file_put_contents($ITS_Path."uploads/$jobid/inputfile.xml", $data);
//        
        shell_exec("java -jar ".$ITS_Path."lib/saxon9he.jar -o:".$ITS_Path."uploads/$jobid/intermediate.xsl ".$ITS_Path."tools/$dataCategory/datacategories-definition.xml ".$ITS_Path."uploads/$jobid/datacategories-2-xsl.xsl inputDatacats=$dataCategory inputDocUri=".$ITS_Path."uploads/$jobid/inputfile.xml");
        
        shell_exec("java -jar ".$ITS_Path."lib/saxon9he.jar -o:".$ITS_Path."uploads/$jobid/nodelist-with-its-information.xml ".$ITS_Path."uploads/$jobid/inputfile.xml ".$ITS_Path."uploads/$jobid/intermediate.xsl");
        
        shell_exec("java -jar ".$ITS_Path."lib/saxon9he.jar -o:".$ITS_Path."uploads/$jobid/output.txt ".$ITS_Path."uploads/$jobid/nodelist-with-its-information.xml ".$ITS_Path."tools/tabdelimiting.xsl");
        
        
        
        $output = file_get_contents($ITS_Path."uploads/$jobid/output.txt");
        $output = str_replace("text=\t","",str_replace("path=/","/", $output));
        $output=trim(str_replace("\t\n","\n", str_replace("    ","\t", $output)));
        
        
        $output=$output."\n";
        file_put_contents($ITS_Path."uploads/$jobid/output.txt", $output);
        $response->code = 200;
        return $output;
        
        
                        
                        
}

// called by apache, which is called by a user calling apache through it's url
public function post($request, $jobid, $dataCategory)
{			
        // done!
        $response = new Response($request);
        $response->addHeader('Content-type', 'text/xml; charset=utf-8');
        $code = Response::OK;
        $tabDelimitedOutput = validator::validate($request->data, $jobid, $dataCategory);
        // must format the output using the tabDelimitedOuput
        $finalOutput = $tabDelimitedOutput;
        $response->body = $finalOutput;
        $response->code = $code;
        
    
        return $response;
}
 
}

?>