<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use JonnyW\PhantomJs\Client;


class Model extends CI_Model {
    function __construct() {
      parent::__construct();
      $this->load->database();
    }
    
    //================================DB接続関連================================
    public function getNG_Word($genre_id, $genre_sub_id){
        //小ジャンルが選択された時
        if($genre_sub_id !== 0){
            $sql = "select ng_word from ng_words where genre_id='".$genre_id."' AND genre_sub_id='".$genre_sub_id."' OR wildcard='1'";
        }else if($genre_sub_id == 0){
        //小ジャンルが選択されていない時
            $sql = "select ng_word from ng_words where genre_id='".$genre_id."' OR wildcard='1'";
        }
        $query = $this->db->query($sql);
        $lp_checked_table_rows = json_decode(json_encode($query->result()), true);
        $result = array_column($lp_checked_table_rows, "ng_word");
        return $result;
    }

    public function getRevision($ng_word){
        //$stmtと$sqlの意味合いはだいたい同じ
        $sql = "select revision from ng_words where ng_word='".$ng_word."'";
        $query = $this->db->query($sql);
        $lp_checked_table_rows = json_decode(json_encode($query->result()), true);
        $result = array_column($lp_checked_table_rows, "revision");
        return $result;
    }
    //停止中　カラムにNGの重要度が追加された時に必要なメソッド
    // public function getLevel($ng_word){
    //     //$stmtと$sqlの意味合いはだいたい同じ
    //     $sql = "select level from ng_words where ng_word='".$ng_word."'";
    //     $query = $this->db->query($sql);
    //     $lp_checked_table_rows = json_decode(json_encode($query->result()), true);
    //     $result = array_column($lp_checked_table_rows, "level");
    //     return $result;
    // }
    public function getNo($ng_word){
        //$stmtと$sqlの意味合いはだいたい同じ
        $sql = "select no from ng_words where ng_word='".$ng_word."'";
        $query = $this->db->query($sql);
        $lp_checked_table_rows = json_decode(json_encode($query->result()), true);
        $result = array_column($lp_checked_table_rows, "no");
        return $result;
    }
    // public function getScriptData($genre_id, $genre_sub_id){
    //     //小ジャンルが選択された時
    //     if($genre_sub_id !== 0){
    //         $sql = "select * from ng_words where genre_id='".$genre_id."' AND genre_sub_id='".$genre_sub_id."' OR wildcard='1'";
    //     }else if($genre_sub_id == 0){
    //     //小ジャンルが選択されていない時
    //         $sql = "select * from ng_words where genre_id='".$genre_id."' OR wildcard='1'";
    //     }
        
    //     $query = $this->db->query($sql);
    //     $results = json_decode(json_encode($query->result()), true);
    //     return $results;
    // }

    public function insert_data($genre_id, $genre_sub_id, $ng_word, $revision, $wildcard){
        $data = array(
            'genre_id' => $genre_id,
            'genre_sub_id' => $genre_sub_id,
            'ng_word' => $ng_word,
            'revision' => $revision,
            'wildcard' => $wildcard
        );
        $this->db->insert('ng_words', $data);
    }

    //======================================================================================
    public function getJpg($request_url) {
        require( APPPATH.'../vendor/autoload.php' );
        
        $client = Client::getInstance();
        $client->getEngine()->setPath(APPPATH.'../vendor/bin/phantomjs');
        
        $request  = $client->getMessageFactory()->createCaptureRequest($request_url);
        $response = $client->getMessageFactory()->createResponse();
        $save_path = APPPATH.'../public/screenshot/capture_'.date("Y-m-d-H-i-s").'.png';
        $request->setOutputFile($save_path);
        $client->send($request, $response);
        return $save_path;
    }
    
    //画像ダウンロード →ディレクトリ名(ホストURL)　ファイル名(NGワード_number.jpg)
    public function getDownloadImage($image_path, $input_url_name, $ng_word){

        $url = parse_url($input_url_name);
        $dir_path = APPPATH."../public/ng_image/".$url['host'];
        //--------スプリントと、インプロードで書き換え---------
        // $new_ng_word = str_split($ng_word);
        $new_ng_word = preg_split("//u", $ng_word, -1, PREG_SPLIT_NO_EMPTY);
        // print_r( $new_ng_word ); //この時点で漢字やカタカナは文字化けする　str_splitで文字化け対策
        $new_ng_word = implode("_", $new_ng_word);
        //-------------------------------------------------
        $file_path = $dir_path."/".$new_ng_word.".png";
        //$pathディレクトリが存在するか確認
        if(file_exists($dir_path)){
            //存在したときの処理→画像をかぶらないように作成する
            $file_path = $this->model->unique_filename($file_path, $num=0);
            $data = file_get_contents($image_path);
            file_put_contents($file_path, $data);
            return $file_path;
        }else{
            //存在しないときの処理→ディレクトリを作成
            if(mkdir($dir_path, 0777)){
                //画像を作成する
                $file_path = $this->model->unique_filename($file_path, $num=0);
                $data = file_get_contents($image_path);
                file_put_contents( $file_path, $data);
                return $file_path;
            }else{
                //作成に失敗した時の処理
                echo "作成に失敗";
            }
        }
    }

    //ファイルがダブったときの再帰的処理
        function unique_filename($file_path, $num=0){
            if( $num > 0){
                $info = pathinfo($file_path);
                $file_path = str_replace('.png', '', $file_path); //拡張子を引きたい png　str_replace('ab', '', $str);
                $file_path = $file_path. "_" . $num; //差分をつける
                $path = $file_path. ".png"; //拡張子を足したい png
            } else {
                $path = $file_path;
            }
             
            if(file_exists($path)){
                $num++;
                return $this->model->unique_filename($file_path, $num);
            } else {
                return $path;
            }
        }
    
    public function getInfofromImage($image_path, $input_url_name, $genre_id, $genre_sub_id) {

        // /config/development/api_key.phpからAPIキーを呼び出す
        $this->config->load('api_key');
        $this->load->model('google_model');

        //画像から出たテキストと、NGとしてチェックすべきテキストの抽出
        $google_text_array = $this->google_model->google_api_run($image_path);
        
        $ng_words = $this->model->getNG_Word($genre_id, $genre_sub_id);
        
        //単語が入ってるかチェック → テキストが埋め込まれている
        if(isset($google_text_array["responses"][0]["textAnnotations"])){
            //定義
            $n = 0;
            $flag = 0;
            $text = "";
            $check_words = "";
            $ng_word_temp = "";
            $fx = "";
            $fy = "";
            $ng_word_array = array();
            $rectangle_first_x_array = array();
            $rectangle_first_y_array = array();
            $rectangle_third_x_array = array();
            $rectangle_third_y_array = array();
            $ng_image_path_array = array();
            $image_path_array = array();
            $ng_image_for_showing_array = array();
            foreach($google_text_array["responses"][0]["textAnnotations"] as $descriptionAndAnotation){
                //最初の文章全体の結果をスキップ
                if ($descriptionAndAnotation === reset($google_text_array["responses"][0]["textAnnotations"])) {
                    continue;
                }
                //単語を抽出
                $text = $descriptionAndAnotation["description"];

                    //テキストが10文字続いて、NGワードを発見できない場合、一旦文章の途中ですべてをリセットする。
                    if($check_words !== ""){
                        $n = $n + 1;
                        if($n == 10 ){ //しきい値
                            $flag = 0;
                            $n = 0;
                            $check_words = "";
                            $ng_word_temp = "";
                            $fx = "";
                            $fy = "";
                        }
                    }
                    
                //NGワードループ
                foreach($ng_words as $ng_word){
                    //単語の完全一致 $check_wordsの完全一致を統合させるとワードの検知がずれてしまうので一緒にしないこと。
                    if($text === $ng_word){
                      $flag = 1;
                    }

                    //先頭の文字の部分一致 ※strcmp() は、文字列が等しい場合、文字の位置を数値で返す　0だと先頭
                    if(strpos($ng_word, $text) === 0){
                        //例　超低刺激の、超が$check_wordsに格納されている
                        $check_words = $check_words.$text;
                        $ng_word_temp = $ng_word;

                        //例　超低刺激の、超の左上座標を取得
                        if(strpos($check_words, $text) === 0 && $flag !== 1){
                            $fx = "";
                            $fy = "";
                            $fx = $descriptionAndAnotation["boundingPoly"]["vertices"]['0']['x'];
                            $fy = $descriptionAndAnotation["boundingPoly"]["vertices"]['0']['y'];
                            
                            //１番目の座標(左上)
                            list(
                                $rectangle_first_x,
                                $rectangle_first_y
                            ) = $this->getTheUpperLeftCoordinatePoint($fx, $fy);
                        }
                    }
                    //一致していなくて、かつ、先頭一致もしていないときスキップする
                    if(empty($check_words) && $flag !== 1){
                        continue;
                    }

                    //先頭文字以降の部分チェック
                    if(
                        //NGワードに連結テキストが含まれている場合
                        strpos($ng_word, $check_words) !== false &&
                        //先頭文字でチェックに使用したNGワードと同じかどうかチェックする
                        $ng_word_temp === $ng_word &&
                        strpos($ng_word, $text) !== 0
                    ){
                        $check_words = $check_words.$text;
                        
                    }
                    //完全一致（連結用と完全一致した場合）
                    if($check_words === $ng_word_temp){
                        $flag = 1; //→画像処理開始
                        $ng_word_temp = "";
                    }
                    if($flag === 1){
                        //チェックスルーした画像を処理
                        //NG画像をダウンロード、そのパスを取得
                        $ng_image_path = $this->model->getDownloadImage($image_path, $input_url_name, $ng_word);
                        $check_words = "";
                        //NG画像PNGの透過を白塗り背景のJPG画像へ変換
                        $this->createWhiteImage($ng_image_path);
                        
                        //座標を取得 ※先頭文字の座標を持っていたらこれを弾く
                        if($fx == "" || $fy == ""){
                            $fx = "";
                            $fy = "";
                            $fx = $descriptionAndAnotation["boundingPoly"]["vertices"]['0']['x'];
                            $fy = $descriptionAndAnotation["boundingPoly"]["vertices"]['0']['y'];
                        }
                            $tx = "";
                            $ty = "";
                            $tx = $descriptionAndAnotation["boundingPoly"]["vertices"]['2']['x'];
                            $ty = $descriptionAndAnotation["boundingPoly"]["vertices"]['2']['y'];
                        
                        //座標(左上)
                        list(
                            $rectangle_first_x,
                            $rectangle_first_y
                            ) = $this->getTheUpperLeftCoordinatePoint($fx, $fy);
                        //座標（右下）
                        list(
                            $rectangle_third_x,
                            $rectangle_third_y
                            ) = $this->getTheUpperLeftCoordinatePoint($tx, $ty);

                        // $this->model->writeRectangle(
                        //     $ng_image_path,
                        //     //　画像の座標２点を渡す
                        //     //　右上の座標
                        //     $rectangle_first_x,
                        //     $rectangle_first_y,
                        //     //　左下の座標
                        //     $rectangle_third_x,
                        //     $rectangle_third_y,
                        //     $ng_word
                        // );
                        //$ng_image_pathをサーバー表示用に置き換えさせる
                        $ng_image_for_showing = str_replace(APPPATH.'../public', base_url(), $ng_image_path);

                        array_push($ng_word_array, $ng_word);
                        array_push($rectangle_first_x_array, $rectangle_first_x);
                        array_push($rectangle_first_y_array, $rectangle_first_y);
                        array_push($rectangle_third_x_array, $rectangle_third_x);
                        array_push($rectangle_third_y_array, $rectangle_third_y);
                        array_push($ng_image_path_array, $ng_image_path);
                        array_push($image_path_array, $image_path);
                        array_push($ng_image_for_showing_array ,$ng_image_for_showing);
                        $flag = 0;
                        $n = 0;
                        $check_words = "";
                        $ng_word_temp = "";
                        $fx = "";
                        $fy = "";
                    }
                    //endif NGワード発見した場合の処理 フラグの処理
                    //======================================================================
                    //$check_wordsに3個以上同じ単語が入らないようにするためのチェック
                    if($check_words !== ""){
                        $all_ng_counts = "";
                        $ng_counts = "";
                        $all_ng_counts = count($ng_words);
                    foreach($ng_words as $ng_word){
                            if(strpos($ng_word, $check_words) === false){
                                $ng_counts++;
                                if($ng_counts == $all_ng_counts){
                                    $check_words = "";
                                    $all_ng_counts = "";
                                    $fx = "";
                                    $fy = "";
                                }
                            }
                        }
                        $all_ng_counts = "";
                    }//======================================================================
                }//endforeach(1単語を1NGワードグループで回している) DBの登録したNGテキスト数だけ試行回数を行う
              
                 
        } //endforeach　画像から座標とテキストを抜き取る

        return array(
                $ng_word_array,
                $rectangle_first_x_array,
                $rectangle_first_y_array,
                $rectangle_third_x_array, 
                $rectangle_third_y_array,
                $ng_image_path_array,
                $image_path_array,
                $ng_image_for_showing_array
              );
        
        }//endif　画像に文字が含まれている場合の処理
    }//end getInfofromImage
    
    //左上座標を取得
    public function getTheUpperLeftCoordinatePoint($fx, $fy){
        if(
            isset($fx)&&
            isset($fy)
        ){
            $rectangle_first_x = $fx;
            $rectangle_first_y = $fy;
        }else if(
            isset($descriptionAndAnotation["boundingPoly"]["vertices"]['0']['x'])&&
            isset($fy) === false
            ){
            $rectangle_first_x = $descriptionAndAnotation["boundingPoly"]["vertices"]['0']['x'];
            $rectangle_first_y = 0;
        }else if(
            isset($fx) === false &&
            isset($fy)
            ){
            $rectangle_first_x = 0;
            $rectangle_first_y = $fy;
        }else{
            $rectangle_first_x = 0;
            $rectangle_first_y = 0;
        }
        
        return array(
            $rectangle_first_x,
            $rectangle_first_y
            );
        }
        
        //右下座標を取得
        public function getTheLowerRightCoordinatePoint($tx, $ty){
            if(
                isset($tx)&&
                isset($ty)
            ){
                $rectangle_third_x = $tx;
                $rectangle_third_y = $ty;
            }else{
                $rectangle_third_x = 1;
                $rectangle_third_y = 1;
            }
            return array(
                $rectangle_third_x,
                $rectangle_third_y
            );
            
        }
    //------------透過→色付きに変換する処理(のつもりだけど、現在は透過画像を生成するだけの機能)-----------------------
    public function createWhiteImage($file_path){
        //画像の定義を行う
        $input_file = $file_path;
        $output_file = $file_path;

        //動的に画像の拡張子を取得する
        $typeofimage = exif_imagetype($file_path);
        if($typeofimage == 1){
            $input = imagecreatefromgif($input_file);
        }else if($typeofimage == 2){
            $input = imagecreatefromjpeg($input_file);
        }else if($typeofimage == 3){
            $input = imagecreatefrompng($input_file);
        }

        //動的に画像サイズを取得
        list($width, $height) = getimagesize($input_file);
        //画像のリソースを変数に格納 (imagecreatetruecolor)
        $output = imagecreatetruecolor($width, $height);
        
        // アノテーション色設定→透明度の設定　完全透明と不完全透明、画像部分で、（不完全透明の色の設定ができる）
        $white = imagecolorallocate($output,  255, 255, 255);
        // $red = imagecolorallocate($output,  255, 0, 0);
        // $gray = imagecolorallocate($output, 0xAA, 0xAA, 0xAA);

        // 画像の構築（ここ重要）
        imagefilledrectangle($output, 0, 0, $width, $height, $white);
        imagecopy($output, $input, 0, 0, 0, 0, $width, $height);
        imagecolortransparent($output, $white);
        imagepng($output, $output_file);
    }

    public function writeRectangle($image_path, $x1, $y1, $x2, $y2){
        $ImageResource = imagecreatefrompng($image_path);
        $red = imagecolorallocate($ImageResource, 255, 0, 0);
        $color = $red;
        //画像のデータと先の太さ設定を指定する
        imagesetthickness($ImageResource, 5);
        //画像のデータ変数、軸情報４個、色変数で画像に描写を行う
        imagerectangle($ImageResource , $x1-5, $y1-3, $x2+5, $y2+3, $color);
        imagepng($ImageResource, $image_path);
    }
    
    //現在保留中
    // public function writeRectangle($image_path, $x1, $y1, $x2, $y2,$ng_word){
    public function writeRectangle_horyuu($image_path, $x1, $y1, $x2, $y2){
        //NGレベルの取得
        // $get_level = $this->model->getLevel($ng_word);
        //画像のデータ変数をリソースから生成
        $ImageResource = imagecreatefrompng($image_path);
        //線の色をつける
        // $black = imagecolorallocate($ImageResource ,0,0,0);

        //================NGレベル判定================
        //中程度
        // if($get_level[0] == 3){
        //     $green = imagecolorallocate($ImageResource, 59, 175, 117);
        //     $color = $green;
        // //高程度
        // }else if($get_level[0] == 4){
        //     $red = imagecolorallocate($ImageResource, 255, 0, 0);
        //     $color = $red;
        // }else{
        //     $black = imagecolorallocate($ImageResource ,0,0,0);
        //     $color = $black;
        // }
        //書き込む設定を行う
        $red = imagecolorallocate($ImageResource, 255, 0, 0);
        $color = $red;
        //画像のデータと先の太さ設定を指定する
        imagesetthickness($ImageResource, 5);
        //画像のデータ変数、軸情報４個、色変数で画像に描写を行う
        imagerectangle($ImageResource , $x1-5, $y1-3, $x2+5, $y2+3, $color);
        //画像のデータ変数、ローカルディレクトリの指定変数へ新しく描画した画像を生成
        imagepng($ImageResource, $image_path);
    }

    //画像に注釈を書き込むテスト
    // public function writeText($image_path, $x1, $y1, $x2, $y2, $ng_word){
    //     $get_revison = $this->model->getRevision($ng_word);
    //     //テキストの入力
    //     $text = $ng_word.' (訂正:'.$get_revison[0].')';
    //     //フォントの設定
    //     $font = APPPATH."models/meiryo.ttc";
    //     //テキストの大きさ
    //     $fsize = 12;
    //     $fangle = 0;
    //     $ImageResource = imagecreatefrompng($image_path);
    //     //テキストの色の設定
    //     $red = imagecolorallocate($ImageResource,  255, 0, 0);
    //     //オフホワイトの背景色を設定
    //     $off_white = imagecolorallocate($ImageResource,  240, 240, 240);
    //     //背景の色を短形で塗りつぶす(短形の座標を手動で設定→あまり融通は利かない
    //     // imagefilledrectangle ( $ImageResource , $x1 , $y1-40 , $x2+380, $y1-10 , $off_white ); //1行で書き込む
    //     imagefilledrectangle ( $ImageResource , 0 , $y1-40 , 5000, $y1-10 , $off_white ); //X軸を0にして書き込む
    //     //テキストを書き込む
    //     // imagettftext($ImageResource, $fsize, $fangle, $x1, $y1-20, $red, $font, $text); //1行で書き込む
    //     imagettftext($ImageResource, $fsize, $fangle, 0, $y1-20, $red, $font, $text); //X軸を0にして書き込む
    //     //画像の作成を実行する
    //     imagepng($ImageResource, $image_path);
    // }

    
    public function getDocumentAndImagesfromUrl($request_url){
        // phpQueryの読み込み
        require_once(APPPATH.'models/phpQuery-onefile.php');
        // 取得したいwebサイトを読み込む
        $html_pre = file_get_contents($request_url);
        // パターンA
        /*$html = preg_replace('/^<\?xml.*\?>/', '', $html);
        $document = phpQuery::newDocument($html);*/
        // パターンB
        $html = $html_pre;
        $html = mb_convert_encoding ($html , 'UTF-8', 'ASCII, JIS, UTF-8, SJIS, Shift_JIS');
        $document = phpQuery::newDocumentHTML($html);
        // パターンC
        $exist_img = phpQuery::newDocument($document)->find("img");
        if(empty($exist_img[0]->attr("src"))){
            $html = @mb_convert_encoding ($html_pre , 'UTF-8', 'auto');
            $document = phpQuery::newDocumentHTML($html);
        }
        // $html = mb_convert_encoding ($html , 'UTF-8', 'auto');
        // $document = phpQuery::newDocumentHTML($html);

        // var_dump($document);
        // die;
        //※この時点で$request_urlが何回層あるのか探す（アプリケーションパスを設定）

        //-----------------ディレクトリ設定------------------
        $path_arr = explode('/', $request_url);
        $sub_store_path = '';
        $sub_store_path = $path_arr;
        $url = parse_url($request_url);
        $top_dir = $url['scheme'].'://'.$url['host'].'/';
        //相対パスを絶対パスに置換（../→トップディレクトリに変更する）
        $document = str_replace("../", $top_dir, $document);

        if(strpos($document, 'src="./') !== false){
            $max_path_counts = count($sub_store_path);
            $css_path_counts = (int)$max_path_counts - 1;
            $sub_store_path = array_splice($sub_store_path, 0, $css_path_counts);
            $request_url = implode("/", $sub_store_path);
            $document = str_replace('src="./', 'src="'.$request_url.'/', $document);
        }
        $sub_store_path = $path_arr;
        if(strpos($document, 'src="img/') !== false){
            $max_path_counts = count($sub_store_path);
            $css_path_counts = (int)$max_path_counts - 1;
            $sub_store_path = array_splice($sub_store_path, 0, $css_path_counts);
            $request_url = implode("/", $sub_store_path);
            // src="img/を入力URLに置換（ href="[-----]img/→入力URLに変更)
            $document = str_replace('src="img/', 'src="'.$request_url.'/'.'img/', $document);
        }
        $sub_store_path = $path_arr;
        if(strpos($document, 'src="images/') !== false){
            $max_path_counts = count($sub_store_path);
            $css_path_counts = (int)$max_path_counts - 1;
            $sub_store_path = array_splice($sub_store_path, 0, $css_path_counts);
            $request_url = implode("/", $sub_store_path);
            // src="img/を入力URLに置換（ href="[-----]img/→入力URLに変更)
            $document = str_replace('src="images/', 'src="'.$request_url.'/'.'images/', $document);
        }
        $sub_store_path = $path_arr;
        if(strpos($document, 'src="asset/') !== false){
            $max_path_counts = count($sub_store_path);
            $css_path_counts = (int)$max_path_counts - 1;
            $sub_store_path = array_splice($sub_store_path, 0, $css_path_counts);
            $request_url = implode("/", $sub_store_path);
            // src="asset/を入力URLに置換（ href="[-----]asset/→入力URLに変更)
            $document = str_replace('src="asset/', 'src="'.$request_url.'/'.'asset/', $document);
        }
        $sub_store_path = $path_arr;
        if(strpos($document, 'href="asset/') !== false){
            $max_path_counts = count($sub_store_path);
            $css_path_counts = (int)$max_path_counts - 1;
            $sub_store_path = array_splice($sub_store_path, 0, $css_path_counts);
            $request_url = implode("/", $sub_store_path);
            // src="asset/を入力URLに置換（ href="[-----]asset/→入力URLに変更)
            $document = str_replace('href="asset/', 'href="'.$request_url.'/'.'asset/', $document);
        }
        if(strpos($document, 'href="css/') !== false){
            $max_path_counts = count($sub_store_path);
            $css_path_counts = (int)$max_path_counts - 1;
            $sub_store_path = array_splice($sub_store_path, 0, $css_path_counts);
            $request_url = implode("/", $sub_store_path);
            $document = str_replace('href="css/', 'href="'.$request_url.'/'.'css/', $document);
        }
        $sub_store_path = $path_arr;
        if(strpos($document, 'href="pc/') !== false){
            $max_path_counts = count($sub_store_path);
            $css_path_counts = (int)$max_path_counts - 1;
            $sub_store_path = array_splice($sub_store_path, 0, $css_path_counts);
            $request_url = implode("/", $sub_store_path);
            // src="asset/を入力URLに置換（ href="[-----]asset/→入力URLに変更)
            $document = str_replace('href="pc/', 'href="'.$request_url.'/'.'pc/', $document);
        }
        if(strpos($document, 'href="/') !== false){
            $max_path_counts = count($sub_store_path);
            $css_path_counts = (int)$max_path_counts - 1;
            $sub_store_path = array_splice($sub_store_path, 0, $css_path_counts);
            $request_url = implode("/", $sub_store_path);
            $document = str_replace('href="/', 'href="'.$request_url.'/', $document);
        }
        if(strpos($document, 'src="/') !== false){
            $max_path_counts = count($sub_store_path);
            $css_path_counts = (int)$max_path_counts - 1;
            $sub_store_path = array_splice($sub_store_path, 0, $css_path_counts);
            $request_url = implode("/", $sub_store_path);
            $document = str_replace('src="/', 'src="'.$request_url.'/', $document);
        }
        $sub_store_path = $path_arr;
        if(strpos($document, 'href="./') !== false){
            $max_path_counts = count($sub_store_path);
            $css_path_counts = (int)$max_path_counts - 1;
            $sub_store_path = array_splice($sub_store_path, 0, $css_path_counts);
            $request_url = implode("/", $sub_store_path);
            $document = str_replace('href="./', 'href="'.$request_url.'/', $document);
        }

        //===========================================
        //$documentのエラー表記を出さないためにスクリプトを削除
        $document = str_replace('script', '', $document);
        
        $host = $url['host'];
        $path_arr = explode('/', $request_url);
        $array_images = phpQuery::newDocument($document)->find("img");
        // 画像配列を取得
        $array_src = array();
        foreach($array_images as $img){
            //画像URLを取得
            $img = pq($img);
            $src = $img->attr("src");
            $response = @file_get_contents($src, NULL, NULL, 0, 1);
            if ($response !== false) {
                array_push($array_src, $src);
            }
        }
        // CSSリンク（が含まれているかもしれない）を取得
        $array_css_find = phpQuery::newDocument($document)->find("link");
        $array_css_url = array();
        foreach($array_css_find as $css_url){
            //CSS外部のURLを取得する
            $css_url = pq($css_url);
            $src = $css_url->attr("href");
            //URLがCSSかどうかのチェック
            if(
            	strpos($src, 'css') !== false ||
                strpos($src, 'CSS') !== false
                ){
                    //CSSのURLを配列にプッシュ
                    array_push($array_css_url, $src);
                }//endstrpos
        }
        return array($document, $array_src, $array_css_url, $host);
    }
    
    public function getCssImagesfromUrl($request_css_url){
        // phpQueryの読み込み
        require_once(APPPATH.'models/phpQuery-onefile.php');
        //URLを配列に格納
        $path_arr = explode('/', $request_css_url);
        $css = file_get_contents($request_css_url);
        $document_css = phpQuery::newDocument($css);

        // CSSのセレクタと要素を１個ずつ取得するパターン
        $pattern = '/\..+?\}/s'; // "/s"は改行を含むオプション
        // 例　A.+?Bは、AとBの間 今回A="." B="}"
        preg_match_all($pattern, $document_css, $aryDatas);
        //定義
        $array_css_url_after = array();
        $array_css_url_before = array();
        $array_css_selector = array();
        $array_selector_and_image = array();
        foreach($aryDatas[0] as $selector_and_image_data){
            //---------------------------------------------
            //以下の｛文字列｝を取得する正規表現
            // .セレクタ文字列 { 文字列 }
            $pattern_for_image = '/\{.+?\}/s';
                preg_match($pattern_for_image, $selector_and_image_data, $image_detection);
            //---------------------------------------------
            //{ 文字列 }の中に画像拡張子が入っているのかチェックする処理
            if(!empty($image_detection[0])){
            if(
                strpos($image_detection[0], "jpg") !== false ||
                strpos($image_detection[0], "png") !== false ||
                strpos($image_detection[0], "gif") !== false
            ){
                //以下のセレクタ文字列を取得する正規表現
                $pattern_for_selector = '/\..+?\{/';
                preg_match($pattern_for_selector, $selector_and_image_data, $several_selector_data);
                
                $css_selector_replace = str_replace('{', '', $several_selector_data[0]);
                //この時点で複数のセレクタ（スペースを含む、カンマ区切り入るかも）が入っていたから、再度、ドットとスペースで1個のセレクタを取得する
                if(strpos($css_selector_replace,' ') !== false){
					$pattern_for_one_selector = '/\..+?\ /';
					preg_match($pattern_for_one_selector, $css_selector_replace, $selector_data);
				}else{
					$selector_data = $css_selector_replace;
				}
                //この時点でカンマとスペースを含むかもしれないドット付きのセレクタを1個取得するため、除去する
                $selector_data = str_replace('.', '', $selector_data);
                $selector_data = str_replace(',', '', $selector_data);
                $css_selector = str_replace(' ', '', $selector_data);

                // 画像を取得
                $pattern_for_image = '/\(.+?\)/';
                preg_match($pattern_for_image, $selector_and_image_data, $image_data);
                $css_url_replace = str_replace('(', '', $image_data[0]);
                $css_url = str_replace(')', '', $css_url_replace);
                
                //画像を配列に格納（元ファイルに書かれているエビデンス）
                array_push($array_css_url_before, $css_url);
                array_push($array_css_selector, $css_selector);

                //相対パスを絶対パスに書き換え
                $sub_store_path = '';
                $sub_store_path = $path_arr;
                if(strpos($css_url, 'http') === false){

                	// パターン1(../../../var/image.png)../がいくつあるかカウント、個数を$path_arrから引いて絶対パスを作る
                	if(preg_match('/^\.\./', $css_url) == 1){
						$relative_pass_counts = mb_substr_count($css_url, "../");
						// 相対パスを削除する部分
						$css_url = str_replace('../', '', $css_url);
						$css_url = str_replace("'", "", $css_url);
						// 入力URLのパスを下から１個ずつ削除して相対パスにつなげる
						$max_path_counts = count($sub_store_path);
						$css_path_counts = (int)$max_path_counts - (int)$relative_pass_counts -1;
						$sub_store_path = array_splice($sub_store_path, 0, $css_path_counts);
						$css_url_save = implode("/", $sub_store_path);
						$css_url = $css_url_save.'/'.$css_url;
					}elseif(preg_match('/^\//', $css_url) == 1){
						// パターン2..(/var/image.png)の場合
						$css_url_save = implode("/", $sub_store_path);
						$css_url = $css_url_save.$css_url;
						// $css_url = $path_arr[0].'//'.$path_arr[2].$css_url;
					}else{
						// パターン3..(var/image.png)の場合
						$max_path_counts = count($sub_store_path);
						$css_path_counts = (int)$max_path_counts - 1;
						$sub_store_path = array_splice($sub_store_path, 0, $css_path_counts);
						$css_url_save = implode("/", $sub_store_path);
						$css_url = $css_url_save.'/'.$css_url;
					}
                }//endif(strpos($css_url, 'http') === false){

                //連想配列( [M-bk] => ../img/subs-01-bk-20170331.jpg )
                $array_selector_and_image += array($css_selector[0] => $css_url);
                array_push($array_css_url_after, $css_url);
            } //if(strpos($image_detection[0], "jpg") !== false ||...
        } //endif(!empty($image_detection[0])){
        } //foreach($aryDatas[0] as $selector_and_image_data){
        

        return array(
                        $array_css_url_before, //相対パスの画像../img/subs-01-bk-20170331.jpg
                        $array_css_url_after, //絶対パスの画像https://rovectin-jp.com/lpte/img/subs-01-bk-20170331.jpg
                        $document_css, //CSSのソースファイル
                        $array_css_selector, //[M-bk]セレクター
                        $array_selector_and_image //連想配列( [M-bk] => ../img/subs-01-bk-20170331.jpg )
                    );
        
    }
    
    public function replaceNGtexts($document, $genre_id, $genre_sub_id){
        //NGワードをDBから呼び出し
        $ng_words = $this->model->getNG_Word($genre_id, $genre_sub_id);
        
        //NGテキストを検出・赤文字に変換
        foreach($ng_words as $one_ng_word){
            $get_revison = $this->model->getRevision($one_ng_word);
            $document =
                str_replace(
                $one_ng_word,
                '<div style="color: red; font-weight: 900; ">'.
                $one_ng_word.' (訂正:'.$get_revison[0].')'.
                '</div>',
                $document
                );
        }
        return $document;
    }
}