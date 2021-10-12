<?php
namespace App\valenceSdk\samples\BasicSample;
/**
 * Copyright (c) 2012 Desire2Learn Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the license at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

//require_once 'config.php';
//require_once $config['libpath'] . '/D2LAppContextFactory.php';
use App\valenceSdk\samples\BasicSample\config;
use App\valenceSdk\lib\D2LAppContextFactory;
use App\valenceSdk\lib\D2LHostSpec;
use App\valenceSdk\lib\D2LUserContext;
use App\Param;
use Exception;
use Log;
class UsoApi{
    public $config=array();

    function __construct(){

        $this->config['libpath']='../../lib';
        
        $activo=$param=Param::where('llave','api_brightSpace_activa')->first();

        $param=Param::where('llave','url_bSpace')->first();
        $this->config['host']=$param->valor;
        $this->config['port']=443;
        $this->config['scheme']='https';

        $param=Param::where('llave','id_bSpace')->first();
        $this->config['appId']=$param->valor;
        $param=Param::where('llave','key_bSpace')->first();
        $this->config['appKey']=$param->valor;

	    $param=Param::where('llave','idUser_bSpace')->first();
        $this->config['idUser']=$param->valor;
        $param=Param::where('llave','keyUser_bSpace')->first();
        $this->config['keyUser']=$param->valor;

        $param=Param::where('llave','apiVersion_bSpace')->first();
        $this->config['LP_Version']=$param->valor;

        //dd($this->config);
    }

    /*public $config = array(
        'libpath'    => '../../lib',
    
        'host'       => 'cedvapwctest.brightspace.com',
        'port'       => 443,
        'scheme'     => 'https',
    
        'appId'      => 'TcOyM-3AvJNK8Tg81CwTPw',
        'appKey'     => '5OeObwfWqBbq7AKt4Rp98A',
    
        'LP_Version' => '1.0'
    );*/

    public function getPageURL() {
        $portString = '';
        if (($_SERVER['HTTPS'] == 'on' && $_SERVER['SERVER_PORT'] != 443)
         || ($_SERVER['HTTPS'] == 'off' && $_SERVER['SERVER_PORT'] != 80)) {
                $portString = ':' . $_SERVER['SERVER_PORT'];
        }
        return "http" . ($_SERVER['HTTPS'] == 'on' ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . $portString . $_SERVER['REQUEST_URI'];
    }

    public function deauthenticate(){
        //session_start();
        //unset($_SESSION['userId']);
        //unset($_SESSION['userKey']);
        session()->forget(['userId','userKey']);
        //session_write_close();
        //header("location: index.php");
    }

    public function authenticate(){
	//dd($this->config);
	if($this->config['idUser']==0 and $this->config['keyUser']==0){
		$redirectPage = str_replace('authenticateUser.php', 'postLogin.php', $this->getPageURL());
	        $authContextFactory = new D2LAppContextFactory();
        	$authContext = $authContextFactory->createSecurityContext($this->config['appId'], $this->config['appKey']);
	        $hostSpec = new D2LHostSpec($this->config['host'], $this->config['port'], $this->config['scheme']);
        	$url = $authContext->createUrlForAuthenticationFromHostSpec($hostSpec, $redirectPage);
	        header("Location: $url");
	}
        
    }

    public function doValence($verb, $route) {
	//El arreglo config ya tiene todos los datos de trabajo, igual que el ejemplo del SDK.
	//Se valida la existencia de un id y key de usuario
        
        $userId = $this->config['idUser'];
        $userKey = $this->config['keyUser'];
	//dd();
        
        // Create authContext
        $authContextFactory = new D2LAppContextFactory();
        $authContext = $authContextFactory->createSecurityContext($this->config['appId'], $this->config['appKey']);
    
        // Create userContext
        $hostSpec = new D2LHostSpec($this->config['host'], $this->config['port'], $this->config['scheme']);
        $userContext = $authContext->createUserContextFromHostSpec($hostSpec, $userId, $userKey);
    	//dd($userContext);
        // Create url for API call
        $uri = $userContext->createAuthenticatedUri($route, $verb);
        //dd($uri);
        // Setup cURL
        $ch = curl_init();
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $verb,
            CURLOPT_URL            => $uri,
            
            /* Explicitly turn this on because some versions of cURL (and therefore PHP installations) do
               not default this to true. This verifies the SSL certs that are used to communicate with an
               LMS via HTTPS. It is vitally important that you always do this in production. */
            CURLOPT_SSL_VERIFYPEER => true,
            
            /* CURLOPT_CAINFO points to a liste of trusted certificates to use for verifying (see above).
               If you are on some platforms (e.g. possibly Windows) you may need to explicitly provide these.
               Your platform may not require this as it may provide a system-wide setting (which you should prefer).
               If you need to explicitly set this get an up-to-date file from http://curl.haxx.se/docs/caextract.html
               and preferably set this up in your php.ini globally. For more information, see
               http://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/ */
            // CURLOPT_CAINFO         => getcwd() . '/cacert.pem'
        );
        curl_setopt_array($ch, $options);
    
        // Do call
        $response = curl_exec($ch);
    
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $responseCode = $userContext->handleResult($response, $httpCode, $contentType);
   
        if ($responseCode == D2LUserContext::RESULT_OKAY) {
            return json_decode($response, true);
        }
    
        // TODO handle time skew errors
    
        curl_close($ch);
        throw new Exception("Valence API call $uri failed: $httpCode: $response ");
    }

    public function doValence2($verb, $route, $data=null) {
        //El arreglo config ya tiene todos los datos de trabajo, igual que el ejemplo del SDK.
        //Se valida la existencia de un id y key de usuario
            
            $userId = $this->config['idUser'];
            $userKey = $this->config['keyUser'];
        //dd();
            
            // Create authContext
            $authContextFactory = new D2LAppContextFactory();
            $authContext = $authContextFactory->createSecurityContext($this->config['appId'], $this->config['appKey']);
        
            // Create userContext
            $hostSpec = new D2LHostSpec($this->config['host'], $this->config['port'], $this->config['scheme']);
            $userContext = $authContext->createUserContextFromHostSpec($hostSpec, $userId, $userKey);
            //dd($userContext);
            // Create url for API call
            $uri = $userContext->createAuthenticatedUri($route, $verb);
            //dd($uri);
            $client = new \GuzzleHttp\Client();
	    switch ($verb) {
	    	case 'GET':
			$response = $client->request('GET', $uri, [
        	        	'UpdateUserData ' => [
	                    	'Activation' => ['isActive'=>false],
                		]
                    ]);
            //if($response->getStatusCode()==200){
                $response = $response->getBody()->getContents();
			    return json_decode($response, true);
            //}        
	            	
			break;
		case 'PUT':
			//dd($uri);
			$request = $client->put($uri,  ['json'=>$data]);
	            	//dd($resquest->getStatusCode);
			return json_decode($request->getBody()->getContents(), true);
			
		break;

	    }
            
        }
}

