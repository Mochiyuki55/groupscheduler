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
// セッションからproject_idを取得する
$project_id = $_SESSION['project']['id'];
// projectテーブルから管理用アクセスキーと参加用アクセスキーを取得する
$sql = "SELECT * FROM project WHERE id = :project_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":project_id" => $project_id));
$keys = $stmt->fetch(PDO::FETCH_ASSOC);

$manage_key = $keys['manage_key'];
$attend_key = $keys['attend_key'];

// edit_complete.phpを表示したら、セッションをクリアする
$_SESSION=array();
session_destroy();

unset($pdo);

?>

<!DOCTYPE html>
<html lang="jp">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="ログイン不要の多人数向けマッチングアプリ" />
        <meta name="author" content="GroupMatching" />
        <title>予定入力完了｜Group Scheduler</title>
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
            <div class="container">
                <div class="text-center">
                    <h2 class="mt-4 section-heading text-uppercase">予定設定完了</h2>
                    <h3 class="section-subheading text-muted">以下のURLにアクセスし、アクセスキーを入力してください。<br>
                    参加者にはトップページのURLと参加用アクセスキーを通知してください。</h3>
                </div>
                <div class="text-center">
                  <div class="mb-5">
                      <h4>アクセス用URL</h4>
                      <h5 ><span id="span_1"><?php echo SITE_URL; ?></span>　　<button class="btn btn-info" type="button" onclick="copyToClipBoard()">コピー</button></h5>
                  </div>

                  <div class="mb-5">
                      <h4>管理用アクセスキー</h4>
                      <h5><span id="span_2"><?php echo h($manage_key); ?></span>　　<button class="btn btn-info" type="button" onclick="copyToClipBoard_2()">コピー</button></h5>
                  </div>

                  <div class="mb-5">
                      <h4>参加用アクセスキー</h4>
                      <h5><span id="span_3"><?php echo h($attend_key); ?></span>　　<button class="btn btn-info" type="button" onclick="copyToClipBoard_3()">コピー</button></h5>
                  </div>

                  <div>
                    <a href="index.php" style="font-size:1.5rem">トップページへ戻る</a>
                  </div>

                </div>




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

        <script>
          function copyToClipBoard() {
            //範囲を指定
            let range = document.createRange();
            let span = document.getElementById('span_1');
            range.selectNodeContents(span);

            //指定した範囲を選択状態にする
            let selection = document.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);

            //コピー
            document.execCommand('copy');
            alert('URLをコピーしました');
          }
          function copyToClipBoard_2() {
            let range = document.createRange();
            let span = document.getElementById('span_2');
              range.selectNodeContents(span);
            let selection = document.getSelection();
              selection.removeAllRanges();
              selection.addRange(range);
            document.execCommand('copy');
              alert('管理用アクセスキーをコピーしました');
          }
          function copyToClipBoard_3() {
            let range = document.createRange();
            let span = document.getElementById('span_3');
              range.selectNodeContents(span);
            let selection = document.getSelection();
              selection.removeAllRanges();
              selection.addRange(range);
            document.execCommand('copy');
              alert('参加用アクセスキーをコピーしました');
          }
        </script>

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
