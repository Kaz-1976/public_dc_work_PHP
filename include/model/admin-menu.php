<?php
// ログアウト処理
logout();
// アクセス許可チェック処理
if (!checkAccess(true)) {
  header('Location: ' . LINK_TOP_PAGE);
  exit;
}
// CSRF対策：トークン生成
$token = bin2hex(openssl_random_pseudo_bytes(16));
$_SESSION['csrf-token'] = $token;
