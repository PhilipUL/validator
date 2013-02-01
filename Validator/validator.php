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
//         $result=$data;
//        $dataArray = split("\n/", $data);
//        $result ="";
//        foreach($dataArray as $line){
//            $line="/".$line;
//            //echo $line;
//            // split the line by tab, as this should split by attributes
//            
//            $temp1 = split("\t",$line);
//            unset($line);
//            $temp = array();
//            for($i = 0; $i < sizeof($temp1); $i++)
//                {
//                if($i=0)$temp[]=&$temp1[$i];
//                else{
//                    $temp[]=&$temp1[$i];
//                     if(strpos($temp[$i], "=")===false)  continue;
//                     if(isset ($temp[$i+1])&& strpos($temp[$i+1], "=")===false) $temp[$i].=$temp[$i+1];
//                }
//           }
//           unset($temp1);
//           
//            $result.=$temp[0];
//            unset ($temp[0]);
//            if(sizeof($temp)>=2){
//                
//                for($i = 1; $i < sizeof($temp); $i++)
//                {
//                    
//                    if($datacat == "translate")
//                    {
//                        $temp[$i] ="\t".strtolower(str_replace("its:", "",$temp[$i]));
//                    } else if($datacat == "locnote")
//                    {
//                       
//                        $pair= split("=", str_replace("its:", "",$temp[$i]));
//                      
//                        if(strcasecmp("its-loc-note", $pair[0])==0){ 
//                            $temp[$i]="locNote={$pair[1]}";
//                        }else if(strcasecmp("its-loc-note-type", $pair[0])==0){
//                            $temp[$i]="locNoteType=".strtolower($pair[1]);
//                        }
//                    }   
//
//                }
//                // sort array alphabetically index 1 to end 
//                asort($temp);
//                echo "<br>";
//                foreach($temp as $str)
//                {
//                    $result.= "\t".$str;
//                }
//            }
//            $result.="\n";
//            
//            
//        }
//        
//        //if(strpos($result,"<!DOCTYPE"."html>");  
 
        
       
    $result=$data;   
    
    // split document by \n/
    $dataArray = split("\n/", $data);
    $result ="";
    $first = true; 
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
                }
                $atribs[]=str_replace("its:", "",$current);
            }
        }
        sort($atribs);
        print_r($atribs);
        for($i = 0; $i < sizeof($atribs); $i++)
        {

            if($datacat == "translate")
            {
                $result.="\t".strtolower($atribs[$i]);
            } else if($datacat == "locnote")
            {
                echo "entered locnote \n";
                $atribs[$i]=str_replace("its-loc-note", "locNote", $atribs[$i]);
                $atribs[$i]=str_replace("its-loc-note-type", "locNoteType", $atribs[$i]);
                $pair= split("=", $atribs[$i]);
                if(strcasecmp("locNote", $pair[0])==0){ 
                    echo "loc-note atrib \n";
                    $result.="\tlocNote={$pair[1]}";
                }else if(strcasecmp("locNoteType", $pair[0])==0){
                    echo "loc-note-type atrib \n";
                    $result.="\tlocNoteType=".strtolower($pair[1]);
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
                
                $resourceFilename = file_get_contents($baseURL."get_resource_filename.php?id=$jobid&type=ITS");
                
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
        
        
        shell_exec("java -jar ".$ITS_Path."lib/saxon9he.jar -o:".$ITS_Path."uploads/$jobid/intermediate.xsl ".$ITS_Path."tools/datacategories-definition.xml ".$ITS_Path."uploads/$jobid/datacategories-2-xsl.xsl inputDatacats=$dataCategory inputDocUri=".$ITS_Path."uploads/$jobid/inputfile.xml");
        echo "<br>java -jar ".$ITS_Path."lib/saxon9he.jar -o:".$ITS_Path."uploads/$jobid/intermediate.xsl ".$ITS_Path."tools/datacategories-definition.xml ".$ITS_Path."uploads/$jobid/datacategories-2-xsl.xsl inputDatacats=$dataCategory inputDocUri=".$ITS_Path."uploads/$jobid/inputfile.xml";
        //shell_exec("chmod 777 -R ".$ITS_Path."uploads/$jobid/");
        
        shell_exec("java -jar ".$ITS_Path."lib/saxon9he.jar -o:".$ITS_Path."uploads/$jobid/nodelist-with-its-information.xml ".$ITS_Path."uploads/$jobid/inputfile.xml ".$ITS_Path."uploads/$jobid/intermediate.xsl");
        echo "<br> java -jar ".$ITS_Path."lib/saxon9he.jar -o:".$ITS_Path."uploads/$jobid/nodelist-with-its-information.xml ".$ITS_Path."uploads/$jobid/inputfile.xml ".$ITS_Path."uploads/$jobid/intermediate.xsl";
        
        shell_exec("chmod 777 -R ".$ITS_Path."uploads/$jobid/");
        shell_exec("java -jar ".$ITS_Path."lib/saxon9he.jar -o:".$ITS_Path."uploads/$jobid/output.txt ".$ITS_Path."uploads/$jobid/nodelist-with-its-information.xml ".$ITS_Path."tools/tabdelimiting.xsl");
        
        echo "<br> java -jar ".$ITS_Path."lib/saxon9he.jar -o:".$ITS_Path."uploads/$jobid/output.txt ".$ITS_Path."uploads/$jobid/nodelist-with-its-information.xml ".$ITS_Path."tools/tabdelimiting.xsl";
        shell_exec("chmod 777 -R ".$ITS_Path."uploads/$jobid/");
        
        
        $output = file_get_contents($ITS_Path."uploads/$jobid/output.txt");
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
