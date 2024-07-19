<?php
// DB接続処理
$db = db_connect();
?>

<?php
// ログアウト処理
logout();
?>

<?php
// Cookieの内容を変数に格納
$cookie_confirmation = isset($_COOKIE['cookie-confirmation']) ? 'checked' : '';
$login_id = isset($_COOKIE['login-id']) ? htmlspecialchars($_COOKIE['login-id']) : '';
$login_pw = isset($_COOKIE['login-pw']) ? htmlspecialchars($_COOKIE['login-pw']) : '';
?>

<?php
/**
 * 
 * バリデーション関数（ログイン認証）
 *
 * @param object $db        DB接続情報
 * @param string $userId    ユーザーID
 * @param string $userPw    ユーザーパスワード
 * 
 * @return object
 *
 */
function Validate($db, $userId, $userPw): object
{
    // 変数初期化
    $error = (object) ['err_flg' => false, 'err_msg' => ''];
    // ユーザーデーター取得
    $result = getUserData($db, $userId, true);
    // エラーチェック：DBエラー
    if ($result->err_flg) {
        $error->err_flg = true;
        $error->err_msg = $result->err_msg;
        return $error;
    }
    // エラーチェック：空文字チェック
    if ($userId === '' || $userPw === '') {
        $error->err_flg = true;
        $error->err_msg = 'IDまたはパスワードが入力されていません。';
        return $error;
    }
    // エラーチェック：存在チェック
    if (count($result->data) === 0) {
        $error->err_flg = true;
        $error->err_msg = 'IDまたはパスワードが誤っています。';
        return $error;
    }
    // エラーチェック：パスワードチェック
    if (!password_verify($userPw, $result->data['password'])) {
        $error->err_flg = true;
        $error->err_msg = 'IDまたはパスワードが誤っています。';
        return $error;
    }
    // リターン
    return $error;
}
/**
 * 
 * カートレコード取得
 * 
 * @param object $db
 * @param int $cartId
 *
 * @return object
 */
function getCartData($db, $cartId)
{
    // 変数初期化
    $data = (object) ['data' => null, 'err_flg' => false, 'err_msg' => ''];

    // SELECT文生成
    $sql = <<<___SQL___
        SELECT  user_id
        ,       checkout_flg
        FROM    ec_cart
        WHERE   id  =   :id
    ___SQL___;

    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':id',
            'value' => $cartId
        ]
    ];

    // SELECT文発行
    $result = db_select($db, $sql, $bind);

    // 実行結果取得
    if ($result->data === null) {
        $data->data = null;
    } else {
        $data->data = $result->data[0];
    }
    $data->err_flg = $result->err_flg;
    $data->err_msg = $result->err_msg;

    // リターン
    return $data;
}

/**
 * 
 * ユーザー情報取得関数
 * 
 * @param object $db        DB接続情報
 * @param string $userId    ユーザーID
 * @param string $userpw    パスワード
 *
 * @return object
 * 
 */
function getUserData($db, $userId, $enable): object
{
    // 変数初期化
    $data = (object) ['data' => null, 'err_flg' => false, 'err_msg' => ''];

    // SQL文生成
    $sql = <<<___SQL___
        SELECT  id
        ,       user_id
        ,       user_name
        ,       email
        ,       password
        ,       admin_flg
        ,       enable_flg
        FROM    ec_users
        WHERE   user_id = :user_id
        ___SQL___;
    $sql = $sql . ($enable ? ' AND enable_flg = :enable_flg;' : ';');

    // バインドパラメーター設定
    $bind = $enable ? [
        [
            'name' => ':enable_flg',
            'value' => 1
        ],
        [
            'name' => ':user_id',
            'value' => $userId
        ]
    ] : [
        [
            'name' => ':user_id',
            'value' => $userId
        ]
    ];

    // SQL文発行
    $result = db_select($db, $sql, $bind);

    // 実行結果取得
    if ($result->data == null) {
        $data->data = [];
    } else {
        $data->data = $result->data[0];
    }
    $data->err_flg = $result->err_flg;
    $data->err_msg = $result->err_msg;
    // リターン
    return $data;
}
/**
 * 
 * ユーザーテーブル：最終ログイン日時更新関数
 * 
 * @param object $db        DB接続情報
 * @param string $userId    ユーザーID
 *
 * @return object
 * 
 */
function setUserLastLogin($db, $userId): object
{
    // 変数初期化
    $data = (object) ['data' => null, 'err_flg' => false, 'err_msg' => ''];
    // SQL文生成
    $sql = <<<___SQL___
        UPDATE  ec_users
        SET     last_login_date =   NOW()
        WHERE   user_id         =   :user_id;
        ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':user_id',
            'value' => $userId
        ]
    ];
    // SQL文発行
    $result = db_execute($db, $sql, $bind, true);

    // 実行結果取得
    $data->err_flg = $result->err_flg;
    $data->err_msg = $result->err_msg;

    // リターン
    return $data;
}
?>

<?php
// POSTされたフォームの値を変数に格納する
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF対策：トークンチェック
    if (checkCSRF(isset($_POST['csrf-token']) ? $_POST['csrf-token'] : null)) {
        // Cookieの保存期間
        $cookie_expiration = time() + COOKIE_EXPIRATION_PERIOD;
        //  フォームの入力値を変数に格納
        $cookie_confirmation = isset($_POST['cookie-confirmation']) ? $_POST['cookie-confirmation'] : '';
        $login_id = isset($_POST['login-id']) ? $_POST['login-id'] : '';
        $login_pw = isset($_POST['login-pw']) ? $_POST['login-pw'] : '';
        // バリデーション
        $result = Validate($db, $login_id, $login_pw);
        if ($result->err_flg) {
            // メッセージ設定
            $_SESSION['message'] = $result->err_msg;
            // セッション変数設定
            unset($_SESSION['user-data']);
        } else {
            // メッセージ設定
            $_SESSION['message'] = '';
            // ユーザーデータ取得
            $result = getUserData($db, $login_id, true);
            // セッション変数設定
            $_SESSION['user-data'] = $result->data;
            // ログインIDの入力省略にチェックがされている場合はCookieを保存
            if ($cookie_confirmation === 'checked') {
                // Cookieを設定
                setcookie('cookie-confirmation', $cookie_confirmation, $cookie_expiration);
                setcookie('login-id', $login_id, $cookie_expiration);
            } else {
                // Cookieを削除
                setcookie('cookie-confirmation', '', time() - 30);
                setcookie('login-id', '', time() - 30);
            }
            // cookieに保存されたカートIDを復元
            if (isset($_COOKIE['cart-id'])) {
                // カート情報取得
                $result = getCartData($db, (int)$_COOKIE['cart-id']);
                if ($result->err_flg) {
                    // カートのユーザーIDとログインユーザーのユーザーIDを比較
                    if ((int)$result->data['user_id'] === (int)$_SESSION['user-data']['id'] && (int)$result->data['checkout_flg'] === 0) {
                        $_SESSION['cart-id'] = $_COOKIE['cart-id'];
                    } else {
                        // 保存されているカートIDをクリア
                        unset($_SESSION['cart-id']);
                        setcookie('cart-id', '', time() - 30);
                    }
                } else {
                    // 保存されているカートIDをクリア
                    unset($_SESSION['cart-id']);
                    setcookie('cart-id', '', time() - 30);
                }
            } else {
                // 保存されているカートIDをクリア
                unset($_SESSION['cart-id']);
            }
            // 最終ログインを更新
            $result = setUserLastLogin($db, $login_id);
        }
    } else {
        // メッセージ設定
        $_SESSION['message'] = '不正なリクエストです。';
        // リロード
        header('Location: ' . LINK_TOP_PAGE);
        exit;
    }
} else {
    // CSRF対策：トークン生成
    $token = bin2hex(openssl_random_pseudo_bytes(16));
    $_SESSION['csrf-token'] = $token;
}
?>

<?php
// ログイン中である場合はページ遷移
if (isLogin()) {
    // 管理ユーザーなら管理メニューへ、一般ユーザーなら商品一覧へ
    if (isAdmin()) {
        header('Location: ' . LINK_ADMIN_MENU);
        exit;
    } else {
        header('Location: ' . LINK_ITEM_LIST);
        exit;
    }
}
?>