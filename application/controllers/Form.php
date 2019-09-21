<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Form extends CI_Controller {

	public function index()
	{
	    //配列データの定義ファイルの呼び出し
	    require_once(APPPATH.'config/data.php');
	    $this->load->helper('url');
	    $this->load->helper('form');
	    $this->load->helper('html');
	    $this->load->model('model');
    	$this->load->library('form_validation');
        $this->form_validation->set_rules('genre_id', 'ジャンル', 'required');
        $this->form_validation->set_rules('url_name', 'URL', 'required');
    	$this->form_validation->set_message('required', '%sが入力されていません。');
		
		if ($this->form_validation->run() == FALSE)
        {
            //初回読み込み、またはエラー時のview呼び出し
            $this->load->view('form');

        }else{
        //============================POSTを取得==========================
        //URLを取得
        $data['url_name'] = $this->input->post('url_name');
        //ジャンル情報を取得
        $data['genre_id'] = $this->input->post('genre_id');
        $data['genre_sub_id'] = $this->input->post('genre_sub_id');
        //===============URL→画面をキャプチャー（PhantomJSの利用）==============
        $data['save_path'] = $this->model->getJpg($data['url_name']);

        //=====================NGが一切無い場合==========================
        if(empty($this->model->getNG_Word($data['genre_id'], $data['genre_sub_id']))){
            $this->load->view('thanks', $data);
        };

        //--------------------URLからHTMLと画像URLを取得--------
        list(
            //HTMLそのもの取得
            $data['all_from_query'],
            //画像配列（pngやjpg等)
            $data['all_image_array_from_query'],
            //外部CSS配列
            $data['all_cssurl_array_from_query'],
            //input domainの取得
            $data['host_domain']
        ) = $this->model->getDocumentAndImagesfromUrl($data['url_name'], $data['genre_id'], $data['genre_sub_id']);
        
        $ng_count = 0;
        //HTMLの画像調査
        foreach( $data['all_image_array_from_query'] as $src ){
            
            // 1枚画像URLから、NGテキストとNG座標を返す（GoogleAPIの利用）
            list(
                $text_from_google,
                $rectangle_first_x_from_google,
                $rectangle_first_y_from_google,
                $rectangle_third_x_from_google,
                $rectangle_third_y_from_google,
                $ng_local_image_path,
                $ng_url_image_path
            ) = $this->model->getInfofromImage($src, $data['url_name'], $data['genre_id'], $data['genre_sub_id']);
            
            //ローカル（アノテーション付きNG）画像を表示画像（ドメイン）に書き換えてる
            //リソース画像は同じなので、先頭のみ取得
            if(isset($ng_local_image_path[0])){
                //NG画像を発見するとナンバリング
                $ng_count = $ng_count + 1;
                //サーバー上で画像を閲覧できるようにローカル画像の場所をパスへ書き換え
                $ng_image_for_showing = str_replace(APPPATH.'../public', base_url(), $ng_local_image_path[0]);
                // スクリプトモーダル作成
                $ng_image_for_showing = $ng_image_for_showing.'"'." id='test_".$ng_count."'";
                $script_document = "$('[id=test_".$ng_count."]').wrap('<div class=ngimage id=test_".$ng_count."_view></div>');"." \n"."$('[id=test_".$ng_count."_view]').append('<p>";
                $ng_rectangle_count = 0;
                foreach($text_from_google as $ng_word_of_one_image){
                    //ここで書き換えのほうが良いような気がする
                    $this->model->writeRectangle(
                        $ng_local_image_path[0],
                        //　画像の座標２点を渡す
                        //　右上の座標
                        $rectangle_first_x_from_google[$ng_rectangle_count],
                        $rectangle_first_y_from_google[$ng_rectangle_count],
                        //　左下の座標
                        $rectangle_third_x_from_google[$ng_rectangle_count],
                        $rectangle_third_y_from_google[$ng_rectangle_count]
                    );
                    $ng_rectangle_count = $ng_rectangle_count + 1;
                    $revision = $this->model->getRevision($ng_word_of_one_image);
                    $script_document = $script_document.$ng_word_of_one_image."　".$revision[0]."<br>";
                }
                //モダールとじ
                $script_document_of_one_image = $script_document."</p>'); \n";
                //格納
                array_push($data['ng_url_image_path_for_showing'], $ng_url_image_path[0]);
                array_push($data['ng_local_image_path_for_showing'], $ng_image_for_showing);
                array_push($data['script_document_of_each_images'], $script_document_of_one_image);
            }
        } //ループ終了

        $ng_count = 0;

        //画像（HTML情報にある本家ドメイン（NG画像の場所）→←レントラサーバーのドメイン）の差し替え



        $data['new_html'] = str_replace(
            $data['ng_url_image_path_for_showing'],
            $data['ng_local_image_path_for_showing'],
            $data['all_from_query']
            );
        // echo "<pre>";
        // print_r($data['ng_url_image_path_for_showing']);
        // echo "</pre>";
        // echo "<pre>";
        // print_r($data['ng_local_image_path_for_showing']);
        // echo "</pre>";
        // echo "<pre>";
        // print_r($data['all_from_query']);
        // echo "</pre>";
        // die;

        //HTMLの文字を書き換える
        $data['new_html'] = $this->model->replaceNGtexts($data['new_html'], $data['genre_id'], $data['genre_sub_id']);

        //CSSをスクレイピング
        //CSSがある場合
        if(!empty($data['all_cssurl_array_from_query'])){
            //CSSのURLごとに画像だけを配列化する
            foreach($data['all_cssurl_array_from_query'] as $css_url){
                list(
                    //画像URLの配列データ
                    $array_css_url_before,
                    $array_css_url_after,
                    //テキストデータ
                    $document_css,
                    //セレクターデータ
                    $array_css_selector,
                    //連想配列　keyがセレクタ、valueがURL
                    $array_selector_and_image
                ) = $this->model->getCssImagesfromUrl($css_url);
                //URL接続可能な画像に差し替え
                $document_css = str_replace($array_css_url_before, $array_css_url_after, $document_css);
                
                //外部CSSが複数ある場合に対して、格納をしておく
                array_push($data['array_selector_and_image'], $array_selector_and_image);
                array_push($data['array_css_url_after'], $array_css_url_after);
                array_push($data['document_css'], $document_css);
            }//endforeach
        }//endif!empty
        //===========CSS画像をチェック=========
        foreach( $data['array_selector_and_image'] as $each_css_image_array){
            //画像配列に対して、各画像ファイルに切り分けて調べる
            if(empty($each_css_image_array)){
                continue;
            }
        foreach( $each_css_image_array as $css_selector => $css_url ){
            //CSSの画像のURLが接続できるかチェック
            $html = @file_get_contents($css_url);
            //もしアクセスできないときはスキップ
            if($html == false){
                continue;
            }
            list(
                $css_text_from_google,
                $css_rectangle_first_x_from_google,
                $css_rectangle_first_y_from_google,
                $css_rectangle_third_x_from_google,
                $css_rectangle_third_y_from_google,
                $css_ng_local_image_path,
                $css_ng_url_image_path
            ) = $this->model->getInfofromImage($css_url, $data['url_name'], $data['genre_id'], $data['genre_sub_id']);

                // var_dump($css_ng_local_image_path);
            

            if(isset($css_ng_local_image_path[0])){
                $ng_count = $ng_count + 1;
                $css_ng_image_for_showing = str_replace(APPPATH.'../public', base_url(), $css_ng_local_image_path[0]);
                //CSS用のスクリプトモーダル作成
                // $css_ng_image_for_showing = '"'.$css_ng_image_for_showing.'"'." id='test_".$ng_count."'";
                $css_script_document = "$('[id=test_".$ng_count."_css]').wrap('<div class=ngimage_css id=test_".$ng_count."_view_css></div>');"." \n"."$('[id=test_".$ng_count."_view_css]').append('<p>";
                $ng_rectangle_count = 0;
                foreach($css_text_from_google as $css_ng_word_of_one_image){
                    //ここで書き換えのほうが良いような気がする
                    $this->model->writeRectangle(
                        $css_ng_local_image_path[0],
                        //画像の座標２点を渡す
                        //右上の座標
                        $css_rectangle_first_x_from_google[$ng_rectangle_count],
                        $css_rectangle_first_y_from_google[$ng_rectangle_count],
                        //左下の座標
                        $css_rectangle_third_x_from_google[$ng_rectangle_count],
                        $css_rectangle_third_y_from_google[$ng_rectangle_count]
                    );
                    $ng_rectangle_count = $ng_rectangle_count + 1;
                    $revision = $this->model->getRevision($css_ng_word_of_one_image);
                    $css_script_document = $css_script_document.$css_ng_word_of_one_image."　".$revision[0]."<br>";
                }
                
                //モダールとじ
                $css_script_document_of_one_image = $css_script_document."</p>'); \n";
                //格納
                array_push($data['array_css_selector'], $css_selector);
                array_push($data['css_ng_url_image_path_for_showing'], $css_ng_url_image_path[0]);
                array_push($data['css_ng_local_image_path_for_showing'], $css_ng_image_for_showing);
                array_push($data['css_script_document_of_each_images'], $css_script_document_of_one_image);
            }
        }
        }
        // die;
        //CSSにNG画像がないときにスキップする（したい）
        if(!empty($data['array_css_selector'])){

        // ※明日用のメモ 上の条件を書いて今は動いたが、下のCSSのモーダル用のセレクタを飛ばしていたので出なかったと。
        // 一旦やめたけど、再度以下の処理の見直を行う。
        // var_dump($data['array_css_selector']);
        // array(4) { [0]=> string(8) "CT-01-BK" [1]=> string(12) "teiki-descri" [2]=> string(13) "teiki-descri2" [3]=> string(9) "teiki-CTA" }
        // var_dump($data['css_ng_url_image_path_for_showing']);
        // var_dump($data['css_ng_local_image_path_for_showing']);
        // var_dump($data['css_script_document_of_each_images']);
        // die;
            $ng_count = 0;
            //HTMLのセレクタとスクレイピングしたセレクタの差し替え
            foreach($data['array_css_selector'] as $css_selector){
                $ng_count = $ng_count + 1;
                $search_css_selector = $css_selector.'"';
                $replace_css_selector = $css_selector.'"'." "."id=test_".$ng_count."_css";
                //ここでHTMLが差し替わっていない
                array_push($data['search_css_selector'], $search_css_selector);
                array_push($data['replace_css_selector'], $replace_css_selector);
                // $data['new_html_with_tracking_css'] = str_replace('teiki-descri2"', 'teiki-descri2" id=test_3_css', $data['new_html']);
            }

            $data['new_html_with_tracking_css'] = str_replace($data['search_css_selector'], $data['replace_css_selector'], $data['new_html']);

            // var_dump($data['array_css_selector']);
            // echo "<br>";
            // var_dump($data['new_html_with_tracking_css']);

            //スクレイピングしたCSSファイルごとにHTMLへ挿入する
            foreach($data['document_css'] as $document_css){
                //本家とサーバーの差し替え
                $new_css = str_replace(
                    $data['css_ng_url_image_path_for_showing'],
                    $data['css_ng_local_image_path_for_showing'],
                    $document_css
                );
                
                if ($document_css === reset($data['document_css'])) {
                // 最初のCSSファイル→最初に'<style>'をつける。
                    $style_html = '<style>'.$new_css;
                }
                
                $style_html = $style_html.$new_css;
                
                //最後のCSSファイル→エンド'</style>'でくくる。
                if ($document_css === end($data['document_css'])) {
                    $style_html = $style_html.
                    '</style>';

                    // HTMLファイルの下に挿入
                    $data['new_html_with_style'] = $data['new_html_with_tracking_css'].$style_html;
                }
            }
        }//endif(!empty($data['array_css_selector'])){

        //モーダル用の外部ファイル(スクリプトとCSS)読み込みの設定
        $search_modal = "<head>";
        $replace_modal = "
        <head>
        <script src='//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js'></script>
        <link rel='stylesheet' type='text/css' href='./style_modal.css'>
        ";

        if(!empty($data['new_html_with_style'])){
            $data['new_html_with_new_head'] = str_replace($search_modal, $replace_modal, $data['new_html_with_style']);
        }else{
            $data['new_html_with_new_head'] = str_replace($search_modal, $replace_modal, $data['new_html']);
        }
        //モーダルのスクリプトを埋め込む処理
        $data1 = $data['script_document_of_each_images'];
        $data2 = $data['css_script_document_of_each_images'];
        $data3 = array_merge($data1, $data2);

        $script_document = "";
        $script_document_all = "";
        foreach ($data3 as $scriput_function) {
            $script_document_all = $script_document_all.$scriput_function;
            if($scriput_function === end($data3)){
                $script_document_all = "<script>\n".$script_document_all."</script>";
              }
        }
        $data['new_html_with_script'] = $data['new_html_with_new_head']." ".$script_document_all;

        $this->load->view('thanks', $data);
	}

}
}
?>