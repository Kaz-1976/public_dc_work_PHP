<?php
// DB接続処理
$db = db_connect();
?>

<?php
// ログアウト処理
logout();
// アクセス許可チェック処理
if (!checkAccess(true)) {
    header('Location: ' . LINK_TOP_PAGE);
    exit;
}
?>

<?php
// POSTされた入力項目を格納（新規フォーム）
$formUser = (object) [
    'userId'    => isset($_POST['form-user-id']) ? htmlspecialchars($_POST['form-user-id']) : '',
    'name'      => isset($_POST['form-user-name']) ? htmlspecialchars($_POST['form-user-name']) : '',
    'kana'      => isset($_POST['form-user-kana']) ? htmlspecialchars($_POST['form-user-kana']) : '',
    'mail'      => isset($_POST['form-user-mail']) ? htmlspecialchars($_POST['form-user-mail']) : '',
    'password'  => isset($_POST['form-user-password']) ? htmlspecialchars($_POST['form-user-password']) : '',
    'admin'     => isset($_POST['form-user-admin']) ? ($_POST['form-user-admin']  ? 1 : 0) : 0,
    'enable'    => isset($_POST['form-user-enable']) ? ($_POST['form-user-enable']  ? 1 : 0) : 0
];
// POSTされた入力項目を格納（更新フォーム）
$listUser = (object)[
    'id'        => isset($_POST['list-id']) ? (int) htmlspecialchars($_POST['list-id']) : 0,
    'userId'    => isset($_POST['list-user-id']) ? htmlspecialchars($_POST['list-user-id']) : '',
    'name'      => isset($_POST['list-user-name']) ? htmlspecialchars($_POST['list-user-name']) : '',
    'kana'      => isset($_POST['list-user-kana']) ? htmlspecialchars($_POST['list-user-kana']) : '',
    'mail'      => isset($_POST['list-user-mail']) ? htmlspecialchars($_POST['list-user-mail']) : '',
    'password'  => isset($_POST['list-user-password']) ? htmlspecialchars($_POST['list-user-password']) : '',
    'admin'     => isset($_POST['list-user-admin']) ? ($_POST['list-user-admin'] ? 1 : 0) : 0,
    'enable'    => isset($_POST['list-user-enable']) ? ($_POST['list-user-enable'] ? 1 : 0) : 0
];
?>

<?php
/**
 * 
 * ページネーション出力関数
 * 
 * @param object $db
 * 
 */
function usersPagenation($db)
{
    // 最終ページ取得
    $pageMax = (int) ceil(getUsersCount($db) / ITEM_PER_PAGE_LIST);
    // ページネーション出力
    writePagenation(LINK_ADMIN_USERS, $pageMax);
}
/**
 * 
 * バリデーション関数（ユーザー管理）
 *
 * @param object $db
 * @param object $user
 * @param string $mode INSERT | UPDATE 
 * @return object
 *
 */
function Validate($db, $user, $mode): object
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
    if (!preg_match('/^[a-zA-Z0-9]+$/', $user->userId) || strlen($user->userId) > 64) {
        $obj_error->err_flg = true;
        $obj_error->err_msg = 'ユーザーIDは64文字以内の半角英数字で入力してください。';
        return $obj_error;
    }

    // パスワードチェック
    if ($mode === 'INSERT') {
        if (!preg_match('/^[a-zA-Z0-9!@#$%^&*]+$/', $user->password) || strlen($user->password) < 8 || strlen($user->password) > 64) {
            $obj_error->err_flg = true;
            $obj_error->err_msg = 'パスワードは64文字以内の半角英数字および半角記号（! @ # $ % ^ & *）で入力してください。';
            return $obj_error;
        }
    }

    // メールアドレスチェック
    if (!preg_match('/^[a-zA-Z0-9_+-]+(.[a-zA-Z0-9_+-]+)*@([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]*\.)+[a-zA-Z]{2,}$/', $user->mail) || strlen($user->mail) > 64) {
        $obj_error->err_flg = true;
        $obj_error->err_msg = 'メールアドレスの形式が誤っています。';
        return $obj_error;
    }

    // リターン
    return $obj_error;
}
/**
 * 
 * ユーザーテーブル：有効フラグ更新関数
 * 
 * @param object $db        DB接続情報
 * @param object $user      ユーザーレコード
 *
 * @return object
 * 
 */
function setUserEnable($db, $user): object
{
    // 変数初期化
    $data = (object) ['data' => null, 'err_flg' => false, 'err_msg' => ''];
    // SQL文生成
    $sql = <<<___SQL___
        UPDATE  ec_users
        SET     enable_flg  =   :enable_flg
        ,       update_user =   :update_user
        ,       update_date =   NOW()
        WHERE   id          =   :id;
        ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':id',
            'value' => $user->id
        ],
        [
            'name' => ':enable_flg',
            'value' => $user->enable ? 0 : 1
        ],
        [
            'name' => ':update_user',
            'value' => $_SESSION['user-data']['id']
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
/**
 * 
 * ユーザーテーブル：管理フラグ更新関数
 * 
 * @param object $db        DB接続情報
 * @param object $user      ユーザーレコード
 *
 * @return object
 * 
 */
function setUserAdmin($db, $user): object
{
    // 変数初期化
    $data = (object) ['data' => null, 'err_flg' => false, 'err_msg' => ''];
    // SQL文生成
    $sql = <<<___SQL___
        UPDATE  ec_users
        SET     admin_flg   =   :admin_flg
        ,       update_user =   :update_user
        ,       update_date =   NOW()
        WHERE   id          =   :id;
        ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':id',
            'value' => $user->id
        ],
        [
            'name' => ':admin_flg',
            'value' => $user->admin ? 0 : 1
        ],
        [
            'name' => ':update_user',
            'value' => $_SESSION['user-data']['id']
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
/**
 * 
 * ユーザー総数取得関数
 *
 * @param object $db
 * @return int
 */
function getUsersCount($db): int
{
    // SELECT文構築
    $sql = 'SELECT count(*) count FROM ec_users WHERE user_id != :super_user_id;';
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':super_user_id',
            'value' => SUPER_USER_ID
        ]
    ];
    // SELECT文発行
    $record = db_select($db, $sql, $bind);
    // リターン
    return (int) $record->data[0]['count'];
}

/**
 * 
 * ユーザーレコード取得関数
 *
 * @param object $db
 * @return object
 */
function getUserRecord($db)
{
    // パラメーター取得
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    // SELECT文構築
    $sql = <<<___SQL___
        SELECT      id
        ,           user_id
        ,           user_name
        ,           user_kana
        ,           email
        ,           password
        ,           admin_flg
        ,           enable_flg
        FROM        ec_users
        WHERE       user_id     !=  :super_user_id
        ORDER BY    id
        LIMIT       :offset
        ,           :limit;
        ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':super_user_id',
            'value' => SUPER_USER_ID
        ],
        [
            'name' => ':offset',
            'value' => ITEM_PER_PAGE_LIST * ($page - 1)
        ],
        [
            'name' => ':limit',
            'value' => ITEM_PER_PAGE_LIST
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
            'value' => $user->admin
        ],
        [
            'name' => ':enable_flg',
            'value' => $user->enable
        ],
        [
            'name' => ':create_user',
            'value' => $_SESSION['user-data']['id']
        ],
        [
            'name' => ':update_user',
            'value' => $_SESSION['user-data']['id']
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
/**
 * 
 * ユーザーレコード更新関数
 *
 * @param object $db
 * @param object $user
 * @return object
 */
function updateUserRecord($db, $user): object
{
    // 変数初期化
    $data = (object) ['data' => null, 'err_flg' => false, 'err_msg' => ''];
    // INSERT文構築
    $sql = <<<___SQL___
        UPDATE  ec_users
        SET     user_id     =   :user_id
        ,       user_name   =   :user_name
        ,       user_kana   =   :user_kana
        ,       email       =   :email
        ,       admin_flg   =   :admin_flg
        ,       enable_flg  =   :enable_flg
        ,       update_user =   :update_user
        ,       update_date =   NOW()
        WHERE   id          =   :id;
    ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':id',
            'value' => $user->id
        ],
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
            'name' => ':admin_flg',
            'value' => $user->admin
        ],
        [
            'name' => ':enable_flg',
            'value' => $user->enable
        ],
        [
            'name' => ':update_user',
            'value' => $_SESSION['user-data']['id']
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
            case 'admin-users':
                // リロード
                header('Location: ' . LINK_ADMIN_USERS);
                exit;
            case 'create':
                // バリデーション
                $result = Validate($db, $formUser, 'INSERT');
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
                    $_SESSION['message'] = 'ユーザー情報を登録しました。';
                }
                // リロード
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            case 'update':
                // バリデーション
                $result = Validate($db, $listUser, 'UPDATE');
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit;
                }
                // 更新処理
                $result = updateUserRecord($db, $listUser);
                // メッセージ設定
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                } else {
                    $_SESSION['message'] = 'ユーザー情報を更新しました。';
                }
                // リロード
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            case 'admin':
                // 管理フラグ更新処理
                $result = setUserAdmin($db, $listUser);
                // メッセージ設定
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                } else {
                    if ($listUser->admin === 0) {
                        $_SESSION['message'] = '管理ユーザーに変更しました。';
                    } else {
                        $_SESSION['message'] = '一般ユーザーに変更しました。';
                    }
                }
                // リロード
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            case 'enable':
                // 有効フラグ更新処理
                $result = setUserEnable($db, $listUser);
                // メッセージ設定
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                } else {
                    if ($listUser->enable === 0) {
                        $_SESSION['message'] = '有効にしました。';
                    } else {
                        $_SESSION['message'] = '無効にしました。';
                    }
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