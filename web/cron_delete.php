<?php
require_once('config.php');
require_once('functions.php');

// やること：CRONの設定時刻になったら、プロジェクトの更新が一定期間行われなかった場合、
// 言い換えれば、deadlineが現在時刻より早く、かつprojectのupdated_atが現在時刻より1時間以上早い場合
// そのプロジェクトは期限完了したのに更新なし＝用済みということで、
// プロジェクトとプロジェクトに関する全てのテーブルのレコードを削除する

$pdo = connectDb();
//現在時刻を取得
$current_1 = date ( "Y/m/d H:i:s\n");
// 現在時刻の1時間前を取得
$current_2 = date ( "Y/m/d H:i:s\n", strtotime ("now -1 hour"));


$projects = array();
// 現在時刻がdeadlineを超えているprojectのidを取得
// deadline < :current_1 AND updated_at < :current_2
$sql = "SELECT * FROM project WHERE deadline < :current_1 AND updated_at < :current_2";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":current_1" => $current_1, ":current_2" => $current_2));
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    array_push($projects, $row);
}



$candidates = array();
foreach ($projects as $project) {
  // 関連するcandidate_idを取得
  $sql = "SELECT * FROM candidates WHERE project_id = :project_id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(":project_id" => $project['id']));
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      array_push($candidates, $row);
  }
  echo print_r($candidates);

  // candidate_memberテーブルの内、関連するcandidate_idを持つレコードを削除
  foreach ($candidates as $candidate) {
    $sql = "DELETE FROM candidate_member WHERE candidate_id = :candidate_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":candidate_id" => $candidate['id']));
  }

  // project, candidates, membersテーブルの対象レコードを削除
  $sql = "DELETE FROM project WHERE id = :id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(":id" => $project['id']));

  $sql = "DELETE FROM candidates WHERE project_id = :project_id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(":project_id" => $project['id']));

  $sql = "DELETE FROM members WHERE project_id = :project_id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(":project_id" => $project['id']));

}

echo "不要なプロジェクトを削除しました。";

unset($pdo);

?>
