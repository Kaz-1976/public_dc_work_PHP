<?php

/**
 * 
 * ログアウト関数
 *
 */
function logout()
{
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if ($_POST['action'] === 'logout') {
            // 
            setcookie('cart-id', $_SESSION['cart-id'], time() + COOKIE_EXPIRATION_PERIOD);
            // セッション名取得
            $session = session_name();
            // セッション変数を空にする
            $_SESSION = [];
            // セッション破棄 
            session_destroy();
            // Cookie内のセッションIDを削除
            if (isset($_COOKIE[$session])) {
                $params = session_get_cookie_params();
                setcookie($session, '', time() - 30, '/');
            }
            // メッセージ設定
            $_SESSION['message'] = 'ログアウトしました。';
            // ログインページへ遷移
            header('Location: ' . LINK_TOP_PAGE);
            exit;
        }
    }
}

/**
 * 
 * アクセス可否チェック関数
 *
 * @param boolean $admin
 * 
 * @return boolean
 * 
 */
function checkAccess($admin)
{
    if (isLogin()) {
        if (isAdmin() && $admin) {
            return true;
        }
        if (!isAdmin() && !$admin) {
            return true;
        }
    }
    return false;
}

/**
 * 
 * スーパーユーザーチェック関数
 *
 */
function isSuperUser()
{
    if (isLogin()) {
        if ($_SESSION['user-data']['user_id'] === SUPER_USER_ID) {
            return true;
        }
        return false;
    }
}

/**
 * 
 * 管理ユーザーチェック関数
 *
 */
function isAdmin()
{
    if (isLogin()) {
        if ($_SESSION['user-data']['admin_flg'] === 1) {
            return true;
        }
        return false;
    }
}

/**
 * 
 * ログイン状態取得関数
 *
 * @return boolean
 * 
 */
function isLogin()
{
    return isset($_SESSION['user-data']);
}

/**
 * 
 * カートユーザーチェック
 * 
 * @param object $db
 * @return bool
 * 
 */
function CartUser($db): bool
{
    if (isset($_SESSION['cart-id'])) {
        if (getCartDetailCount($db) !== 0) {
            return true;
        }
    }
    return false;
}
/**
 * 
 * カート情報クリア関数
 * 
 */
function clearCartId()
{
    // セッション変数のカートIDをクリア
    unset($_SESSION['cart-id']);
}

/**
 * 
 * CSRFトークンチェック
 * 
 */
function checkCSRF($token)
{
    if (isset($_SESSION['csrf-token'])) {
        if ($_SESSION['csrf-token'] === $token) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * 
 * ページネーション出力関数
 *
 * @param string $link
 * @param int $pageMax
 * @return void
 * 
 */
function writePagenation($link, $pageMax)
{
    // パラメーター取得
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    // 先頭ページへ
    echo '<div class="page">';
    if ($page === 1) {
        echo '<span class="page-text">';
        echo '&lt;&lt;';
        echo '</span>';
    } else {
        echo '<span class="page-text">';
        echo '<a href=' . $link . '>&lt;&lt;</a>';
        echo '</span>';
    }
    echo '</div>';
    // 前のページへ
    echo '<div class="page">';
    if ($page === 1) {
        echo '<span class="page-text">';
        echo '&lt;';
        echo '</span>';
    } else {
        echo '<span class="page-text">';
        echo '<a href="' . $link . '?page=' . (string) ($page - 1) . '">&lt;</a>';
        echo '</span>';
    }
    echo '</div>';
    // ページ番号
    for ($i = 1; $i <= $pageMax; $i++) {
        if ($i === $page) {
            echo '<div class="page-current">';
            echo '<span class="page-text-current">';
            echo (string) $i;
            echo '</span>';
            echo '</div>';
        } else {
            echo '<div class="page">';
            echo '<span class="page-text">';
            echo '<a href="' . $link . '?page=' . (string) $i . '">' . (string) $i . '</a>';
            echo '</span>';
            echo '</div>';
        }
    }
    // 次のページへ
    echo '<div class="page">';
    if ($pageMax === 0 || $page === $pageMax) {
        echo '<span class="page-text">';
        echo '&gt;';
        echo '</span>';
    } else {
        echo '<span class="page-text">';
        echo '<a href="' . $link . '?page=' . (string) ($page + 1) . '">&gt;</a>';
        echo '</span>';
    }
    echo '</div>';
    // 最終ページへ
    echo '<div class="page">';
    if ($pageMax === 0 || $page === $pageMax) {
        echo '<span class="page-text">';
        echo '&gt;&gt;';
        echo '</span>';
    } else {
        echo '<span class="page-text">';
        echo '<a href="' . $link . '?page=' . (string) $pageMax . '">&gt;&gt;</a>';
        echo '</span>';
    }
    echo '</div>';
}
