<?php
require_once('config.php');
require_once('functions.php');

session_start();

// セッションがない状態でアクセスした場合、index.phpに遷移する
if (!isset($_SESSION['project'])) {
    header('Location: '.SITE_URL);
    exit;
}

$pdo = connectDb();

// 候補リストアップ処理
  // セッション情報を取得
  $deadline = $_SESSION['project']['deadline'];
  $project_id = $_SESSION['project']['id'];
  // 主催者の名前
  $manager_name = $_SESSION['member']['member_name'];

  // プロジェクトに登録されている候補を全て取得する
  $candidates = array();
  $sql = "SELECT * FROM candidates WHERE project_id = :project_id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(":project_id" => $project_id));
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      array_push($candidates, $row);
  }
  //
  // プロジェクトに参加可否登録をしたメンバー情報を取得する
  $members = array();
  $sql = "SELECT * FROM members WHERE project_id = :project_id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(":project_id" => $project_id));
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      array_push($members, $row);
  }

  // 各候補ごとに、プロジェクトに参加しているメンバーの中で、参加可能と答えているメンバーをまとめる
  $candidates_yes = array();
  foreach ($candidates as $candidate) {
    $candidates_yes[$candidate['id']] = array();
      $sql = "SELECT * FROM candidate_member WHERE candidate_id = :candidate_id AND attend_flag = 1";
      $stmt = $pdo->prepare($sql);
      $stmt->execute(array(":candidate_id" => $candidate['id']));
      foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
          array_push($candidates_yes[$candidate['id']], $row['member_id']);
      }
  }

  // memberテーブルから、idとmember_nameとを結びつける
  $members_id_name = array();
  $sql = "SELECT id, member_name FROM members";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  foreach ($stmt->fetchAll(PDO::FETCH_KEY_PAIR) as $id => $name) {
      $members_id_name += array($id => $name);
      // これで、$member_id_nameにidをキーとして入力するとmember_nameを取得できる
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
        <title>予定選出画面｜Group Scheduler</title>
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
                    <h2 class="mt-4 section-heading text-uppercase">予定確認画面</h2>
                    <h3 class="section-subheading text-muted"><?php echo h($manager_name); ?>さん、候補日ごとの参加可否状況をご確認ください。</h3>

                <p>このプロジェクトの参加者は次の<?php echo h(count($members)); ?>人です。</p>
                <p>
                  <?php foreach ($members as $member) {
                    echo h($member['member_name'])."　";
                  } ?>
                </p>

                <p>設定した予定候補ごとの参加可能なメンバーは、以下の通りです。</p>
                <div class="text-left">
                  <ol class="list-group list-group-numbered">
                    <?php foreach ($candidates as $candidate): ?>

                      <li class="list-group-item ">
                          <!-- ここに時間帯を出力 -->
                          　<?php echo h(substr($candidate['candidate_at_start'], 0, -3)); ?>　〜　<?php echo h(substr($candidate['candidate_at_end'], 0, -3)); ?>　<br>
                            参加人数：<?php echo h(count($candidates_yes[$candidate['id']])); ?>人<br>
                                <?php
                                  foreach ($candidates_yes[$candidate['id']] as $id) {
                                    echo h($members_id_name[$id])."　";
                                  }
                                ?>
                      </li>
                    <?php endforeach; ?>
                  </ol>
                </div>

                <p class="mt-4"><a href="index.php" class="link-primary" style="font-size:1.2rem">トップページへ戻る</a></p>

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
