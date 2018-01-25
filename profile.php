<?php
  session_start();

  // DBの接続
  require('dbconnect.php');

  // GET送信された、member_idをつかって、プロフィール情報をmembersテーブルから取得
  $sql = "SELECT * FROM `members` WHERE `member_id`=".$_GET["member_id"];

  $stmt = $dbh->prepare($sql);
  $stmt->execute();

  $profile_member = $stmt->fetch(PDO::FETCH_ASSOC);

  //一覧データを取得
  $sql = "SELECT `tweets`.*,`members`.`nick_name`,`members`.`picture_path` FROM `tweets` INNER JOIN `members` ON `tweets`.`member_id`=`members`.`member_id` WHERE `delete_flag`=0 AND `tweets`.`member_id`=".$_GET["member_id"]." ORDER BY `tweets`.`modified` DESC ";

  $stmt = $dbh->prepare($sql);
  $stmt->execute();

  // 一覧表示用の配列を用意
  $tweet_list = array();

  //　複数行のデータを取得するためループ
  while (1) {
    $one_tweet = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($one_tweet == false){
      break;
    }else{
      //データが取得できている
      $tweet_list[] = $one_tweet;
    }
  }

  //自分もフォローしていたら１、フォローしていなかったら０を代入する
      //相互フォロー
      $fl_flag_sql = "SELECT COUNT(*) as `cnt` FROM `follows` WHERE `member_id`=".$_SESSION["id"]." AND `follower_id`=".$_GET["member_id"];
      $fl_stmt = $dbh->prepare($fl_flag_sql);
      $fl_stmt->execute();
      $fl_flag = $fl_stmt->fetch(PDO::FETCH_ASSOC);



//フォロー処理
// profile.php?follow_id=7 というリンクが推された＝フォローボタンが押された
  if (isset($_GET["follow_id"])){
    //follow情報を記録するSQL文を作成
    $sql = "INSERT INTO `follows` (`member_id`, `follower_id`) VALUES (?, ?);";
    $data = array($_SESSION["id"],$_GET["follow_id"]);
    $fl_stmt = $dbh->prepare($sql);
    $fl_stmt->execute($data);

    //フォローを押す前の状態に戻す
    header("Location: profile.php?member_id=".$_GET["member_id"]);
  }

//フォロー解除処理
    if (isset($_GET["unfollow_id"])) {
    $sql = "DELETE FROM `follows` WHERE `member_id`=? AND `follower_id`=?";
    $data = array($_SESSION["id"],$_GET["unfollow_id"]);
    $unfl_stmt = $dbh->prepare($sql);
    $unfl_stmt->execute($data);

    //フォロー解除を押す前の状態に戻す
    header("Location: profile.php?member_id=".$_GET["member_id"]);

  }


?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SeedSNS</title>

    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/form.css" rel="stylesheet">
    <link href="assets/css/timeline.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">

  </head>
  <body>
  <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header page-scroll">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.html"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
                <li><a href="logout.php">ログアウト</a></li>
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-3 content-margin-top">
        <img src="picture_path/<?php echo $profile_member["picture_path"]; ?>" width="250" height="250">
        <h3><?php echo $profile_member["nick_name"]; ?></h3>
        <?php if($_SESSION["id"] != $profile_member["member_id"]){ ?>

        <?php if ($fl_flag["cnt"] == 0){ ?>
        <a href="profile.php?member_id=<?php echo $profile_member["member_id"]; ?>&follow_id=<?php echo $profile_member["member_id"]; ?>">
        <button class="btn btn-block btn-default">フォロー</button></a>

        <?php }else{ ?>





        <a href="profile.php?member_id=<?php echo $profile_member["member_id"]; ?>&unfollow_id=<?php echo $profile_member["member_id"]; ?>">
        <button class="btn btn-block btn-default">フォロー解除</button></a>
        <?php } ?>
        <?php } ?>
        <br>
        <a href="index.php">&laquo;&nbsp;一覧へ戻る</a>
      </div>
      <div class="col-md-9 content-margin-top">
        <?php foreach ($tweet_list as $one_tweet) { ?>  
        <div class="msg">
          <img src="picture_path/<?php echo $one_tweet["picture_path"]; ?>" width="100" height="100">
          <p>投稿者 : <span class="name"> <?php echo $one_tweet["nick_name"]; ?> </span></p>
          <p>
            つぶやき : <br>
            <?php echo $one_tweet["tweet"]; ?>
          </p>
          <p class="day">
            <?php 
              $modify_date = $one_tweet["modified"];
              // strtotime 文字型のデータを日時型に変換できる
              $modify_date = date("Y-m-d H:i",strtotime($modify_date));

              echo $modify_date; 

              ?>
            <?php if ($_SESSION["id"] == $one_tweet["member_id"]){ ?>
            [<a onclick="return confirm('削除します、よろしいですか？');" href="delete.php?tweet_id=<?php echo $one_tweet["tweet_id"]; ?>" style="color: #F33;">削除</a>]
            <?php } ?>
          </p>
        </div>
        <?php } ?>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="assets/js/jquery-3.1.1.js"></script>
    <script src="assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="assets/js/bootstrap.js"></script>
  </body>
</html>
