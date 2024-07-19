<?php
// DB接続処理
$db = db_connect();
?>

<?php
// ログアウト処理
logout();
// アクセス許可チェック処理
if (!checkAccess(false)) {
    header('Location: ' . LINK_TOP_PAGE);
    exit;
}
?>

<?php
// POSTされた入力項目を格納（更新フォーム）
$listCartDetail = (object)[
    'cartId'        =>  isset($_SESSION['cart-id']) ? (int) htmlspecialchars($_SESSION['cart-id']) : null,
    'id'            =>  isset($_POST['list-cart-detail-id']) ? (int) htmlspecialchars($_POST['list-cart-detail-id']) : null,
    'productId'     =>  isset($_POST['list-cart-detail-product-id']) ? (int) htmlspecialchars($_POST['list-cart-detail-product-id']) : null,
    'productName'   =>  isset($_POST['list-cart-detail-product-name']) ? htmlspecialchars($_POST['list-cart-detail-product-name']) : null,
    'stockId'       =>  isset($_POST['list-cart-detail-stock-id']) ? (int) htmlspecialchars($_POST['list-cart-detail-stock-id']) : null,
    'qty'           =>  isset($_POST['list-cart-detail-qty']) ? (int) htmlspecialchars($_POST['list-cart-detail-qty']) : null
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
function cartDetailPagenation($db)
{
    // 最終ページ取得
    $pageMax = (int) ceil(getCartDetailCount($db) / ITEM_PER_PAGE_LIST);
    // ページネーション出力
    writePagenation(LINK_CART_PAGE, $pageMax);
}
/**
 * 
 * バリデーション関数（カート更新）
 *
 * @param object $db
 * @param object $cartDetail
 * @return object
 *
 */
function Validate($db, $cartDetail): object
{
    // 変数初期化
    $obj_error = (object) ['err_flg' => false, 'err_msg' => ''];

    // 未入力チェック
    if (empty($cartDetail->qty)) {
        $obj_error->err_flg = true;
        $obj_error->err_msg = '未入力の項目があります。';
        return $obj_error;
    }

    // 数量チェック
    if ($cartDetail->qty <= 0) {
        $obj_error->err_flg = true;
        $obj_error->err_msg = '数量は１以上の値を入力してください。商品名：' . $cartDetail->productName;
        return $obj_error;
    }

    // 在庫チェック
    $result = getStocksData($db, $cartDetail);
    if ($result['stock_qty'] < $cartDetail->qty) {
        $obj_error->err_flg = true;
        $obj_error->err_msg = '在庫が不足しています。商品名：' . $cartDetail->productName . ' 在庫数量：' . $result['stock_qty'];
        return $obj_error;
    }


    // リターン
    return $obj_error;
}
/**
 * 
 * カート明細存在チェック
 * 
 * @param object $db
 * @return bool
 * 
 */
function isExistCart($db): bool
{
    if (isset($_SESSION['cart-id'])) {
        if ((int) getCartDetailCount($db, $_SESSION['cart-id']) !== 0) {
            return true;
        }
    }
    return false;
}
/**
 * 
 * カート明細総数取得関数
 *
 * @param object $db
 * @return int
 */
function getCartDetailCount($db): int
{
    // SELECT文構築
    $sql = 'SELECT count(*) count FROM ec_cart_detail WHERE cart_id = :cart_id;';
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':cart_id',
            'value' => $_SESSION['cart-id']
        ]
    ];
    // SELECT文発行
    $record = db_select($db, $sql, $bind);
    // リターン
    return (int) $record->data[0]['count'];
}
/**
 * 
 * カート合計金額取得関数
 *
 * @param object $db
 * @return array
 */
function getCartTotal($db): array
{
    // SELECT文構築
    $sql = 'SELECT sum(qty) qty, sum(price * qty) price FROM ec_cart_detail WHERE cart_id = :cart_id;';
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':cart_id',
            'value' => $_SESSION['cart-id']
        ]
    ];
    // SELECT文発行
    $record = db_select($db, $sql, $bind);
    // リターン
    return $record->data[0];
}
/**
 * 
 * 在庫データー取得関数
 *
 * @param object $db
 * @param object $cartDetail
 * @return mixed
 */
function getStocksData($db, $cartDetail)
{
    // SELECT文構築
    $sql = <<<___SQL___
            SELECT  stock_qty
            FROM    ec_stocks
            WHERE   id  =   :id;
        ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':id',
            'value' => $cartDetail->stockId
        ]
    ];
    // SELECT文発行
    $record = db_select($db, $sql, $bind);
    // リターン
    return $record->data[0];
}
/**
 * 
 * カート明細レコード取得関数
 *
 * @param object $db
 * @param int $cartId
 * @return mixed
 * 
 */
function getCartDetailRecord($db, $cartId)
{
    // パラメーター取得
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    // SELECT文構築
    $sql = <<<___SQL___
            SELECT      ec_cart_detail.id           id
            ,           ec_cart_detail.product_id   product_id
            ,           ec_products.product_name    product_name
            ,           ec_stocks.id                stock_id
            ,           ec_products.price           price
            ,           ec_cart_detail.qty          qty
            FROM        ec_cart
            ,           ec_cart_detail
            ,           ec_products
            ,           ec_stocks
            WHERE       ec_cart.id                  =   :cartId
            AND         ec_cart_detail.cart_id      =   ec_cart.id
            AND         ec_cart_detail.product_id   =   ec_products.id
            AND         ec_products.id              =   ec_stocks.product_id
            AND         ec_cart.checkout_flg        =   0
            ORDER BY    ec_cart_detail.id
            LIMIT       :offset
            ,           :limit;
        ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':cartId',
            'value' => $cartId
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
    if ($record->err_flg) {
        return null;
    } else {
        return $record;
    }
}
/**
 * 
 * カート明細レコード更新関数
 *
 * @param object $db
 * @param object $cartDetail
 * @return object
 */
function updateCartDetailRecord($db, $cartDetail): object
{
    // 変数初期化
    $data = (object) ['err_flg' => false, 'err_msg' => ''];
    // UPDATE文構築
    $sql = <<<___SQL___
        UPDATE  ec_cart_detail
        SET     qty         =   :qty
        ,       update_user =   :update_user
        ,       update_date =   NOW()
        WHERE   cart_id     =   :cartId
        AND     id          =   :id;
    ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':cartId',
            'value' => $cartDetail->cartId
        ],
        [
            'name' => ':id',
            'value' => $cartDetail->id
        ],
        [
            'name' => ':qty',
            'value' => $cartDetail->qty
        ],
        [
            'name' => ':update_user',
            'value' => $_SESSION['user-data']['id']
        ]
    ];
    // DB更新処理
    $result = db_execute($db, $sql, $bind, true);
    // エラー処理
    if ($result->err_flg) {
        // 実行結果取得
        $data->err_flg = $result->err_flg;
        $data->err_msg = $result->err_msg;
        // リターン
        return $data;
    }
    // リターン
    return $data;
}
/**
 * 
 * カート明細レコード削除関数
 *
 * @param object $db
 * @param object $cartDetail
 * @return object
 */
function deleteCartDetailRecord($db, $cartDetail): object
{
    // 変数初期化
    $data = (object) ['err_flg' => false, 'err_msg' => ''];
    // DELETE文構築
    $sql = <<<___SQL___
        DELETE
        FROM    ec_cart_detail
        WHERE   cart_id     =   :cartId
        AND     id          =   :id;
    ___SQL___;
    // バインドパラメーター設定
    $bind = (array) [
        [
            'name' => ':cartId',
            'value' => $cartDetail->cartId
        ],
        [
            'name' => ':id',
            'value' => $cartDetail->id
        ]
    ];
    // DB削除処理
    $result = db_execute($db, $sql, $bind, true);
    // エラー処理
    if ($result->err_flg) {
        // 実行結果取得
        $data->err_flg = $result->err_flg;
        $data->err_msg = $result->err_msg;
        // リターン
        return $data;
    }
    // リターン
    return $data;
}
/**
 * 
 * カート削除関数
 *
 * @param object $db
 * @param object $cartDetail
 * @return object
 */
function emptyCartRecord($db, $cartDetail): object
{
    // 変数初期化
    $data = (object) ['err_flg' => false, 'err_msg' => ''];
    // トランザクション開始
    $db->beginTransaction();
    // DELETE文構築
    $sql = <<<___SQL___
        DELETE
        FROM    ec_cart_detail
        WHERE   cart_id =   :cartId;
    ___SQL___;
    // バインドパラメーター設定
    $bind = (array) [
        [
            'name' => ':cartId',
            'value' => $cartDetail->cartId
        ]
    ];
    // DB削除処理
    $result = db_execute($db, $sql, $bind, false);
    // エラー処理
    if ($result->err_flg) {
        // ロールバック
        $db->rollback();
        // 実行結果取得
        $data->err_flg = $result->err_flg;
        $data->err_msg = $result->err_msg;
        // リターン
        return $data;
    }
    // コミット
    $db->commit();
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
            case 'update':
                // バリデーション
                $result = Validate($db, $listCartDetail);
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit;
                }
                // 明細更新処理
                $result = updateCartDetailRecord($db, $listCartDetail);
                // メッセージ設定
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                } else {
                    $_SESSION['message'] = 'カートを更新しました。';
                }
                // リロード
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            case 'delete':
                // 明細削除処理
                $result = deleteCartDetailRecord($db, $listCartDetail);
                // メッセージ設定
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                } else {
                    $_SESSION['message'] = 'カートから削除しました。';
                }
                // リロード
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            case 'empty':
                // カート削除処理
                $result = emptyCartRecord($db, $listCartDetail);
                // メッセージ設定
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                } else {
                    $_SESSION['message'] = 'カートを空にしました。';
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