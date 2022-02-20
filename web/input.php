<?php
require_once('config.php');
require_once('functions.php');
session_start();

// セッションがない状態でアクセスした場合、index.phpに遷移する
if (!isset($_SESSION['project'])) {
    header('Location: '.SITE_URL);
    exit;
}

// DB接続
$pdo = connectDb();

// 候補リストアップ処理
  $candidates = array();
  // セッション変数のprojectテーブルのidを取得
  $project_id = $_SESSION['project']['id'];
  $member_name = $_SESSION['member']['member_name'];

  // candidateテーブルから各候補の時間帯を取得
  $sql = "SELECT * FROM candidates WHERE project_id = :project_id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(":project_id" => $project_id));
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    // $itemsに取得したレコードを配列で格納する　$itemsはHTMLで出力する
      array_push($candidates, $row);
  }
  // projectテーブルから各候補の時間帯を取得
  $deadline = $_SESSION['project']['deadline'];

// index.phpの読み込み時の分岐処理
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF 対策
  setToken();

} else{
  // 参加登録を入力してPOSTされた場合
  // CSRF 対策
  checkToken();

  //POSTされた参加可否のラジオボタンの、name配列を取得する
  $replies=$_POST['attend_flag'];

  //エラーチェック
  $err = "";
  foreach ($candidates as $candidate) {
    $f = $replies[$candidate['id']];
    if(!isset($f)){
      $err = "未入力の項目があります。";
    }
  }

  if($err == ""){
    // 各候補について、candidate_memberテーブルに新規登録する。
    foreach ($replies as $reply => $value) {
      // すでにcandidate-memberテーブルに登録されているかを確認
      $sql = "SELECT * FROM candidate_member WHERE candidate_id = :candidate_id AND member_id = :member_id";
      $stmt = $pdo->prepare($sql);
      $stmt->execute(array(":candidate_id" => $reply, ":member_id" => $_SESSION['member']['id']));
      $check = $stmt->fetch();

      // 登録されていれば、そのレコードのattend_flagを更新する
      if($check){
        $sql = "UPDATE candidate_member SET attend_flag = :attend_flag WHERE candidate_id = :candidate_id AND member_id = :member_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(":candidate_id"=>$reply, ":member_id" => $_SESSION['member']['id'], ":attend_flag" => $value));

        // 登録されていなければ、参加可否をcandidate_memberテーブルに新規登録
      }else{
        $sql = "INSERT INTO candidate_member (candidate_id, member_id, attend_flag) VALUES (:candidate_id, :member_id, :attend_flag)";
        $stmt= $pdo->prepare($sql);
        $stmt->execute(array(":candidate_id"=>$reply, ":member_id" => $_SESSION['member']['id'], ":attend_flag" => $value));
      }
    }

    // 登録完了メッセージを用意
    $complete_msg = "参加可否の登録が完了しました。";

    // プロジェクトの更新
    $sql = "UPDATE project SET updated_at = now() WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":id" => $project_id));

  }

}


unset($pdo);

?>

<!DOCTYPE html>
<html lang="jp">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="ログイン不要の多人数向けマッチングアプリ" />
        <meta name="author" content="GroupMatching" />
        <title>予定入力画面｜Group Scheduler</title>
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
                <div class="text-center">
                    <h2 class="mt-4 section-heading text-uppercase">予定入力画面</h2>
                    <h3 class="section-subheading text-muted"><?php echo h($member_name); ?>さん、参加可能な時間帯にチェックを入れ、登録ボタンを押してください。</h3>
                </div>
                <span class="text-danger"><?php echo h($err); ?></span>
                <span class="text-success"><?php echo h($complete_msg); ?></span>

                <form class="form-check" action="" method="post">
                    <div class="text-center" style="background-color:orange;font-size:1.2rem;">
                      <h4>締切日時：<?php echo h(substr($deadline, 0,-3)); ?></h4>
                    </div>

                    <!-- 候補リスト -->
                    <ol class="list-group list-group-numbered">
                      <?php foreach ($candidates as $candidate): ?>
                        <li class="list-group-item">
                            <!-- ここに時間帯を出力 -->
                            　<?php echo h(substr($candidate['candidate_at_start'], 0, -3)); ?>　〜　<?php echo h(substr($candidate['candidate_at_end'], 0, -3)); ?>　　
                              <label><input type="radio" name="attend_flag[<?php echo h($candidate['id']); ?>]" value="1"> 参加可能　</label>
                              <label><input type="radio" name="attend_flag[<?php echo h($candidate['id']); ?>]" value="0"> 参加不可 </label>
                        </li>
                      <?php endforeach; ?>
                    </ol>

                  <div class="d-grid gap-2 col-4 mx-auto">
                    <button class=" my-4 btn btn-primary btn-lg" type="submit" name="">参加登録</button>
                  </div>

                  <!-- CSRF対策：次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
                  <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

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
