<?
/***
 * main class to start conversion Bibtex in Bibo
 * 
 * **/
include ('PARSEENTRIES.php');
class convertToBibo extends PARSEENTRIES{
	
	//extract words between "ands"
	function splitAnd ($arr){
		$arrayName=array();
		 if (preg_match("/and/",$arr)){
			 	if (strpos($arr,"{\"o}" )===true) {$deu="{\"o}"; $replace='oe';} 
			 	else if( strpos($arr,"{\"u}")===true){ $deu="{\"u}";$replace='ue';} else $deu=$replace="";
		$arrayName = explode(' and ', str_replace($deu,$replace,str_replace(".","",str_replace(",","",(ltrim($arr))))));
	}
		//else if(preg_match("/,/",$arr)){
		//$arrayName = explode(',',ltrim($arr));
		//}
		else $arrayName[0] = str_replace(".","",str_replace(",","",(ltrim($arr))));
		
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
	//$editor_names = $this->search_nested_arrays($separate,"editor");
	//$author_names = $this->search_nested_arrays($separate,"author");
	//$count_editor_name= count($this->splitAnd($editor_names[0]));
	//$count_author_name = count($this->splitAnd($author_names[0]));
	$inproceedingsFlag="";
	$deu="";
	$replace="";
    for ($c=0;$c< $all_index; $c++) {
   // $keys = array_keys($separate[0][2][$c]);
   
	$counter = 0;
	$author_collection = null;
    $booktitle="";
    
	  
			foreach ($separate[0][2][$c] as $key=>$value){
				if($key=="bibtexEntryType" && ($value=="inproceedings" ||$value =="incollection")) 
					{
					
					$inproceedingsFlag = $value;
					while (list($key, $value) = each($separate[0][2][$c])) {
						if($key=="booktitle"){		
							$booktitle= $value ;
						    	}
							}
							$inproceedingsFlag="";
		             }
		          }
		          
	   foreach ($separate[0][2][$c] as $key=>$value) {
		
					if ($counter == 0) {
					 $tmp_key = $key;
					 $tmp_value = $value;
					 $counter++;
			
		 }
			 else if ($counter==1) { 
				       							 
							$str = "<http://aksw.org/".$value."_".$tmp_value.">"." ".$parse->mapping_term_follow_doctype($tmp_key,$tmp_value).";"."\n";
							$counter++;
			 } 
			 			
		 else{   
					if ($parse->mapping_term($key)!="") {
						
						if($key=="booktitle"){
							//$str.="<http://".str_replace(" ","_",ltrim($booktitle)).">;"."\n";
							// $author_collection .="<http://".str_replace(" ","_",ltrim($booktitle))." a bibo:Book;\n"
							$author_collection .="_:bnod_booktitle"." a bibo:Book;\n"
							 ."dcterms:title \"".($booktitle)."\".\n";
							  }
							
						if($key == "pages"){
							   							                                 
								$value = str_replace("--","-",$value);
								$value = str_replace("-–","-",$value);
								$value = str_replace("–-","-",$value);
								$value = str_replace("–","-",$value);$value = str_replace(" - ","-",$value);
								list($startPage,$endPage)=explode("-",$value);
								$str.="bibo:pageStart \"".$startPage."\";\n"."bibo:pageEnd \"".$endPage."\";\n";
								
							}
						// FILTER editor value
						if( $key == "editor"){ 
														
							foreach($this->splitAnd($value) as $editor_name){ 
								$deu = "{\\\"o}";						
						   	 $cleand_editor = str_replace($deu,'oe',str_replace('_et_al',"",(str_replace('.',"",(str_replace(" ","_",ltrim($editor_name)))))));
							 $str .= "bibo:$key "."$key:".$cleand_editor.";"."\n";
						    list($Name,$Surname) = explode(" ", ltrim($editor_name));
						     $author_collection .= "$key:".$cleand_editor." foaf:Name \"".str_replace('.',"",$Name)."\";\n"."foaf:Surname \"".$Surname."\";"
						     ."\n"."foaf:Fullname \"".$cleand_editor."\"."."\n";
										
									} 
									
															
							}
							if($key=="author" ){
								foreach($this->splitAnd($value) as $author_name){ //to do: finding german equ for Ö Ü Ä , change { in some names
								//	if (strpos($author_name,"{\\\"o}" )===false) {
										
									//$deu="{\\\"o}"; $replace='oe'; 
									$author_name=str_replace("{\\\"o}",'oe',$author_name);
									
							//		if($deu == "{\"o}") $replace='oe'; else if($deu=="{\"u}") $replace='ue' ;
							
							 $cleand_author =str_replace("{","",str_replace('_et_al',"",(str_replace('.',"",(str_replace(" ","_",ltrim($author_name)))))));
							 $str .= "dc:creator "."$key:".$cleand_author.";"."\n";
							 							   
							 list($Name,$Surname) = explode(" ", ltrim($author_name));
						      $author_collection .= "$key:".$cleand_author." foaf:Name \"".str_replace('.',"",$Name)."\";\n"."foaf:Surname \""
						      .$Surname."\";"."\n"."foaf:Fullname \"".$cleand_author."\"."."\n";
							
							  }
								//creating author list
								 $str .= "bibo:authorList "."_:bnodeauthor".";"."\n";
								 $author_counter = 1;
								 $author_collection .= "_:bnodauthor ";
							      foreach($this->splitAnd($value) as $author_name){
									  $author_name=str_replace("{\\\"o}",'oe',$author_name);
						//	if (strpos($author_name,"{\"o}" )===true) {$deu="{\"o}"; $replace='oe';} else if( strpos($author_name,"{\"u}")===true){ $deu="{\"u}";$replace='ue';} else $deu=$replace="";
							 	     $cleand_author = str_replace('_et_al',"",(str_replace('.',"",(str_replace(" ","_",ltrim($author_name))))));
									 $author_collection .= "rdf:_".$author_counter." $key:".$cleand_author.";"."\n";
									 $author_counter++;
										}
										$author_collection .="a "."rdf:seq."."\n";
								//creating author list END
						}
						    
						// 
						if ($counter < count($separate[0][2][$c])-1 && $key != "editor" && $key!="author" && $key!="pages" && $key!="booktitle" )
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
//<?xml version=version="1.0"

	$header = '
@prefix foaf: <http://xmlns.com/foaf/0.1/>.
@prefix dcterms: <http://purl.org/dc/terms/>.
@prefix dc: <http://purl.org/dc/elements/1.1/>.
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>.
@prefix rdfs:<http://www.w3.org/2000/01/rdf-schema#>.
@prefix bibo: <http://purl.org/ontology/bibo/>.
@prefix iris: <http://purl.org/net/unis/iris/>.
@prefix event: <http://purl.org/net/c4dm/event.owl#place/>.
@prefix editor: <http://purl.org/ontology/bibo/editor/>.
@prefix schema:<http://schemas.talis.com/2005/address/schema#localityName/>.
@prefix author: <http://purl.org/dc/terms/contributor/>.';

   $go = new convertToBibo();
  // print_r($go->parsingBibtex());
   $str_outputs = $go->parsingBibtex();
   echo $header."\n";
   foreach ($str_outputs as $str_output) {
			//$output = substr_replace($str_output,".",-2);
			echo $str_output."\n";
   }
