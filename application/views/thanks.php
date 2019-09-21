<?php
//NGがある場合の表示データ
print_r($new_html_with_script);

//NGがない場合に入力URLを表示する
if(empty($new_html_with_script)){
	header( "Location:".$url_name );
	exit ;
}