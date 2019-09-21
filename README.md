
以下を記入＆インストール

venderファイルを../applicationにインストールしておく(composer)

------------------------------------------------------------------------------
application/config/development/api_key.php
//Google API
$config['google_api_key'] = 'ここに自分のAPIキーを入れる';

------------------------------------------------------------------------------
application/config/development/database.php
'dsn'	=> 'mysql:host=localhost;dbname=lp_check',
'username' => 'ユーザー名を記入',
'password' => 'パスワードを記入',
------------------------------------------------------------------------------
application/config/development/config.php
$config['base_url'] = 'ここにURLを入れてください';
------------------------------------------------------------------------------

