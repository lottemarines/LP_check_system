<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>LPチェック</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
</head>
<body>

<div id="container">
	<h1>URLとジャンルを入力してください</h1>
	<?php echo validation_errors(); ?>
    <?php echo form_open(''); ?>
    <h3>大～中ジャンルを選択</h3>
    	<select name="genre_id">
            <option selected="selected" value="">ジャンルを選択</option>
            <optgroup label="車">
            <option value="1">車買取</option>
            <option value="2">中古車販売</option>
            <option value="3">車用品、メンテナンス</option>
            </optgroup>
            <optgroup label="金融">
            <option value="4">金融（キャッシング・カードローン）</option>
            <option value="5">金融（クレジットカード）</option>
            <option value="6">金融（証券・FX・その他）</option>
            <option value="7">金融（その他ローン）</option>
            <option value="8">金融（保険）</option>
            <option value="9">自動車保険</option>
            <option value="10">保険</option>
            </optgroup>
            <optgroup label="不動産・家まわり">
            <option value="11">不動産（売却）</option>
            <option value="12">不動産（リフォーム）</option>
            <option value="13">不動産（賃貸）</option>
            <option value="14">不動産（購入）</option>
            <option value="15">不動産（不動産投資）</option>
            <option value="16">不動産（その他）</option>
            <option value="17">リフォーム</option>
            <option value="18">エアコン工事</option>
            </optgroup>
            <optgroup label="求人">
            <option value="19">転職求人</option>
            <option value="20">転職（看護師）</option>
            <option value="21">転職（薬剤師）</option>
            <option value="22">転職（医師）</option>
            <option value="23">転職（保育士・介護士）</option>
            <option value="24">転職（一般）</option>
            <option value="25">転職（アルバイト）</option>
            <option value="26">転職（派遣）</option>
            </optgroup>
            <optgroup label="生活">
            <option value="27">弁護士・司法書士・税理士</option>
            <option value="28">探偵</option>
            <option value="29">旅行</option>
            <option value="30">学び</option>
            <option value="31">結婚</option>
            <option value="32">出会い系</option>
            <option value="33">水・ウォーターサーバー</option>
            <option value="34">VOD</option>
            <option value="35">生活トラブル</option>
            <option value="36">買取</option>
            <option value="37">引越し</option>
            <option value="38">レンタルサーバー・IT</option>
            </optgroup>
            <optgroup label="美容・健康">
            <option value="39">健康食品</option>
            <option value="40">サプリ</option>
            <option value="41">サロン・店舗</option>
            <option value="42">スキンケア</option>
            <option value="43">ダイエット</option>
            <option value="44">ボディケア</option>
            <option value="45">ヘアケア</option>
            <option value="46">歯</option>
            <option value="47">コスメ</option>
            <option value="48">その他 /　美容商品</option>
            <option value="49">その他 /　ヘルス・ビューティー</option>
            </optgroup>
            <optgroup label="通販">
            <option value="50">ファッション</option>
            <option value="51">食・グルメ</option>
            <option value="52">カニ通販・おせち</option>
            <option value="53">ペット</option>
            <option value="54">その他EC</option>
            <option value="55">悩み・コンプレックス</option>
            
            </optgroup>
        </select>
        <br>
        <h4>小ジャンルを選択（任意）</h4>
        <select class="children" name="genre_sub_id">
            <option selected="selected" value="0">小ジャンルを選択</option>
            <option class="36" value="1" display:none>一般買取</option>
            <option class="36" value="2">ピアノ買取</option>
            <option class="36" value="3">バイク買取</option>
            <option class="39" value="4">青汁</option>
            <option class="39" value="5">その他健康食品</option>
            <option class="40" value="6">酵素</option>
            <option class="40" value="7">葉酸</option>
            <option class="40" value="8">乳酸菌</option>
            <option class="40" value="9">水素</option>
            <option class="40" value="10">筋肉系</option>
            <option class="40" value="11">エイジングケア</option>
            <option class="40" value="12">ダイエット</option>
            <option class="41" value="13">エステ</option>
            <option class="41" value="14">脱毛（サロン）</option>
            <option class="41" value="15">脱毛（医療）</option>
            <option class="41" value="16">脱毛（その他）</option>
            <option class="41" value="17">ジム、ヨガ</option>
            <option class="42" value="18">洗顔クレンジング</option>
            <option class="42" value="19">基礎化粧品</option>
            <option class="42" value="20">化粧品</option>
            <option class="42" value="21">クリーム・乳液</option>
            <option class="42" value="22">美容液</option>
            <option class="42" value="23">オールインワン</option>
            <option class="43" value="24">ダイエット食品</option>
            <option class="43" value="25">置き換え食品</option>
            <option class="43" value="26">ダイエットグッズ</option>
            <option class="44" value="27">黒ずみ</option>
            <option class="44" value="28">デオドラント</option>
            <option class="44" value="29">ニキビ</option>
            <option class="44" value="30">汗系</option>
            <option class="44" value="31">バストケア</option>
            <option class="47" value="32">ファンデーション</option>
            <option class="47" value="33">その他コスメ</option>
            <option class="50" value="34">一般ファッション</option>
            <option class="50" value="35">着圧系</option>
            <option class="50" value="36">アクセサリー</option>
            <option class="55" value="37">背が伸びる</option>
            <option class="55" value="38">関節痛</option>
            <option class="55" value="39">精力剤</option>
            <option class="55" value="40">育毛</option>
            <option class="55" value="41">その他の悩み</option>
        </select>
        <br>
        <br>
        <h3>URLを記入</h3>
		<input type="text" id="url_name" name="url_name" value="">
		<br>
		<br>
	<input type="submit" name="" value="送信する">
	</form>
</div>



</body>
</html>

<style>
select{
	font-size:25px;
}
</style>

<script>
//最初はセレクトを消しておく
$('.36, .39, .40, .41, .42, .43, .44, .47, .50, .55').hide();

//セレクトに変化があったらファンクションを開始する
	$('[name=genre_id]').change(function() {
		var genre_id = $('[name=genre_id] option:selected').val();
		if(genre_id == "36"){
			$(".36").show();
			$('.39, .40, .41, .42, .43, .44, .47, .50, .55').hide();
		}else if(genre_id == "39"){
			$(".39").show();
			$('.36, .40, .41, .42, .43, .44, .47, .50, .55').hide();
		}else if(genre_id == "40"){
			$(".40").show();
			$('.36, .39, .41, .42, .43, .44, .47, .50, .55').hide();
		}else if(genre_id == "41"){
			$(".41").show();
			$('.36, .39, .40, .42, .43, .44, .47, .50, .55').hide();
		}else if(genre_id == "42"){
			$(".42").show();
			$('.36, .39, .40, .41, .43, .44, .47, .50, .55').hide();
		}else if(genre_id == "43"){
			$(".43").show();
			$('.36, .39, .40, .41, .42, .44, .47, .50, .55').hide();
		}else if(genre_id == "44"){
			$(".44").show();
			$('.36, .39, .40, .41, .42, .43, .47, .50, .55').hide();
		}else if(genre_id == "47"){
			$(".47").show();
			$('.36, .39, .40, .41, .42, .43, .44, .50, .55').hide();
		}else if(genre_id == "50"){
			$(".50").show();
			$('.36, .39, .40, .41, .42, .43, .44, .47, .55').hide();
		}else if(genre_id == "55"){
			$(".55").show();
			$('.36, .39, .40, .41, .42, .43, .44, .47, .50').hide();
		}
		else{
			//関係のないセレクタを消す
			$('.36, .39, .40, .41, .42, .43, .44, .47, .50, .55').hide();
			//大ジャンルのセレクタが再度入力された時に、値を初期値へリセットする
			$('[name=genre_sub_id]').val(0);
		}

	});

</script>