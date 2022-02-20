<?php
require_once('config.php');
require_once('functions.php');
session_start();

// セッションがない状態でアクセスした場合、index.phpに遷移する
if (!isset($_SESSION['project'])) {
    header('Location: '.SITE_URL);
    exit;
}

//DB接続
$pdo = connectDb();

// 候補リストアップ処理
  // candidatesテーブルでproject_idが一致するレコードを全て取得する
  $items = array();
  // セッション変数のprojectテーブルのidを取得
  $project_id = $_SESSION['project']['id'];

  $sql = "SELECT * FROM candidates WHERE project_id = :project_id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(":project_id" => $project_id));
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    // $itemsに取得したレコードを配列で格納する　$itemsはHTMLで出力する
      array_push($items, $row);
  }
unset($pdo);

// edit.phpの読み込み時の分岐処理
if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  // CSRF 対策
  setToken();

} else{
  // CSRF 対策
  checkToken();

  $pdo = connectDb();

  // index.phpから遷移してきた場合、特に何もしない
  // edit.phpの候補作成ボタンでedit.phpを再読み込みした場合
  if($_POST['type']=="create_candidate"){

    // edit.phpからPOSTされたcandidate_at_startを取得
      $start_time = $_POST['candidate_at_start_year']."/".
                    $_POST['candidate_at_start_month']."/".
                    $_POST['candidate_at_start_day']." ".
                    $_POST['candidate_at_start_hour'].":".
                    $_POST['candidate_at_start_minute'];

      // edit.phpからPOSTされたcandidate_at_endを取得
      $end_time = $_POST['candidate_at_end_year']."/".
                  $_POST['candidate_at_end_month']."/".
                  $_POST['candidate_at_end_day']." ".
                  $_POST['candidate_at_end_hour'].":".
                  $_POST['candidate_at_end_minute'];


    // 入力エラーチェック処理（関数を設定してエラーを返す）
    $err = array(); // エラー時のメッセージ用変数
    $complete_msg = ""; // 成功時のメッセージ用変数

    // エラー：存在しない日付が含まれている
    if (!checkdate($_POST['candidate_at_start_month'], $_POST['candidate_at_start_day'], $_POST['candidate_at_start_year'])) {
      $err['candidate_at'] = "開始時刻に存在しない日付が含まれています。";
    }
    if (!checkdate($_POST['candidate_at_end_month'], $_POST['candidate_at_end_day'], $_POST['candidate_at_end_year'])) {
      $err['candidate_at'] = "終了時刻に存在しない日付が含まれています。";
    }

    // エラー：候補の開始時刻が終了時刻より遅ければ、時刻の設定が不適切とする
    if($start_time >= $end_time){
      $err['candidate_at'] ='候補の時間設定が適切ではありません。';
    }

    // エラーがない場合
    if(empty($err)){
      // candidatesテーブルにレコードを新規登録する
      $sql = "INSERT INTO candidates(project_id, candidate_at_start, candidate_at_end, created_at, updated_at)
              VALUES (:project_id, :candidate_at_start, :candidate_at_end, now(), now())";
      $stmt= $pdo->prepare($sql);
      $stmt->execute(array(
                           ":project_id" => $project_id,
                           ":candidate_at_start" => $start_time,
                           ":candidate_at_end" => $end_time
                           ));
      $complete_msg = "候補が更新されました。";

    }

    // 候補リストアップ処理
      // candidatesテーブルでproject_idが一致するレコードを全て取得する
      $items = array();
      $sql = "SELECT * FROM candidates WHERE project_id = :project_id";
      $stmt = $pdo->prepare($sql);
      $stmt->execute(array(":project_id" => $project_id));
      foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        // $itemsに取得したレコードを配列で格納する　$itemsはHTMLで出力する
          array_push($items, $row);
      }

      // プロジェクトの更新
      $sql = "UPDATE project SET updated_at = now() WHERE id = :id";
      $stmt = $pdo->prepare($sql);
      $stmt->execute(array(":id" => $project_id));

      unset($pdo);

  // edit.phpの予定登録ボタンでedit.phpを再読み込みした場合
  } elseif($_POST['type'] == "register_candidate"){

    $pdo = connectDb();

    $project_id = $_SESSION['project']['id'];

    // 参加締切時刻のデータを変数に格納
    $deadline = $_POST['deadline_year']."/".
                $_POST['deadline_month']."/".
                $_POST['deadline_day']." ".
                $_POST['deadline_hour'].":".
                $_POST['deadline_minute'].":".
                "00";

    // 入力エラーチェック処理（関数を設定してエラーを返す）
    $err = array(); // エラー時のメッセージ用変数
    $complete_msg = ""; // 成功時のメッセージ用変数

    // エラー：存在しない日付が含まれている
    if (!checkdate($_POST['deadline_month'], $_POST['deadline_day'], $_POST['deadline_year'])) {
      $err['candidate_at'] = "締切時刻に存在しない日付が含まれています。";
    }


    if(empty($err)){
     // projectテーブル内のproject_idが合致するレコードのdeadlineを更新する
     $sql = "UPDATE project SET deadline = :deadline WHERE id = :project_id";
     $stmt = $pdo->prepare($sql);
     $stmt->execute(array(":deadline" => $deadline, ":project_id" => $project_id));

     // プロジェクトの更新
     $sql = "UPDATE project SET updated_at = now() WHERE id = :id";
     $stmt = $pdo->prepare($sql);
     $stmt->execute(array(":id" => $project_id));

     // edit_complete.phpに遷移する
     unset($pdo);
     header('Location: '.SITE_URL.'edit_complete.php');
     exit;
   }
  }

}


?>

<!DOCTYPE html>
<html lang="jp">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="ログイン不要の多人数向けマッチングアプリ" />
        <meta name="author" content="GroupMatching" />
        <title>予定設定画面｜Group Scheduler</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <!-- Font Awesome icons (free version)-->
        <script src="https://use.fontawesome.com/releases/v5.15.4/js/all.js" crossorigin="anonymous"></script>
        <!-- Google fonts-->
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
        <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" type="text/css" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="css/styles.css" rel="stylesheet" />
    </head>
    <body id="page-top">
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav" style="background-color:black;">
            <div class="container">
              <a class="navbar-brand" href="index.php">GroupScheduler</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu
                    <i class="fas fa-bars ms-1"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                        <!-- <li class="nav-item"><a class="nav-link" href="index.php#services">How to Use</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li> -->
                    </ul>
                </div>
            </div>
        </nav>

<!-- 以下、このページのコンテンツ -->
        <section class="page-section">
            <div class="container text-center d-grid gap-2 col-6 mx-auto">
                <div class="mb-3">
                    <h2 class="mt-4 section-heading text-uppercase">予定設定画面</h2>
                    <h6 class="text-muted">
                      予定候補を入力し、「予定登録」ボタンを押してください。<br>
                      その後、参加締切時刻を入力し、「予定登録」ボタンを押してください。
                    </h6>
                </div>

                <!-- 候補作成成否の通知 -->
                <span class="text-danger"><?php echo h($err['candidate_at']); ?></span>
                <span class="text-success"><?php echo h($complete_msg); ?></span>

                <form class="form" method="post"><!-- 候補作成用フォーム -->
                    <!-- 候補の開始時刻と終了時刻をPOSTする -->
                    <!-- プルダウンメニュー配置-->
                    <?php
                      // オプションの配列の設定
                        $candidate_years_array = CANDIDATE_YEARS_ARRAY;
                        $candidate_months_array = CANDIDATE_MONTHS_ARRAY;
                        $candidate_days_array = CANDIDATE_DAYS_ARRAY;
                        $candidate_hours_array = CANDIDATE_HOURS_ARRAY;
                        $candidate_minutes_array = CANDIDATE_MINUTES_ARRAY;
                    ?>

                    <div class="col-12">
                      予定開始時刻：
                      <?php echo arrayToSelect("candidate_at_start_year", $candidate_years_array); ?>年
                      <?php echo arrayToSelect("candidate_at_start_month", $candidate_months_array); ?>月
                      <?php echo arrayToSelect("candidate_at_start_day", $candidate_days_array); ?>日
                      <?php echo arrayToSelect("candidate_at_start_hour", $candidate_hours_array); ?>時
                      <?php echo arrayToSelect("candidate_at_start_minute", $candidate_minutes_array); ?>分
                    </div>
                    <div class="col-12">
                      予定終了時刻：
                      <?php echo arrayToSelect("candidate_at_end_year", $candidate_years_array); ?>年
                      <?php echo arrayToSelect("candidate_at_end_month", $candidate_months_array); ?>月
                      <?php echo arrayToSelect("candidate_at_end_day", $candidate_days_array); ?>日
                      <?php echo arrayToSelect("candidate_at_end_hour", $candidate_hours_array); ?>時
                      <?php echo arrayToSelect("candidate_at_end_minute", $candidate_minutes_array); ?>分
                    </div>

                    <!-- CSRF対策：次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
                    <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

                    <!-- フォーム識別用input -->
                    <input type="hidden" name="type" value="create_candidate" />

                    <div class="mt-3 d-grid gap-2 col-3 mx-auto">
                      <button type="submit" class="btn btn-primary">候補作成</button>
                    </div>

                    <!-- CSRF対策：次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
                    <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

                </form>

                <form class="form" method="post"><!-- 候補登録用フォーム -->
                  <!-- 候補リスト -->
                  <ol class="list-group list-group-numbered">

                    <?php foreach ($items as $item): ?>
                      <li class="list-group-item">
                          <!-- ここに時間帯を出力 -->
                          　<?php echo h(substr($item['candidate_at_start'], 0, -3)); ?>　〜　<?php echo h(substr($item['candidate_at_end'], 0, -3)); ?>　　
                          <a href="javascript:void(0);" class="btn btn-secondary" onclick="var ok=confirm('削除しても宜しいですか?');
                          if (ok) location.href='delete.php?id=<?php echo $item['id']; ?>'; return false;">削除</a>
                      </li>
                    <?php endforeach; ?>
                  </ol>

                  <div class="row" style="font-size:1.2rem;">

                      <div class="my-3 py-2 col-12">
                        参加締切時刻：
                        <?php echo arrayToSelect("deadline_year", $candidate_years_array); ?>年
                        <?php echo arrayToSelect("deadline_month", $candidate_months_array); ?>月
                        <?php echo arrayToSelect("deadline_day", $candidate_days_array); ?>日
                        <?php echo arrayToSelect("deadline_hour", $candidate_hours_array); ?>時
                        <?php echo arrayToSelect("deadline_minute", $candidate_minutes_array); ?>分
                      </div>

                  </div><!-- row -->

                  <!-- CSRF対策：次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
                  <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

                  <!-- フォーム識別用input -->
                  <input type="hidden" name="type" value="register_candidate" />

                  <div class="d-grid gap-2 col-3 mx-auto">
                    <button class=" my-4 btn btn-primary btn-lg" type="submit" name="">予定登録</button>
                  </div>

                </form>

                <a href="index.php" class="link-primary" style="font-size:1.2rem">トップページへ戻る</a>

            </div><!-- container -->
        </section>
        <!-- Footer-->
        <footer class="footer py-4">
            <div class="container">
                <div class="row align-items-center">
                  <div class="col-lg-4 text-lg-start">Copyright &copy; <?php echo COPY_RIGHT; ?></div>
                </div>
            </div>
        </footer>



        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="js/scripts.js"></script>
        <!-- * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
        <!-- * *                               SB Forms JS                               * *-->
        <!-- * * Activate your form at https://startbootstrap.com/solution/contact-forms * *-->
        <!-- * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
        <script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>
    </body>
</html>
