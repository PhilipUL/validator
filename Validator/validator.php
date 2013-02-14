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
    

static public function alphabeticOrder($data)
{			
        $dataArray = split("\n", $data);
        $size = sizeof($dataArray);
        $count = 0;
        $attsorderelements = array();
        $collections = array();
        $alphabeticOrderElements = array();
        for($m = 0; $m <= $size; $m++)
        {
            echo "<br>$dataArray[$m]";
        }
        echo "<br>size = $size";
        
        for($k = 0; $k <= $size; $k++)
        {
            try{
                    $st = split("\t", $dataArray[$k]); // split the current line by tab
                    $att = $st[0]; // take the first element 
                    
                    if (strpos($att,'@') !== false) // and check if it contain @ an attribute symbol
                    {
                       $count++; // note the presence of an attribute on the current line
                    }  else { // if we have reached the stage were we can't find any more attribute symbols, this marks the end of the nodes associated with a parent
                        if ($count >= 1) // if it turns out that there was at least one attribute
                        {
                            while($count != 0) // then we need to start going back over the last nodes storing the other attributes associated with that parent
                            {
                                $st = split("\t", $dataArray[$k-$count]);
                                $att = $st[0];
                                $count--;
                                array_push($attsorderelements, $att);
                            }
                            $collections = $attsorderelements;
                            sort($collections, SORT_STRING);
                            
                            $subCount = sizeof($attsorderelements);
                            $size = $subCount;
                            for($j = 0; $j < $subCount; $j++) ////////////////
                            {
                                while($size != 0)
                                {
                                    if(strpos($dataArray[$k - $size], $attsorderelements[$j]))
                                    {
                                       array_push($alphabeticOrderElements, $dataArray[$k - $size]);
                                    }
                                    $size--;
                                }
                            }
                        }
                        $count = 0;
                        array_push($alphabeticOrderElements, $dataArray[$k]);
                    }
               } catch (Exception $ex)
               {
                   if ($count >= 1)
                   {
                      $attsorderelements = array();
                      while($count != 0)
                      {
                          $st = split("\t", $dataArray[$k]);
                          $att = $st[0];
                          $count--;
                          array_push($attsorderelements, $att);
                      }
                      
                      sort($collections, SORT_STRING);
                      for($l = 0; $l < sizeof($attsorderelements); $l++)
                      {
                          $size = sizeof($attsorderelements);
                          while($size != 0)
                          {
                              if(strpos($dataArray[$k - $size], $attsorderelements[$l]))
                              {
                                  array_push($alphabeticOrderElements, $dataArray[$k - $size]);
                              }
                              $size--;
                          }
                      }
                   }
                   $count = 0;
               }
               
        }
        $alphabeticOrderElements = implode("\n", $alphabeticOrderElements);
        return $alphabeticOrderElements;
}

static public function clenseIts(&$data, &$datacat)
{	
    
    // split document by \n/
    $dataArray = split("\n/", $data);
    $result ="";
    $first = true; 
    $keyValueArray = array();
    $toBeMapped = array();
    foreach($dataArray as $line)
    {
        echo "<br>";
        if($first == true)
        {
            $first = false;
        }else{
            $line = "/".$line;
        }
        
        $temp = split("\t", $line);
        
        $result.=$temp[0];
        
        $atribs= array();
        for($i = 1; $i < sizeof($temp); $i++)
        {
            // check if the line your on is an attribute, and if it is check if the next line is meant to be an attribute, if not combine
            if(strpos($temp[$i], "=")===false)
            {
                continue;
            }else{
                $current = $temp[$i];
                
                for($x=$i;isset ($temp[$x+1])&& strpos($temp[$x+1], "=")===false;$x++){
                    $current.=$temp[$x+1];
                    echo " added lines together";
                }
                $atribs[]=str_replace("its:", "",$current);
                
            }
        }

        sort($atribs);
        print_r($atribs);
        $k = 0;
        // loop through the different atribues, e.g. domain mapping and domain pointer 
        for($i = 0; $i < sizeof($atribs); $i++)
        {
            // first get rid of new lines in attribs
            $atribs[$i] = trim(preg_replace('/\s+/', ' ', $atribs[$i]));
            $atribs[$i] = trim($atribs[$i]);
            // make sure the attribute ends with the correct number of quotes
            if(substr_count($atribs[$i], '"')%2 == 1)
            {
                $atribs[$i] = $atribs[$i].'"';
            }
            if(substr($atribs[$i], strlen($atribs[$i])-1) != '"')
            {
//                echo "attribute doesn't end in the thing";
//                echo $atribs[$i];
                
                $atribs[$i] = $atribs[$i].'"';
            }

            if($datacat == "translate")
            {   
                $result.="\t".strtolower($atribs[$i]);
            } 
            else if($datacat == "domain")
            {
                echo "in domain <br>";
                
                
                $pos = strpos($atribs[$i], "domainMapping");
                if($pos !== false)
                {
                    // split by ""
                    preg_match('/"([^"]+)"/', $atribs[$i], $quotes);
                    if(isset($quotes[1]))
                    {
                        // iterate through all the comma separteted elements in domainMapping
                        $commas = split(",", $quotes[1]);
                        foreach($commas as $comma)
                        {
                            trim($comma);
                            // if the comma element contains ''
                            preg_match("/'([^']+)'/", $comma, $singleQuotes);
                            if(sizeof($singleQuotes) < 1)
                            {
                                $mapKeyPair = split(" ", $comma);
                                
                                if(sizeof($mapKeyPair) == 2)
                                {
                                    // check if it's already in the array
                                    if(in_array($mapKeyPair[0], $keyValueArray) == false)
                                    {
                                        $keyValueArray[$mapKeyPair[0]] = $mapKeyPair[1];
                                    }
                                }
                            } elseif(sizeof($singleQuotes) == 2)
                            {
                                //echo "======= ".$comma."=======";
                                $mapKeyPair = split("'", $comma);
                                //print_r($mapKeyPair);
                                
                                // check if it's already in the array
                                if(in_array($mapKeyPair[1], $keyValueArray) == false)
                                {
                                    $keyValueArray[$mapKeyPair[1]] = trim($mapKeyPair[2]);
                                }
                                
                            }
                        }

                    }
                    print_r($keyValueArray);
                } 
                
                // if we're on the domainPointer attribute
                $pos = strpos($atribs[$i], "domainPointer");
                if($pos !== false)
                {
                    // split by ""
                    preg_match('/"([^"]+)"/', $atribs[$i], $quotes);
                    if(isset($quotes[1]))
                    {
                        $commas = split(",", $quotes[1]);
                        
                        foreach($commas as $comma)
                        {
                            trim($comma);
                            echo "======in here=======";
                            // if there is an APOSTROPHE at the start or end of the element, get rid of it
                            if(strcmp(substr($comma, 0, 1), "'") == true | strcmp(substr($comma, -0, 1), "'") == true)
                            {
                                unset($comma);
                            }
                        }
                        
                        echo "commas array = ";
                        print_r($commas);
                        $toBeMapped = $commas;
                        
                    }

                }
                
                echo "<br>array to be mapped: ";
                print_r($toBeMapped);
                
                for($i = 0; $i < sizeof($toBeMapped); $i++)
                {
                    if (array_key_exists($toBeMapped[$i], $keyValueArray) == true)
                    {
                        $result.="\t".strtolower("domains=".'"'.$keyValueArray[$toBeMapped[$i]].'"');
                    } else {
                        $result.="\t".strtolower("domains=".'"'.$toBeMapped[$i].'"');
                    }
                }
            }
            else if($datacat == "ruby")
            {
                $result.="\t".$atribs[$i].'"';
            }
            else if($datacat == "locnote")
            {
                //echo "entered locnote \n";
                $atribs[$i]=str_replace("its-loc-note", "locNote", $atribs[$i]);
                $atribs[$i]=str_replace("its-loc-note-type", "locNoteType", $atribs[$i]);
                
                $pair= split("=", $atribs[$i]);
                //print_r($pair);
                if(strcasecmp("locNote", $pair[0])==0){ 
                    //echo "loc-note atrib \n";
//                    $result.="\tlocNote={$pair[1]}";
                    $temp = ereg_replace("\n", "", $pair[1]);
                    $result.="\tlocNote=".$temp;
                    if(isset($atribs[$i+1]) == false)
                    {
                        $atribs[$i+1] = 'locNoteType="description"';
                    }
                }else if(strcasecmp("locNoteType", $pair[0])==0){
                    //echo "loc-note-type atrib \n";
                    $result.="\tlocNoteType=".strtolower($pair[1]);
                } else if(strcasecmp("locNotePointer", $pair[0])==0 )
                {
                    //echo "loc-note-pointer atrib \n";
                    // clean up attribute string
                    if(stripos($pair[1], '"')!== false)
                    {
                        $tempClean =  stripos($pair[1], '"');
                        $pair[1] = '"'.trim(substr($pair[1], $tempClean+1));
                        
                    }
                            
                    $result.="\tlocNote=".$pair[1];
                    if(isset($atribs[$i+1]) == false)
                    {
                        
                        $atribs[$i+1] = 'locNoteType="description"';
                    }
                }else if(strcasecmp("locNoteRef", $pair[0])==0 )
                {
                    //echo "loc-note-pointer atrib \n";
                    echo "pair 1:".$pair[1];
                            
                    $result.="\tlocNoteRef=".$pair[1];
                    if(isset($atribs[$i+1]) == false)
                    {
                        
                        $atribs[$i+1] = 'locNoteType="description"';
                    }
                }else if(strcasecmp("locNoteRefPointer", $pair[0])==0 )
                {
                    //echo "loc-note-pointer atrib \n";
                    echo "pair 1:".$pair[1];
                            
                    $result.="\tlocNoteRef=".$pair[1];
                }else{
                    $result.=$atribs[$i];
                }
                
                
            }   

        }
                // sort array alphabetically index 1 to end 
      $result.="\n";   

    }
    
   return $result;

}


// called by either solas.api or apache
static public function validate($data, $jobid, $dataCategory,$rules=null)
{
    $extension; 
    $resourceFilename="test";
    
    try {
                // Get the LocConnect URL from the .ini file
                $urlSettings = new Settings();
                $baseURL = $urlSettings->get('general.BASE_URL');
                // Get the filename associated with the ID using get_extension
                $request = new HTTP_Request2($baseURL."/get_extension.php?id=$jobid");
                $request->setMethod(HTTP_Request2::METHOD_GET);
                $response = $request->send();
                
                $requestFilename = new HTTP_Request2($baseURL."/get_resource_filename.php?id=$jobid&type=ITS");
                $requestFilename->setMethod(HTTP_Request2::METHOD_GET);
                $responseFilename = $requestFilename->send();
                
                if(200 == $responseFilename->getStatus())
                {
                    //trim the response for the rules file filename
                    $resourceFilename = $responseFilename->getBody();
                    $pos = strpos($resourceFilename, "<content>");
                    if($pos !== false)
                    {
                        $resourceFilename = substr_replace($resourceFilename, "", $pos, strlen("<content>"));
                    }

                    $pos = strrpos($resourceFilename, "</content>");
                    if($pos !== false)
                    {
                        $resourceFilename = substr_replace($resourceFilename, "", $pos, strlen("</content>"));
                    }
                    
                    
                }
                
                if (200 == $response->getStatus()) 
                {
                    // get the filename from the HTTP request by calling getBody(), then get rid of the content tags
                    $filename=$response->getBody();
                    $filename= str_replace('<content>', '', $filename);

                    $extension = substr(strrchr($filename,'.'),1);

                } 
                else 
                {
                   $res='Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                   $response->getReasonPhrase();
                   return $response;
                }

            } catch (HTTP_Request2_Exception $e) 
            {
               $res='Error: ' . $e->getMessage();
               return $res;
            }
    
        

        $itsSettings = new Settings();
        $ITS_Path = $itsSettings->get('general.ITS_PATH');
        
        //$dataCategories = 'translate';
        
        
        shell_exec("mkdir ".$ITS_Path."uploads/$jobid");
        shell_exec("chmod 777 -R ".$ITS_Path."uploads");
        $data=trim(preg_replace('/<\?xml version.*;?>/i', "", $data));
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
        $output = $data;
        
       
        shell_exec("cp ".$ITS_Path."tools/datacategories-2-xsl.xsl ".$ITS_Path."uploads/$jobid/");
        
        
       
        //file_put_contents($ITS_Path."uploads/$jobid/debugMessage1.xml", $extension);
        $errors;
        if(strcmp($extension, "html") == 0)
        {
            $config = array(
           'indent'         => true,
           'output-xhtml'   => true,
                
            );
            
            $tidy= new tidy();
            $data=$tidy->repairString($data, $config, 'utf8');
            //file_put_contents($ITS_Path."uploads/$jobid/debugMessage2.xml", "Data was tidied");
        } 
         // cannot have extension hard coded
        file_put_contents($ITS_Path."uploads/$jobid/inputfile.xml", $data);
        $tempDoc= simplexml_load_string($data);
        
//        $xlink=$tempDoc->xpath("*[@*:xlink=*]");
//        $xlink= $xlink->xlink;
        echo $ITS_Path."uploads/$jobid/$resourceFilename";
        file_put_contents($ITS_Path."uploads/$jobid/$resourceFilename", $rules);
        
        
        file_put_contents($ITS_Path."uploads/$jobid/errorsIntermediate.txt",shell_exec("java -jar ".$ITS_Path."lib/saxon9he.jar -o:".$ITS_Path."uploads/$jobid/intermediate.xsl ".$ITS_Path."tools/datacategories-definition.xml ".$ITS_Path."uploads/$jobid/datacategories-2-xsl.xsl inputDatacats=$dataCategory inputDocUri=".$ITS_Path."uploads/$jobid/inputfile.xml 2>&1"));
        echo "<br>java -jar ".$ITS_Path."lib/saxon9he.jar -o:".$ITS_Path."uploads/$jobid/intermediate.xsl ".$ITS_Path."tools/datacategories-definition.xml ".$ITS_Path."uploads/$jobid/datacategories-2-xsl.xsl inputDatacats=$dataCategory inputDocUri=".$ITS_Path."uploads/$jobid/inputfile.xml";
        //shell_exec("chmod 777 -R ".$ITS_Path."uploads/$jobid/");
        
        file_put_contents($ITS_Path."uploads/$jobid/errorsNodelistWithITSInfo.txt", shell_exec("java -jar ".$ITS_Path."lib/saxon9he.jar -o:".$ITS_Path."uploads/$jobid/nodelist-with-its-information.xml ".$ITS_Path."uploads/$jobid/inputfile.xml ".$ITS_Path."uploads/$jobid/intermediate.xsl 2>&1"));
        echo "<br> java -jar ".$ITS_Path."lib/saxon9he.jar -o:".$ITS_Path."uploads/$jobid/nodelist-with-its-information.xml ".$ITS_Path."uploads/$jobid/inputfile.xml ".$ITS_Path."uploads/$jobid/intermediate.xsl";
        
        shell_exec("chmod 777 -R ".$ITS_Path."uploads/$jobid/");
        shell_exec("java -jar ".$ITS_Path."lib/saxon9he.jar -o:".$ITS_Path."uploads/$jobid/output1.txt ".$ITS_Path."uploads/$jobid/nodelist-with-its-information.xml ".$ITS_Path."tools/tabdelimiting.xsl");
        
        echo "<br> java -jar ".$ITS_Path."lib/saxon9he.jar -o:".$ITS_Path."uploads/$jobid/output1.txt ".$ITS_Path."uploads/$jobid/nodelist-with-its-information.xml ".$ITS_Path."tools/tabdelimiting.xsl";
        shell_exec("chmod 777 -R ".$ITS_Path."uploads/$jobid/");
        
        
        $output = file_get_contents($ITS_Path."uploads/$jobid/output1.txt");
        $output = str_replace("text=\t","",str_replace("path=/","/", $output));
        $output = str_replace("\t\n","\n", str_replace("    ","\t", $output));
        
        $output=trim(str_replace("\n\n","\n", $output));
        $output=validator::clenseIts($output,$dataCategory);
        
        
        
        
        file_put_contents($ITS_Path."uploads/$jobid/output.txt", $output);
        //$response->code = 200;

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
