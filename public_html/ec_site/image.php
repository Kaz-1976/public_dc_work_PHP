<?php
// 共通処理ロード
require_once '../../include/config/const.php';
require_once '../../include/model/db.php';
// DB接続
$db = db_connect();
// 画像データー取得
$sql = 'SELECT product_image, product_image_type FROM ec_products WHERE id = :id;';
$bind = [['name' => ':id', 'value' => $_GET['id']]];
$result = db_select($db, $sql, $bind);
// 画像データー出力
header('Content-type: ' . $result->data[0]['product_image_type']);
echo $result->data[0]['product_image'];
exit;