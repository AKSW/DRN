<?
/***
 * main class to start conversion Bibtex in Bibo
 * 
 * **/
include ('PARSEENTRIES.php');
class convertToBibo extends PARSEENTRIES{
	
	//finds vakue of a specific key
	 function search_nested_arrays($array, $key){
    if(is_object($array))
        $array = (array)$array;
   
    // search for the key
    $result = array();
    foreach ($array as $k => $value) {
        if(is_array($value) || is_object($value)){
            $r = $this->search_nested_arrays($value, $key);
            if(!is_null($r))
                array_push($result,$r);
        }
    }
   
    if(array_key_exists($key, $array))
        array_push($result,$array[$key]);
   
   
    if(count($result) > 0){
        // resolve nested arrays
        $result_plain = array();
        foreach ($result as $k => $value) {
            if(is_array($value))
                $result_plain = array_merge($result_plain,$value);
            else
                array_push($result_plain,$value);
        }
        return $result_plain;
    }
    return NULL;
}
	
	//extract words betwenn "ands"
	function splitAnd ($arr){
		
		$arrayName = explode(' and ',$arr);
		
		return $arrayName;
		}
		
		  	
	// Parse an input Bibtex file and change it to Bibo
	
	function parsingBibtex() 
	{
	
	$parse = new PARSEENTRIES ();
	$parse->expandMacro = TRUE;
	$parse->openBib("Test.bib");
	$parse->extractEntries();
	$parse->closeBib();
	$separate[]=$parse->returnArrays();
	$all_index = count($separate[0][2]);
	$editor_names = $this->search_nested_arrays($separate,"editor");
	$count_editor_name= count($this->splitAnd($editor_names[0]));
	
	
    for ($c=0;$c< $all_index; $c++) {
    $keys = array_keys($separate[0][2][$c]);
	
     //$first = (!empty($keys) ? $keys[0] : null); // erster Schluessel, NULL falls Array leer ist
    //$last = (!empty($keys) ? $keys[count($keys)-1] : null); // letzter Schluessel, NULL falls Array leer ist
   // reset($separate[0][2][0]); // Zeiger auf den Anfang setzen
   // $first = key($separate[0][2][$c]);
   // end($separate[0][2][0]); // Zeiger auf das Ende setzen
   // $last = key($separate[0][2][$c]);
  //  $keyCount = count($keys)-1;
  
 
	$counter = 0;
	$author_collection = null;
	// extract information for each EntryType
	foreach ($separate[0][2][$c] as $key=>$value) {
		
		 if ($counter == 0) {
			 $tmp_key = $key;
			 $tmp_value = $value;
			$counter++;
		 }
		 else if ($counter==1) {
			$str = "<http://aksw.org/".$value.">"." ".$parse->mapping_term_follow_doctype($tmp_key,$tmp_value).";"."\n";
			$counter++;
		 } else {
					if ($parse->mapping_term($key)!="") {
						// FILTER editor value
						if($key == "pages"){
							//foreach($value as $page){
								list($startPage,$endPage)=explode("-",$value);
								$str.="bibo:pageStart \"".$startPage."\";\n"."bibo:pageEnd \"".$endPage.";"."\n";
								//}
							}

						if( $key == "editor"){
							foreach($this->splitAnd($value) as $editor_name){
								
							 $str .= "bibo:$key "."$key:".str_replace(" ","_",$editor_name).";"."\n";
							 list($Name,$Surname) = explode(" ", $editor_name);
						     $author_collection .= "$key:".str_replace(" ","_",$editor_name)." foaf:Name \"".$Name."\";\n"."foaf:Surname \"".$Surname."\";"."\n"."foaf:Fullname \"".$editor_name."\"."."\n";
										
									}
									
							}
							if($key=="author" ){
								foreach($this->splitAnd($value) as $author_name){
							 $str .= "dcterms:creator "."$key:".str_replace(" ","_",$author_name).";"."\n";
							 list($Name,$Surname) = explode(" ", $author_name);
						     $author_collection .= "$key:".str_replace(" ","_",$author_name)." foaf:Name \"".$Name."\";\n"."foaf:Surname \"".$Surname."\";"."\n"."foaf:Fullname \"".$author_name."\"."."\n";
								
								}
						}
						    
						// 
						if ($counter < count($separate[0][2][$c])-1 && $key != "editor" && $key!="author" && $key!="pages")
							{ 
								$str .= $parse->mapping_term($key)." ".'"'.$value.'"'.";"."\n";
								$counter++;
							 }
					}

				}
	//print_r($preamble);//uncomment
	//print "\n";
//	print_r($strings);//uncomment
	//print "\n";
	}
	// END: extract information for each EntryType
	$results [] = substr_replace($str,".",-2)."\n".$author_collection."\n\n";
}
	
	//loop
	return $results;
}
}
	$header = '<?xml version=version="1.0"?>
<rdf:RDF xmlns:foaf="http://xmlns.com/foaf/0.1/"
xmlns:dcterms="http://purl.org/dc/terms/"
xmlns:dc="http://purl.org/dc/elements/1.1/"
xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
xmlns:bibo="http://purl.org/ontology/bibo/"
xmlns:bibo_iris="http://purl.org/net/unis/iris/"
xmlns:bibo_place="http://purl.org/net/c4dm/event.owl#place/"
xmlns:author="http://akws.org/author/"\>';

   $go = new convertToBibo();
  // print_r($go->parsingBibtex());
   $str_outputs = $go->parsingBibtex();
   echo $header."\n";
   foreach ($str_outputs as $str_output) {
			//$output = substr_replace($str_output,".",-2);
			echo $str_output."\n";
   }

