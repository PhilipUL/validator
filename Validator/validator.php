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

static public function clenseIts($data)
{			
        $dataArray = split("\n", $data);
        $result ="";
        foreach($dataArray as $line){
            $temp = split("\t",$line);
            $result.=$temp[0];
            if(sizeof($temp)==2){
                $result.="\t".str_replace("its:", "",$temp[1]);
            }
            $result.="\n";
        }
        
        return $result;
}


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
        
        
        $output=validator::clenseIts($output."\n");
        file_put_contents($ITS_Path."uploads/$jobid/output.txt", $output);
        $response->code = 200;
        //$output = validator::alphabeticOrder($output);
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
