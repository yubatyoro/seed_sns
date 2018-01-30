<?php


//tagsテーブルに今付けられたタグが存在するかチェック（なかったら追加）
function exists_tag($tag,$dbh){


  //tagsテーブルへ存在するかチェックするSQLを作成
  $tag_sql = "SELECT COUNT(*) AS `cnt` FROM `tags` WHERE `tag`=?";

  $data = array($tag);

  //SQL実行
  $stmt = $dbh->prepare($tag_sql);
  $stmt->execute($data);


  //フェッチ
  $tag_count = $stmt->fetch(PDO::FETCH_ASSOC);

  //存在しなかったら追加
  if ($tag_count["cnt"] == 0){
    //tagsテーブルへデータ追加するSQL文作成
    $tag_create_sql = "INSERT INTO `tags` (`tag`) VALUES (?);";


    //SQL実行
    $create_stmt = $dbh->prepare($tag_create_sql);
    $create_stmt->execute($data);
  }



}

//それぞれのハッシュタグのidをtagsテーブルから探して保存


//tweet_tagsテーブルへ登録




?>