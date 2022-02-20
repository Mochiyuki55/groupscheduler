<?php
require_once('config.php');
require_once('functions.php');
session_start();
session_regenerate_id(true);

// DB接続
$pdo = connectDb();

// index.phpの読み込み時の分岐処理
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // 初めて画面にアクセスする際の処理
  // CSRF 対策
  setToken();

} else{
  // アクセスキーを入力してPOSTされた場合
  // CSRF 対策
  checkToken();

  if($_POST['type'] == "create"){
  // 新規登録ボタンを押した場合、プロジェクト開始
    // projectテーブルでダミーレコードを新規登録する
      // 新規追加したダミーレコードを特定できるように、manage_key, attend_keyにランダムな値を入れる(あとで更新する)
      $manage_key = "mng:".uniqid();
      $attend_key = "atd:".uniqid();
      $sql = "INSERT INTO project (deadline, manage_key, attend_key, created_at, updated_at) VALUES (now(), :manage_key, :attend_key, now(), now())";
      $stmt= $pdo->prepare($sql);
      $stmt->execute(array(":manage_key"=>$manage_key, ":attend_key" => $attend_key));

      // さらに、新規登録したレコードのid(project_id)をセッションに格納する
      $sql = "SELECT * FROM project WHERE manage_key = :manage_key";
      $stmt = $pdo->prepare($sql);
      $stmt->execute(array(":manage_key"=>$manage_key));
      $project = $stmt->fetch(PDO::FETCH_ASSOC);
      // この$projectにはレコード内のカラム全てが入っている。
      $_SESSION['project']=$project;

    // edit.phpに画面遷移する。
    header('Location: '.SITE_URL.'edit.php');
    exit;

  } else {
  // アクセスボタンを押した場合

    // POSTされたデータを変数に格納
    $member_name = $_POST['member_name'];
    $access_key = $_POST['access_key'];

    // 入力エラーチェック処理
    $err = array();
    $complete_msg = "";
      // エラー：ユーザーネームが入力されていない
      if($member_name == ''){
        $err['member_name'] = 'ユーザーネームが入力されていません。';
      }

      // エラー：ユーザーネームが長すぎる（21文字以上）
      if(strlen($member_name) > 40){
        $err['member_name'] = 'ユーザーネームが長すぎます。';
      }

      // エラー：アクセスキーが入力されていない
      if($access_key == ''){
        $err['access_key'] = 'アクセスキーが入力されていません。';
      }else{
        // アクセスキーが管理用なのか参加用なのかを判断
          // 参加用アクセスキーの場合
        if(substr($access_key,0,3) == "atd"){
          // アクセスキーがprojectテーブルのattend_keyカラムに登録されているかを確認
          $sql = "SELECT * FROM project WHERE attend_key = :attend_key";
          $stmt = $pdo->prepare($sql);
          $stmt->execute(array(":attend_key"=>$access_key));
          $project = $stmt->fetch(PDO::FETCH_ASSOC);
          if(!$project){
            // エラー：登録されていない場合、エラーを表示
              $err['access_key'] = '登録されたアクセスキーではありません。';
          }

          // エラー：参加者において、ユーザーネームがすでに使用されている
          $sql = "SELECT * FROM members WHERE project_id = :project_id AND member_name = :member_name";
          $stmt = $pdo->prepare($sql);
          $stmt->execute(array(":project_id" => $project['id'], ":member_name"=>$member_name));
          $name = $stmt->fetch(PDO::FETCH_ASSOC);
          if($name){
            $err['member_name'] = 'そのユーザーネームはすでに使われています。';
          }

          // 管理用アクセスキーの場合
        } elseif(substr($access_key,0,3) == "mng"){
          // アクセスキーがprojectテーブルのmanage_keyカラムに登録されているかを確認
          $sql = "SELECT * FROM project WHERE manage_key = :manage_key";
          $stmt = $pdo->prepare($sql);
          $stmt->execute(array(":manage_key"=>$access_key));
          $project = $stmt->fetch(PDO::FETCH_ASSOC);
          if(!$project){
            // エラー：登録されていない場合、エラーを表示
              $err['access_key'] = '登録されたアクセスキーではありません。';
          }

        } else {
          $err['access_key'] = '適切なアクセスキーではありません。';
        }



      }

      // 何もエラーがない場合、
      if(empty($err)){
        // projectデータをセッション変数に格納する
        $_SESSION['project'] = $project;
        // poroject_idに対応するmember_nameが登録されているかを確認する
        $sql = "SELECT * FROM members WHERE project_id = :project_id AND member_name = :member_name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(":project_id" => $project['id'], ":member_name" => $member_name));
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

          // 登録されていなければ、membersテーブルにproject_idとmember_nameを新規登録する
          if(!$member){
            $sql = "INSERT INTO members (project_id, member_name, created_at, updated_at) VALUES (:project_id, :member_name, now(), now())";
            $stmt= $pdo->prepare($sql);
            $stmt->execute(array(":project_id"=>$project['id'], ":member_name" => $member_name));

            // その後、メンバー情報を取得する
            $sql = "SELECT * FROM members WHERE member_name = :member_name";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(":member_name" => $member_name));
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
          }

          // セッション変数にメンバー情報を格納
          $_SESSION['member'] = $member;

          // プロジェクトの更新
          $sql = "UPDATE project SET updated_at = now() WHERE id = :id";
          $stmt = $pdo->prepare($sql);
          $stmt->execute(array(":id" => $project_id));

          // 参加用アクセスキーの場合
          if(substr($access_key,0,3) == "atd"){
            // input.phpに遷移する
            header('Location: '.SITE_URL.'input.php');
            exit;

          // 管理用アクセスキーの場合
          } elseif(substr($access_key,0,3) == "mng"){
            // select.phpに遷移する
            header('Location: '.SITE_URL.'select.php');
            exit;

          }
        }
    }

    unset($pdo);
}
unset($pdo);
?>

<!DOCTYPE html>
<html lang="jp">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="ログイン不要の多人数向け予定調整アプリ" />
        <meta name="author" content="GroupScheduler" />
        <title>HOME｜Group Scheduler</title>
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
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
            <div class="container">
                <a class="navbar-brand" href="index.php">GroupScheduler</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu
                    <i class="fas fa-bars ms-1"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                        <li class="nav-item"><a class="nav-link" href="#services">How to Use</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Masthead-->
        <header class="masthead">
            <div class="container">
                <div class="masthead-subheading">ログイン不要の多人数向け予定調整アプリ</div>
                <div class="masthead-heading text-uppercase">Group Scheduler</div>

                <div class="d-grid gap-2 col-md-3 mx-auto">
                  <form class="form" method="post">
                    <button type="submit" class="btn btn-primary btn-xl">新規登録</button>

                    <!-- CSRF対策：次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
                    <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

                    <!-- フォーム識別用input -->
                    <input type="hidden" name="type" value="create" />

                  </form>
                </div>

                <div class="my-5">
                  <form class="form" method="POST">

                    <div class="mb-3 form-group d-grid gap-2 col-md-6 mx-auto <?php if ($err['user_name'] != '') echo 'has-error'; ?>">
                      <label for="name" class="form-label">ユーザーネーム</label>
                      <input class="form-control" type="text" name="member_name" placeholder="" value="<?php echo h($user_name); ?>" />
                      <span class="help-inline text-warning"><?php echo h($err['member_name']); ?></span>
                    </div>

                    <div class="mb-4 form-group d-grid gap-2 col-md-6 mx-auto <?php if ($err['access_key'] != '') echo 'has-error'; ?>">
                      <label for="access-key" class="form-label">アクセスキー</label>
                      <input class="form-control" type="text" name="access_key" placeholder="" value="<?php echo h($access_key); ?>" />
                      <span class="help-inline text-warning"><?php echo h($err['access_key']); ?></span>
                    </div>

                    <div class=" form-group">
                      <button type="submit" class="btn btn-primary btn-xl">アクセス</button>
                    </div>

                    <!-- CSRF対策：次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
                    <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

                  </form>
                </div>

            </div><!-- container -->
        </header><!-- Masthead-->

        <!-- Services-->
        <section class="page-section" id="services">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase">本サービスの使い方</h2>
                </div>
                <div class="row text-center">

                  <!-- 使い方① -->
                    <div class="row mb-3">
                      <div class="col-lg-6">
                        <h4 class="my-3">① 予定設定</h4>
                        <p class="text-muted">新規登録ボタンを押して、候補となる予定を入力し、予定を設定してください。<br>
                        管理用アクセスキーと参加用アクセスキーが発行されますので、コピーして保存してください。<br>
                        その後、招待する人に参加用アクセスキーを通知してください。</p>
                      </div>
                      <div class="col-lg-6">
                        <img src="./img/予定登録画面.png" class="img-fluid border border-primary" alt="予定登録画面">
                      </div>
                    </div>

                    <!-- 使い方② -->
                    <div class="row mb-3">
                      <div class="col-lg-6">
                        <h4 class="my-3">② 予定入力</h4>
                        <p class="text-muted">トップページにて、ユーザーネームと参加用アクセスキーを入力すると、予定入力画面に移行します。<br>
                        参加される方は、表示される予定候補に対し参加可否を入力し、登録ボタンを押してください。<br>
                      　参加可否は何度でも更新することができます。</p>
                      </div>
                      <div class="col-lg-6">
                        <img src="./img/参加登録画面.png" class="img-fluid border border-primary" alt="参加登録画面">
                      </div>
                    </div>

                    <!-- 使い方③ -->
                    <div class="row mb-3">
                      <div class="col-lg-6">
                        <h4 class="my-3">③ 予定確認</h4>
                        <p class="text-muted">トップページにて、ユーザーネームと管理用アクセスキーを入力すると、予定確認画面に移行します。<br>
                        設定された予定候補と参加可能なメンバーを確認し、最適な予定を参加者に通知してください。</p>
                      </div>
                      <div class="col-lg-6">
                        <img src="./img/予定確認画面.png" class="img-fluid border border-primary" alt="予定確認画面">
                      </div>
                    </div>

                </div>
            </div>
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
