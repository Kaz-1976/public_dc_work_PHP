<?php
session_start();  // セッション開始
ob_start(); // 出力バッファ開始
?>

<?php
// 共通処理ロード
require_once '../../include/config/const.php';
require_once '../../include/model/db.php';
require_once '../../include/model/util.php';
?>

<?php
// 個別ページModelロード
require_once '../../include/model/complete.php';
?>

<?php
// 個別ページViewロード
include_once '../../include/view/complete.php';
?>

<?php
ob_end_flush(); // 出力バッファフラッシュ
?>