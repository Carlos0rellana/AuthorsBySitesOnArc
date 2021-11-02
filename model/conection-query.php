<?php

function getResultListFromArc($queryArray) {
  $curl = curl_init();
  curl_setopt_array($curl,$queryArray);
  $response = curl_exec($curl);
  curl_close($curl);
  return $response;
}

function makeArrayQuerie($apiQueryUrl,$key=false){
  require ROOT_DIR.'/config/dataConection.php';
  if(!$key){$key=$pass;}
  return array(
    CURLOPT_URL => $apiUrl.$apiQueryUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array('Authorization: Bearer '.$key),
  );
}

function getAuthorsListFromArc() {
      return getResultListFromArc(makeArrayQuerie('author/'));
}

function getAuthorImageFromArc($photo_id){
    return getResultListFromArc(makeArrayQuerie('photo/api/v2/photos/'.$photo_id));
}

function getSearchList($site,$userId){
  $canonical='';
  if(!$end){$end=$start;}
  if($canonical_website===true){$canonical='+AND+canonical_website:'.$site;}
  $query='content/v4/search/published?website='.$site.'&q=type:"story"+AND+credits.by._id:"'.$userId.'"&_sourceInclude=publish_date&size=1&from=0&sort=display_date:desc';
  return getResultListFromArc(makeArrayQuerie($query));
}

?>