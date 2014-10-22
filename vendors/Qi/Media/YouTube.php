<?php
    require_once 'Zend/Loader.php';
    Zend_Loader::loadClass('Zend_Gdata_YouTube');
    Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
    Zend_Loader::loadClass('Zend_Gdata_App_Exception');
    
    function getHttpClient($username, $password, $developer_key){
        $loginUri = 'https://www.google.com/youtube/accounts/ClientLogin';
      try {
            $http_client = Zend_Gdata_ClientLogin::getHttpClient(
                $username,
                $password,
                $service = 'youtube',
                $client = NULL,
                $source = 'cougarhunting.com',
                $loginToken = NULL,
                $loginCaptcha = NULL,
                $loginUri
            );
            if(is_null($http_client)) return FALSE;
            $http_client->setHeaders('X-GData-Key', 'key=' . $developer_key);
      }catch (Zend_Gdata_App_AuthException $authEx) {
            $error = 'Zend_Gdata_App_AuthException';
        return FALSE;
      }catch (Zend_Gdata_App_HttpException $e) {
            $error = 'Zend_Gdata_App_AuthException';
        return FALSE;
      }
        return $http_client;
    }
    function getYouTubeFormValues($httpClient, $videoInfo, $module, $values){
      $youTubeService = new Zend_Gdata_YouTube($httpClient);
      $newVideoEntry = new Zend_Gdata_YouTube_VideoEntry();
        // set video info
      $newVideoEntry->setVideoTitle($videoInfo['title']);
      $newVideoEntry->setVideoDescription($videoInfo['description']);
      //make sure first character in category is capitalized
      $videoInfo['category'] = strtoupper(substr($videoInfo['category'], 0, 1)).substr($videoInfo['category'], 1);
      $newVideoEntry->setVideoCategory($videoInfo['category']);
      // convert videoTags from whitespace separated into comma separated
      $videoTagsArray = explode(' ', trim($videoInfo['tags']));
      $newVideoEntry->setVideoTags(implode(', ', $videoTagsArray));
        $newVideoEntry->setVideoPrivate();

      $tokenHandlerUrl = 'http://gdata.youtube.com/action/GetUploadToken';
      try {
          $tokenArray = $youTubeService->getFormUploadToken($newVideoEntry, $tokenHandlerUrl);
          error_log($httpClient->getLastRequest(), 'request');
          error_log($httpClient->getLastResponse()->getBody(), 'response');
      } catch (Zend_Gdata_App_HttpException $httpException) {
          error_log('ERROR ' . $httpException->getMessage()." HTTP details\n".$httpException->getRawResponseBody()."\n");
          return;
      } catch (Zend_Gdata_App_Exception $e) {
          error_log('ERROR - Could not retrieve token for syndicated upload. '.$e->getMessage());
          return;
      }
      $postUrl = $tokenArray['url'];
        $nextUrl = "http://cougar.transio.net/api/{$module}_video_upload.php";
        $first = true;
        foreach($values as $key => $value){
            if($first){
                $nextUrl .= "?";
                $first = false;
            }else{
                $nextUrl .= "&";
            }
            $nextUrl .= "{$key}={$value}";
        }
      $tokenValue = $tokenArray['token'];
        $result = array(
                'post_url' => $postUrl,
                'next_url' => $nextUrl,
                'token_value' => $tokenValue
            );
        return $result;
    }
?>