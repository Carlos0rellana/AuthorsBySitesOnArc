<?php
    require ROOT_DIR.'/model/conection-query.php';

    function getAuthorsCountry($site_id='mx',$siteUrl='',$cdn='',$forced=false){
        $jsonUrl=ROOT_DIR.'/data/'.$site_id.'.json';
        makeAjsonFile($site_id,$siteUrl,$cdn,$forced);
        return $strJsonFileContents = file_get_contents($jsonUrl);
    }

    function validateValues($value,$reference){
        foreach(explode(",", $value->author_type) as $item){
            if(strtolower(str_replace(' ','', $item))===$reference){
                return true;
            }
        }
        return false;
    }

    function getResizedImage($imgRoute,$rootSite){
        $idImg = pathinfo($imgRoute, PATHINFO_FILENAME);
        $imgObject = json_decode(getAuthorImageFromArc($idImg));
        if(isset($imgObject->additional_properties)){
            return $rootSite.$imgObject->additional_properties->resizeUrl;
        }
        return $imgRoute;
    }

    function array_orderby(){
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
                }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }

    function makeAuthorListByCountry($site_id='mx',$siteUrl='',$cdn='',$type='author'){
        $completeAuthorList = json_decode(getAuthorsListFromArc())->q_results;
        $filterAuthorList = array();
        $arcIdSite='not defined';
        switch ($site_id) {
            case "co":
                $arcIdSite='mwncolombia';
                break;
            case "mx":
                $arcIdSite='mwnmexico';
        }
        foreach($completeAuthorList as $value){
            if($value->author_type){
                if(validateValues($value,$site_id) && validateValues($value,$type)){
                    $author = array();
                    $author['id']   = $value->_id;
                    $author['name'] = $value-> byline;
                    $author['image']= "images/avatar.png";
                    $author['url'] = null;
                    $dateLastPublish = json_decode(getSearchList($arcIdSite,$author['id']),true);
                    if(
                        is_array($dateLastPublish) && 
                        array_key_exists('content_elements',$dateLastPublish) && 
                        count($dateLastPublish['content_elements'])>0 &&
                        array_key_exists('publish_date', $dateLastPublish['content_elements'][0] ) ){
                            $lastArticle = $dateLastPublish['content_elements'][0]['publish_date'];
                            $date = new DateTime($lastArticle);
                            $date = $date->format('U');
                    }else{
                        $lastArticle = $date = 0;
                    }
                    $author['lastArticle']=$lastArticle;
                    $author['lastArticleUnix']=$date;
                    if($value->image){
                        $author['image']=getResizedImage($value->image,$cdn);
                    }
                    if($value->bio_page){
                        $author['url']=$value->bio_page;
                    }
                    array_push($filterAuthorList,$author);
                }
            }
        };
        if(count($filterAuthorList) >= 1){
            return(array_orderby($filterAuthorList,'lastArticleUnix',SORT_DESC));
        }else{
            return array('error' => 'No se encontraron autores que cumplieran con lo solicitado (author_type conteniendo: '.$site_id.' y '.$type.').');
        }
        
    }

    function makeHtmlList($site_id,$cdn='',$route='',$forced=false){
        $jsonUrl=ROOT_DIR.'/data/'.$site_id.'.json';
        $jsonList=json_decode(getAuthorsCountry($site_id,$route,$cdn,$forced), true);
        $liString='';
        if(file_exists($jsonUrl) && !$jsonList['error']){
            foreach($jsonList as $author){
                if($author['url'] && $author['lastArticleUnix']!==0){
                    $liString .= '<li>';
                    getResizedImage($author['image'],$cdn);
                    if($author['url']){
                        $liString .='<a href="'.$route.$author['url'].'" target="_top" class="box-item">';
                    }else{
                        $liString .='<div class="box-item no-pointer">';
                    }
                    $liString .= '<div class="name">
                                    <h3>'.$author['name'].'</h3>
                                </div>';
                    $liString .= '<div class="flex-center">
                                    <div class="relative">
                                        <div class="colons">
                                            <svg class="MuiSvgIcon-root" focusable="false" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"></path></svg>
                                        </div>
                                        <div class="img-contains"><img src="'.$author['image'].'"/></div>
                                    </div>
                                </div>';
                    if($author['url']){
                        $liString .='</a>';
                    }else{
                        $liString .='</div>';
                    }
                    $liString .='</li>';
                }
            }
            return('<ul class="authors gallery js-flickity" data-flickity-options=\'{ "wrapAround": true }\'>'.$liString.'</ul>');
        }
        if(file_exists($jsonUrl) && $jsonList['error']){
            return '<p><b>ERROR:</b>'.$jsonList['error'].'</p>';
        }
        return '<p>'.$jsonUrl.' archivo no existe</p>';
    }
    
    function makeAjsonFile($site_id,$siteUrl='',$cdn='',$forced=false){
        $fileUrl = ROOT_DIR.'/data/'.$site_id.'.json';
        if(!file_exists($fileUrl)){
            $jsonNew = makeAuthorListByCountry($site_id,$siteUrl,$cdn);
            writeFile($fileUrl,json_encode($jsonNew));
        }elseif(file_exists($fileUrl) && ((date('U') - filectime($fileUrl)> 86400) || $forced===true)){
            $jsonOld = $saveJson = json_decode(file_get_contents($fileUrl),true);
            $jsonNew = makeAuthorListByCountry($site_id,$siteUrl,$cdn);
            if(count($jsonNew)>0){
                $saveJson=$jsonNew;
            }
            writeFile($fileUrl,json_encode($saveJson));
        }
    }

    function writeFile($fileUrl,$data){
        $fp = fopen($fileUrl,'w');      
        fwrite($fp,$data);
        fclose($fp);
    }
?>