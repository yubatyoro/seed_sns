<?php
  //session_start();



  require('function.php');

//ログインチェック
  login_check();

  //DBの接続
  require('dbconnect.php');








//--------------------POST送信されていたらつぶやきをINSERTで保存------
// $_POST["tweet"] => "" $_POSTが空だと思われない
// $_POST["tweet"] => "" $_POST["tweet"]が空だと認識される


 if (isset($_POST) && !empty($_POST["tweet"])){
    //SQL文作成
    //tweet=つぶやいた内容
    //member_id=ログインした人のid
    //reply_tweet_id=-1
    //created=現在日時（now()を使用）
    //modified=現在日時（now()を使用）
    $sql = "INSERT INTO `tweets` (`tweet`, `member_id`, `reply_tweet_id`, `created`)VALUES(?,?,?,now())";

    //SQL文実行
        $data = array($_POST["tweet"],$_SESSION["id"],-1);
        $stmt = $dbh->prepare($sql);
        $stmt->execute($data);


        //自分の画面に移動する（データの再送信防止）
        header("Location: index.php");
        exit();
      }

 //----------------- ページング処理

  $page = "";
  //パラメータが存在していたらページ番号代入
  if (isset($_GET["page"])){
    $page = $_GET["page"];
  }else{
    //存在しない場合はページ番号を１とする
    $page = 1;
  }

  //1以下のイレギュラーな数字が入ってきたとき、ページ番号を強制的に１とする
  //max カンマ区切りで羅列された数字の中から最大の数字を取得
  $page = max($page,1);


  //1ページ分の表示件数
  $page_row = 5;

  //データ件数から最大ページ数を計算する
  $sql = "SELECT COUNT(*) AS `cnt` FROM `tweets` WHERE `delete_flag`=0";
  $page_stmt = $dbh->prepare($sql);
  $page_stmt->execute();

  $record_count = $page_stmt->fetch(PDO::FETCH_ASSOC);

  // ceil 小数点の切り上げ
  $all_page_number = ceil($record_count['cnt'] / $page_row);

  //パラメータのページ番号が最大番号を超えていれば強制的に最後のページにする
  //min カンマ区切りの数字の中から最小の数字を取得する
  $page = min($page,$all_page_number);

  //表示するデータの取得開始場所
  $start = ($page - 1) * $page_row;

  // -----------------------------------




  //------------表示用のデータ取得--------------------------
  try {
    //ログインしている人の情報を取得
    $sql = "SELECT * FROM `members` WHERE `member_id`=".$_SESSION["id"];


    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    $login_member = $stmt->fetch(PDO::FETCH_ASSOC);

    //一覧用の情報を取得
    // テーブル結合
    //ORDER BY `tweets`.`modified` DESC 最新順に並べ替え
    //論理削除に対応 delete_flag = 0のものだけを取得
    $sql = "SELECT `tweets`.*,`members`.`nick_name`,`members`.`picture_path` FROM `tweets` INNER JOIN `members` ON `tweets`.`member_id`=`members`.`member_id` WHERE `delete_flag`=0 ORDER BY `tweets`.`modified`  LIMIT ".$start.",".$page_row;


    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    //一覧表示用の配列を用意


    


    $tweet_list = array();
    // 複数のデータを取得するためのループ
    while (1) {
      $one_tweet = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($one_tweet == false){
        break;
      }else{
        //like数を求めるSQL文
        $like_sql ="SELECT COUNT(*)as`like_count` FROM `likes` WHERE `tweet_id`=".$one_tweet["tweet_id"];

        //SQL文実行
        $like_stmt = $dbh->prepare($like_sql);
        $like_stmt->execute();


        $like_number = $like_stmt->fetch(PDO::FETCH_ASSOC);

        // $one_tweetの中身
        // $one_tweet["tweet"]つぶやき
        // $one_tweet["member_id"] つぶやいた人のid
        // $one_tweet["nick_name"] つぶやいた人のニックネーム
        // $one_tweet["picture_path"] つぶやいた人のプロフィール画像
        // $one_tweet["modified"] つぶやいた日時

        //一行分のデータに新しいキーを用意して、like数を代入

        $one_tweet["like_count"] = $like_number["like_count"];


        //ログインしている人がlikeしているかどうかの情報を取得
        $login_like_sql = "SELECT COUNT(*) as`like_count` FROM `likes` WHERE `tweet_id`=".$one_tweet["tweet_id"]." AND `member_id`=".$_SESSION["id"];

        //SQL文の実行
        $login_like_stmt = $dbh->prepare($login_like_sql);
        $login_like_stmt->execute();

        //フェッチして取得
        $login_like_number = $login_like_stmt->fetch(PDO::FETCH_ASSOC);


        $one_tweet["login_like_flag"] = $login_like_number["like_count"];



        //データ取得ができている
        $tweet_list[] = $one_tweet;
      }
    }
    

  } catch (Exception $e) {
    
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
      <div class="col-md-4 content-margin-top">
        <legend>ようこそ<?php echo $login_member["nick_name"]; ?>さん！</legend>
        <form method="post" action="" class="form-horizontal" role="form">
            <!-- つぶやき -->
            <div class="form-group">
              <label class="col-sm-4 control-label">つぶやき</label>
              <div class="col-sm-8">
                <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"></textarea>
              </div>
            </div>
          <ul class="paging">
            <input type="submit" class="btn btn-info" value="つぶやく">
                &nbsp;&nbsp;&nbsp;&nbsp;
                <?php if ($page == 1){ ?>
                <li>前</li>
                <?php }else{ ?>
                <li><a href="index.php?page=<?php echo $page - 1; ?>" class="btn btn-default">前</a></li>
                <?php } ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <?php if ($page == $all_page_number){ ?>
                <li>次</li>
                <?php }else{ ?>
                <li><a href="index.php?page=<?php echo $page + 1; ?>" class="btn btn-default">次</a></li>
                <?php } ?>
                <li><?php echo $page; ?> / <?php echo $all_page_number; ?>Page</li>

          </ul>
        </form>
      </div>

      <div class="col-md-8 content-margin-top">
        <?php foreach ($tweet_list as $one_tweet) { 
          ?>

          <!-- 繰り返すタグが置かれる場所 -->
        <div class="msg">
          <a href="profile.php"><img src="picture_path/<?php echo $one_tweet["picture_path"]; ?>" width="48" height="48"></a>
          
          <p>
            <?php echo $one_tweet["tweet"]; ?><span class="name"> (<?php echo $one_tweet["nick_name"]; ?>)</span>
            [<a href="reply.php?tweet_id=<?php echo $one_tweet["tweet_id"]; ?>">Re</a>]



             <?php if ($one_tweet["login_like_flag"] == 0){ ?>
            <a href="like.php?like_tweet_id=<?php echo $one_tweet["tweet_id"]; ?>&page=<?php echo $page; ?>"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i>Like</a>
            <?php }else{ ?>
              <a href="like.php?unlike_tweet_id=<?php echo $one_tweet["tweet_id"]; ?>&page=<?php echo $page; ?>"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i>UnLike</a>
              <?php } ?>

              <?php if ($one_tweet["like_count"] > 0) {echo $one_tweet["like_count"];} ?>


          <p class="day">
            <a href="view.php?tweet_id=<?php echo $one_tweet["tweet_id"]; ?>">

              <?php
              $modify_date = $one_tweet["modified"];
              // strtotime 文字型のデータを日時型に変換できる
              $modify_date = date("Y-m-d H:i",strtotime($modify_date));

              echo $modify_date;

              ?>

            </a>
            <?php if ($_SESSION["id"] == $one_tweet["member_id"]){ ?>
            [<a href="edit.php?tweet_id=<?php echo $one_tweet["tweet_id"]; ?>" style="color: #00994C;">編集</a>]
            [<a onclick="return confirm('削除します、よろしいですか？');" href="delete.php?tweet_id=<?php echo $one_tweet["tweet_id"]; ?>"" style="color: #F33;">削除</a>]
            <?php } ?>
            <?php if($one_tweet["reply_tweet_id"]> 0){ ?>
            [<a href="view.php?tweet_id=<?php echo $one_tweet["reply_tweet_id"]; ?>"style="color: #a9a9a9;">返信元のメッセージを表示</a>]
            <?php } ?>
          </p>
        </div>


      <?php
        }
      ?>


    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="assets/js/jquery-3.1.1.js"></script>
    <script src="assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="assets/js/bootstrap.js"></script>
  </body>
</html>
