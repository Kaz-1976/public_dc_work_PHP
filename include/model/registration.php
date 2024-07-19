<?php
// DB接続処理
$db = db_connect();
?>

<?php
// ログアウト処理
logout();
?>

<?php
// POSTされた入力項目を格納（新規フォーム）
$formUser = (object) [
    'userId'    => isset($_POST['form-user-id']) ? htmlspecialchars($_POST['form-user-id']) : '',
    'name'      => isset($_POST['form-user-name']) ? htmlspecialchars($_POST['form-user-name']) : '',
    'kana'      => isset($_POST['form-user-kana']) ? htmlspecialchars($_POST['form-user-kana']) : '',
    'mail'      => isset($_POST['form-user-mail']) ? htmlspecialchars($_POST['form-user-mail']) : '',
    'password'  => isset($_POST['form-user-password']) ? htmlspecialchars($_POST['form-user-password']) : ''
];
?>

<?php
/**
 * 
 * バリデーション関数（ユーザー管理）
 *
 * @param object $db
 * @param object $user
 * @return object
 *
 */
function Validate($db, $user): object
{
    // 変数初期化
    $obj_error = (object) ['err_flg' => false, 'err_msg' => ''];

    // 未入力チェック
    if ($user->userId === '' || $user->password === '' || $user->name === '' || $user->kana === '' || $user->mail === '') {
        $obj_error->err_flg = true;
        $obj_error->err_msg = '未入力の項目があります。';
        return $obj_error;
    }

    // ユーザーIDチェック
    if (!preg_match('/^[a-zA-Z0-9]+$/', $user->userId) || strlen($user->userId) < 5 || strlen($user->userId) > 64) {
        $obj_error->err_flg = true;
        $obj_error->err_msg = 'ユーザーIDは5～64文字の半角英数字で入力してください。';
        return $obj_error;
    }

    // パスワードチェック
    if (!preg_match('/^[a-zA-Z0-9!@#$%^&*]+$/', $user->password) || strlen($user->password) < 8 || strlen($user->password) > 64) {
        $obj_error->err_flg = true;
        $obj_error->err_msg = 'パスワードは8～64文字の半角英数字および半角記号（! @ # $ % ^ & *）で入力してください。';
        return $obj_error;
    }

    // メールアドレスチェック
    if (!preg_match('/^[a-zA-Z0-9_+-]+(.[a-zA-Z0-9_+-]+)*@([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]*\.)+[a-zA-Z]{2,}$/', $user->mail) || strlen($user->mail) > 64) {
        $obj_error->err_flg = true;
        $obj_error->err_msg = 'メールアドレスの形式が誤っています。';
        return $obj_error;
    }

    // 存在チェック
    $result = getUserRecord($db, $user->userId);
    if ($result->err_flg) {
        // DB検索エラー
        $obj_error->err_flg = true;
        $obj_error->err_msg = $result->err_msg;
        return $obj_error;
    } else {
        if (!empty($result->data)) {
            // 存在エラー
            $obj_error->err_flg = true;
            $obj_error->err_msg = '入力されたIDはすでに使用されています。';
            return $obj_error;
        }
    }

    // リターン
    return $obj_error;
}
/**
 * 
 * ユーザーレコード取得関数
 *
 * @param object $db
 * @return object
 */
function getUserRecord($db, $user)
{
    // SELECT文構築
    $sql = <<<___SQL___
        SELECT      user_id
        ,           user_name
        ,           user_kana
        ,           email
        ,           password
        ,           admin_flg
        ,           enable_flg
        FROM        ec_users
        WHERE       user_id     =  :user_id;
        ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':user_id',
            'value' => $user->userId
        ]
    ];
    // SELECT文発行
    $record = db_select($db, $sql, $bind);
    // リターン
    return $record;
}
/**
 * 
 * ユーザーレコード登録関数
 *
 * @param object $db
 * @param object $user
 * @return object
 */
function createUserRecord($db, $user): object
{
    // 変数初期化
    $data = (object) ['data' => null, 'err_flg' => false, 'err_msg' => ''];
    // INSERT文構築
    $sql = <<<___SQL___
        INSERT INTO ec_users (
            user_id,
            user_name,
            user_kana,
            email,
            password,
            admin_flg,
            enable_flg,
            last_login_date,
            create_user,
            create_date,
            update_user,
            update_date
        )
        VALUES(
            :user_id,
            :user_name,
            :user_kana,
            :email,
            :password,
            :admin_flg,
            :enable_flg,
            NULL,
            :create_user,
            NOW(),
            :update_user,
            NOW()
        )
    ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':user_id',
            'value' => $user->userId
        ],
        [
            'name' => ':user_name',
            'value' => $user->name
        ],
        [
            'name' => ':user_kana',
            'value' => $user->kana
        ],
        [
            'name' => ':email',
            'value' => $user->mail
        ],
        [
            'name' => ':password',
            'value' => password_hash($user->password, PASSWORD_DEFAULT)
        ],
        [
            'name' => ':admin_flg',
            'value' => 0
        ],
        [
            'name' => ':enable_flg',
            'value' => 1
        ],
        [
            'name' => ':create_user',
            'value' => 1
        ],
        [
            'name' => ':update_user',
            'value' => 1
        ]
    ];
    // DB登録処理
    $result = db_execute($db, $sql, $bind, true);

    // 実行結果取得
    $data->err_flg = $result->err_flg;
    $data->err_msg = $result->err_msg;

    // リターン
    return $data;
}
?>

<?php
// POST時の処理
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF対策：トークンチェック
    if (checkCSRF(isset($_POST['csrf-token']) ? $_POST['csrf-token'] : null)) {
        // ボタン処理
        switch ($_POST['action']) {
            case 'create':
                // バリデーション
                $result = Validate($db, $formUser);
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit;
                }
                // 登録処理
                $result = createUserRecord($db, $formUser);
                // メッセージ設定
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                } else {
                    $_SESSION['message'] = 'ユーザー登録が完了しました。';
                }
                // リロード
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            default:
                break;
        }
    } else {
        // メッセージ設定
        $_SESSION['message'] = '不正なリクエストです。';
        // リロード
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    // CSRF対策：トークン生成
    $token = bin2hex(openssl_random_pseudo_bytes(16));
    $_SESSION['csrf-token'] = $token;
}
?>