<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Google_model extends CI_Model {

		public function google_api_run($image_path){
			$this->config->load('api_key');
			$google_api_key = $this->config->item('google_api_key');
			
			// リクエスト用のJSONを作成
		        $json = json_encode( array(
		                "requests" => array(
		                        array(
		                                "image" => array(
		                                        "content" => base64_encode( file_get_contents( $image_path ) ) ,
		                                ) ,
		                                "features" => array(
		                                        array(
		                                                "type" => "TEXT_DETECTION" ,
		                                                "maxResults" => 10 ,
		                                        ),),
		                        ),
		                ) ,
		        ) ) ;
		        
		        // リクエストを実行
		        $curl = curl_init() ;
		        curl_setopt( $curl, CURLOPT_URL, "https://vision.googleapis.com/v1/images:annotate?key=" . $google_api_key ) ;
		        curl_setopt( $curl, CURLOPT_HEADER, true ) ;
		        curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, "POST" ) ;
		        curl_setopt( $curl, CURLOPT_HTTPHEADER, array( "Content-Type: application/json" ) ) ;
		        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false ) ;
		        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ) ;
		        if( isset($referer) && !empty($referer) ) curl_setopt( $curl, CURLOPT_REFERER, $referer ) ;
		        // curl_setopt( $curl, CURLOPT_TIMEOUT, 15 ) ;
		        curl_setopt( $curl, CURLOPT_POSTFIELDS, $json ) ;
		        $res1 = curl_exec( $curl ) ;
		        $res2 = curl_getinfo( $curl ) ;
		        curl_close( $curl );
		        // 取得した全データ
		        $json = substr( $res1, $res2["header_size"] ) ;
		        // PHPの型へJsonを直す
		        $array_php = json_decode($json, true);
		        return $array_php;
		}
    }