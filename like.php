<?php

    session_start();



    //likeボタンが押されたとき
    if (isset($_GET["like_tweet_id"])){
      //like情報をlikesテーブルに登録
      like($_GET["like_tweet_id"],$_SESSION["id"]);
    //  $sql = "INSERT INTO `likes` (`tweet_id`, `member_id`) VALUES (".$_GET["like_tweet_id"].", ".$_SESSION["id"].");";

    ////SQL実行
    //$stmt = $dbh->prepare($sql);
    //$stmt->execute();

    ////一覧ページに戻る
    //header("Location: index.php");

    }


    //unlikeボタンが押されたとき
    if (isset($_GET["unlike_tweet_id"])){
    //登録されているlike情報をlikesテーブルに削除
    unlike($_GET["unlike_tweet_id"],$_SESSION["id"]);
    //$sql = "DELETE FROM `likes` WHERE tweet_id=".$_GET["unlike_tweet_id"]." AND member_id=".$_SESSION["id"];
//
    ////SQL実行
    //$stmt = $dbh->prepare($sql);
    //$stmt->execute();
//
    ////一覧ページに戻る
    //header("Location: index.php");


    }

    //like関数
    // 引数　like_tweet_id
    function like($like_tweet_id,$login_member_id){

      require('dbconnect.php');

    $sql = "INSERT INTO `likes` (`tweet_id`, `member_id`) VALUES ( ".$like_tweet_id.", ".$login_member_id.");";

    //SQL実行
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    //一覧ページに戻る
    header("Location: index.php");

    }

    //unlike関数
    function unlike($unlike_tweet_id,$login_member_id){

    require('dbconnect.php');


    $sql = "DELETE FROM `likes` WHERE tweet_id=".$unlike_tweet_id." AND member_id=".$login_member_id;

    //SQL実行
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    //一覧ページに戻る
    header("Location: index.php");

    }


?>