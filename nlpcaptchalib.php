<?php 
define("NLPCAPTCHA_VALIDATE_URL", "http://call.nlpcaptcha.in/index.php/ad/validate");
define("NLPCAPTCHA_JS_URL", "http://call.nlpcaptcha.in/js/captcha.js");
//--NLPCaptcha Process Timeout variables
define("NLP_VALIDATE_TIMEOUT", '0');

//require_once("nlpconfig.php");
function NLP_Captcha($publisherkey)
{
    $captcha=  "<script type=\"text/javascript\"> var NLPOptions = {key:'".$publisherkey."'};</script>";
    $captcha.=  '<script type="text/javascript" src="'.NLPCAPTCHA_JS_URL.'" ></script>';
  return $captcha;
}
/*
@Func_Name: NLP_Validate()
@Description: Validate Captcha
@PARAM: none
@RETURN: Bool
*/
function NLP_Validate($nlpIdentifier,$nlpAnswer,$validatekey){

		//---we accept validation via post method only.
		if($validatekey==NULL or trim($validatekey) == '' or strlen(trim($validatekey))<>32)
		{
		 return false;
		}
		if((empty($nlpAnswer)||empty($nlpIdentifier))){
		  return false;
		}
                
                $arr_params=array('ValidateKey'=>$validatekey, 'Identifier'=>$nlpIdentifier,'Answer'=>$nlpAnswer);
       
		if(function_exists('curl_init'))
                {
                  
		$response =  curl_post(NLPCAPTCHA_VALIDATE_URL,$arr_params);
                }
                else
                {
                  
                $response=post_request(NLPCAPTCHA_VALIDATE_URL, $arr_params);  
                }
			//print_r($response);
                $responseArr = NLP_Split_Response($response);
		if($responseArr[0] == "success")
		{
			return true;
		}else{
			//--developer purpose only
			//echo "error: ".$responseArr[1]."<br />";
			//--uncomment to view error message
			return false;
		}
}
/** 
 * Send a POST requst using cURL 
 * @param string $url to request 
 * @param array $post values to send 
 * @param array $options for cURL 
 * @return string 
 */ 
function curl_post($url, array $post = NULL, array $options = array()) 
{ 
    $defaults = array( 
        CURLOPT_POST => 1, 
        CURLOPT_HEADER => 0, 
        CURLOPT_URL => $url, 
        CURLOPT_FRESH_CONNECT => 1, 
        CURLOPT_RETURNTRANSFER => 1, 
        CURLOPT_FORBID_REUSE => 1, 
        CURLOPT_TIMEOUT => 40, 
        CURLOPT_POSTFIELDS => http_build_query($post,"","&") 
    ); 
   if(NLP_VALIDATE_TIMEOUT>0)
       {
	$defaults[CURLOPT_TIMEOUT]=NLP_VALIDATE_TIMEOUT;
	}
    $ch = curl_init(); 
    curl_setopt_array($ch, ($options + $defaults)); 
    if( ! $result = curl_exec($ch)) 
    { 
        trigger_error(curl_error($ch)); 
    } 
    curl_close($ch); 
    return $result; 
} 


function NLP_Split_Response($response){
	$spliter = ":@NLP@:"; // Don't change or remove this otherwise application won't work;
	$dataArr = array();
	$dataArr = explode($spliter, $response);
	return $dataArr;
}
if (!function_exists('http_build_query')) { 
    function http_build_query($data, $prefix='', $sep='', $key='') { 
        $ret = array(); 
        foreach ((array)$data as $k => $v) { 
            if (is_int($k) && $prefix != null) { 
                $k = urlencode($prefix . $k); 
            } 
            if ((!empty($key)) || ($key === 0))  $k = $key.'['.urlencode($k).']';
             if (is_array($v) || is_object($v)) { 
                array_push($ret, http_build_query($v, '', $sep, $k)); 
            } else { 
                array_push($ret, $k.'='.urlencode($v)); 
            } 
        } 
        if (empty($sep)) $sep = ini_get('arg_separator.output'); 
        return implode($sep, $ret); 
    }// http_build_query 
}//if 

/*Functions to Peform Validation Post in Case Curl support is Not Available*/
function post_request($url, $data, $referer='') {
 
    // Convert the data array into URL Parameters like a=b&foo=bar etc.
    $data = http_build_query($data);
 
    // parse the given URL
    $url = parse_url($url);
 
    if ($url['scheme'] != 'http') { 
        
        $scheme="ssl://";
        $port=443;
    }else
    {
          $port=80;
    }
    $host =$url['host'];
    // extract host and path:
    
    $path = $url['path'];
    
    // open a socket connection on port 80 - timeout: 30 sec
    $fp = fsockopen($scheme.$host, $port, $errno, $errstr, 30);
 
    if ($fp){
 
        // send the request headers:
        fputs($fp, "POST $path HTTP/1.1\r\n");
        fputs($fp, "Host: $host\r\n");
 
        if ($referer != '')
            fputs($fp, "Referer: $referer\r\n");
 
        fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
        fputs($fp, "Content-length: ". strlen($data) ."\r\n");
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $data);
 
        $result = ''; 
        while(!feof($fp)) {
            // receive the results of the request
            $result .= fgets($fp, 128);
        }
    }
    else { 
        return false;
    }
 
    // close the socket connection:
    fclose($fp);
   
    // split the result header from the content
    $result = explode("\r\n\r\n", $result, 2);
 
    $header = isset($result[0]) ? $result[0] : '';
    $content = isset($result[1]) ? $result[1] : '';
 
    // return as structured array:
    
    return $content;
}
function nlpcaptcha_get_signup_url ($domain = null, $appname = null) {
	return "http://nlpcaptcha.in";
}

?>
