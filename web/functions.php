<?php
require_once('config.php');
session_start();

// データベースに接続する
function connectDb() {
  $host = HOST_NAME; //データベースサーバ名
  $user = DATABASE_USER_NAME; //データベースユーザー名
  $pass = DATABASE_PASSWORD; //パスワード
  $db = DATABASE_NAME; //データベース名
  $param= 'mysql:dbname='.$db.';host='.$host;

  // 例外処理は”起きることが期待されない問題”で、多くの場合、プログラムの実行を停止しても構わない場合に使う
  try{
    // 例外処理：以下の処理でエラーが発生したら
    $pdo = new PDO($param, $user, $pass);
    $pdo->query('SET NAMES utf8;');
    return $pdo;

  } catch (PDOException $e){
    // 例外処理：エラー内容をエコーして処理を終了
    echo $e->getMessage();
    exit;
  }

}

// 配列からプルダウンメニューを生成する
// 引数として、selectタグのname値($inputName),メニュー項目用の配列($srcArray),選択値($selectedIndex),の3つを受け取るようにしています。
function arrayToSelect($inputName, $srcArray, $selectedIndex = "") {
    $temphtml = '<select name="'. $inputName. '">';

    foreach ($srcArray as $key => $val) {
        if ($selectedIndex == $key) {
            $selectedText = ' selected="selected"';
        } else {
            $selectedText = '';
        }
        // 「.=」というのは「$temphtml = $temphtml.'xxx'」と同じ意味で、文字列を変数に連結しているという意味です。
        $temphtml .= '<option value="'. $key. '"'. $selectedText. '>'. $val. '</option>';
    }

    // もとの$temphtmlに次の文字列を付け加える。
    $temphtml .= '</select>';

    return $temphtml;
}


// HTMLエスケープ用関数（XSSのサイバー攻撃の対策）
// HTML上のecho関数のところに設置する　"<?php echo h($変数名);
function h($original_str){
  return htmlspecialchars($original_str, ENT_QUOTES, "UTF-8");
}

// CSRF対策用関数
// CSRF対策は、呼び出し元の画面が固定であり、呼び出しによりDB登録などの重要処理を行う画面には必ず施します。
// 基本的には、<form>の送信先ページには施すと考えておけば良いと思います。
// 逆に、登録処理などを行わないページや複数箇所から呼び出されるページ（例えばTOPページや一覧ページなど）についてはCSRF対策は施しません。
// トークンを発行する処理（このトークンはCookieとは関係ない）
function setToken() {
    // 暗号化されたランダムな文字列を作成
    $token = sha1(uniqid(mt_rand(), true));
    // 作成したトークンをセッションに登録
    $_SESSION['sstoken'] = $token;
}

// トークンをチェックする処理
function checkToken() {
    // 発行したトークンをセッション内に持っていない、もしくはPOSTされたトークンがセッション内のトークンと異なる場合
    if (empty($_SESSION['sstoken']) || ($_SESSION['sstoken'] != $_POST['token'])) {
        echo '<html><head><meta charset="utf-8"></head><body>不正なアクセスです。</body></html>';
        // 処理を強制終了
        exit;
    }
}


?>
