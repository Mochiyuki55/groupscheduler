<?php
// ローカル環境
  define('HOST_NAME','localhost');
  define('DATABASE_USER_NAME','root');
  define('DATABASE_PASSWORD','');
  define('DATABASE_NAME','groupscheduler');
  define('SITE_URL', 'http://localhost/dev/groupscheduler/web/');

// 参考
//「https://limesnake4.sakura.ne.jp/」＝「http://localhost/dev/」
// サーバーが変わったときは以下の設定を変更するだけで良い
  // define('HOST_NAME','mysql57.limesnake4.sakura.ne.jp');
  // define('DATABASE_USER_NAME','limesnake4');
  // define('DATABASE_PASSWORD','Yaguchi88');
  // define('DATABASE_NAME','limesnake4_groupscheduler');
  // define('SITE_URL', 'http://groupscheduler.net/');

// メールフォーム
define('ADMIN_EMAIL', 'yaguchi1061@gmail.com');

// コピーライト
define('COPY_RIGHT', 'Group Scheduler');


define('CANDIDATE_YEARS_ARRAY',array(
    "2022"   => "2022",
    "2023"   => "2023",
    "2024"   => "2024",
    "2025"   => "2025",
    "2026"   => "2026",
    "2027"   => "2027",
    "2028"   => "2028",
    "2029"   => "2029",
    "2030"   => "2030",
    "2031"   => "2031",
    "2032"   => "2032"
));

define('CANDIDATE_MONTHS_ARRAY',array(
  "1"   => "1",
  "2"   => "2",
  "3"   => "3",
  "4"   => "4",
  "5"   => "5",
  "6"   => "6",
  "7"   => "7",
  "8"   => "8",
  "9"   => "9",
  "10" => "10",
  "11" => "11",
  "12" => "12"
));

define('CANDIDATE_DAYS_ARRAY',array(
  "1"   => "1",
  "2"   => "2",
  "3"   => "3",
  "4"   => "4",
  "5"   => "5",
  "6"   => "6",
  "7"   => "7",
  "8"   => "8",
  "9"   => "9",
  "10" => "10",
  "11"   => "11",
  "12"   => "12",
  "13"   => "13",
  "14"   => "14",
  "15"   => "15",
  "16"   => "16",
  "17"   => "17",
  "18"   => "18",
  "19"   => "19",
  "20" => "20",
  "21"   => "21",
  "22"   => "22",
  "23"   => "23",
  "24"   => "24",
  "25"   => "25",
  "26"   => "26",
  "27"   => "27",
  "28"   => "28",
  "29"   => "29",
  "30" => "30",
  "31" => "31",
));

define('CANDIDATE_HOURS_ARRAY',array(
    "0"   => "0",
    "1"   => "1",
    "2"   => "2",
    "3"   => "3",
    "4"   => "4",
    "5"   => "5",
    "6"   => "6",
    "7"   => "7",
    "8"   => "8",
    "9"   => "9",
    "10" => "10",
    "11" => "11",
    "12" => "12",
    "13" => "13",
    "14" => "14",
    "15" => "15",
    "16" => "16",
    "17" => "17",
    "18" => "18",
    "19" => "19",
    "20" => "20",
    "21" => "21",
    "22" => "22",
    "23" => "23"
));

define('CANDIDATE_MINUTES_ARRAY',array(
    "0"    => "0",
    "5"    => "5",
    "10"   => "10",
    "15"   => "15",
    "20"   => "20",
    "25"   => "25",
    "30"   => "30",
    "35"   => "35",
    "40"   => "40",
    "45"   => "45",
    "50"   => "50",
    "55"   => "55"
));
?>
