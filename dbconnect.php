<?php 
    // var_dump 変数の中身を表示する
    // Undefined index: code が表示されている場合
    // POST送信がされていない

    // エラーが表示されていないときはPOST送信されている
    // var_dump($_POST["code"]);
    // 扱いやすいように変数に代入

// ステップ１　データーベースに接続する
// データーベース接続文字列
// mysql:接続するデーターベースの種類
// dbname データーベース名
// host パソコンのアドレス　localhost このプログラムが存在している場所と同じサーバー
// 空欄を入れないように記述するルール
// 開発環境用
    $dsn = 'mysql:dbname=seed_sns;host=localhost';

    // $user データベース接続用ユーザー名
    // $passward データベース接続用ユーザーのパスワード    
    $user = 'root';
    $password='';


// // 本番環境
//     $dsn = 'mysql:dbname=LAA0918700-phpkiso;host=mysql103.phy.lolipop.lan';

//     // $user データベース接続用ユーザー名
//     // $passward データベース接続用ユーザーのパスワード    
//     $user = 'LAA0918700';
//     $password='yubacebu';



    // データベース接続オブジェクト
    $dbh = new PDO($dsn, $user, $password);

    //例外処理を使用可能にする方法(エラー文を表示することができる)
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 今から実行するSQL文を文字コードutf8 で送るという設定
    $dbh->query('SET NAMES utf8');

    ?>