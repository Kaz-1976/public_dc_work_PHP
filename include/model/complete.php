<?php
// DB接続処理
$db = db_connect();
?>

<?php
// ログアウト処理
logout();
// アクセス許可チェック処理
if (!checkAccess(false)) {
    header('Location: ' . LINK_CART_PAGE);
    exit;
}
?>

<?php
// POSTされた入力項目を格納（更新フォーム）
$cart = (object)[
    'id'    =>  isset($_POST['list-cart-id']) ? (int) htmlspecialchars($_POST['list-cart-id']) : (int) htmlspecialchars($_SESSION['checkout-cart-id'])
];
?>

<?php
/**
 * 
 * ページネーション出力関数
 * 
 * @param object $db
 * @param int $cartId
 * 
 */
function cartDetailPagenation($db, $cartId)
{
    // 最終ページ取得
    $pageMax = (int) ceil(getCartDetailCount($db, $cartId) / ITEM_PER_PAGE_LIST);
    // ページネーション出力
    writePagenation(LINK_COMPLETE, $pageMax);
}
/**
 * 
 * カート明細総数取得関数
 *
 * @param object $db
 * @param int $cartId
 * @return int
 * 
 */
function getCartDetailCount($db, $cartId): int
{
    // SELECT文構築
    $sql = 'SELECT count(*) count FROM ec_cart_detail WHERE cart_id = :cart_id;';
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':cart_id',
            'value' => $cartId
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
 * @param int $cartId
 * @return array
 * 
 */
function getCartTotal($db, $cartId): array
{
    // SELECT文構築
    $sql = 'SELECT sum(qty) qty, sum(price * qty) price FROM ec_cart_detail WHERE cart_id = :cart_id;';
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':cart_id',
            'value' => $cartId
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
 * @return object
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
            ,           ec_products.price           price
            ,           ec_cart_detail.qty          qty
            FROM        ec_cart
            ,           ec_cart_detail
                RIGHT JOIN  ec_products
                    ON  ec_cart_detail.product_id   =   ec_products.id
            WHERE       ec_cart.id              =   :cartId
            AND         ec_cart_detail.cart_id  =   ec_cart.id
            AND         ec_cart.checkout_flg    =   1
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
 * チェックアウト前処理
 * 
 * @param object $db
 * @param int $cartId
 * @return object
 * 
 */
function preCheckout($db, $cartId)
{
    // 変数初期化
    $data = (object) ['err_flg' => false, 'err_msg' => ''];

    // 在庫チェック
    $sql = <<<___SQL___
        SELECT      ec_products.id            product_id
        ,           ec_products.product_name  product_name
        ,           ec_stocks.stock_qty       stock_qty
        ,           ec_cart_detail.qty        order_qty
        FROM        ec_cart_detail
            RIGHT JOIN  ec_products
                ON  ec_cart_detail.product_id   =   ec_products.id
                AND ec_products.public_flg      =   1
            RIGHT JOIN  ec_stocks
                ON  ec_products.id              =   ec_stocks.product_id
        WHERE       ec_cart_detail.cart_id  =   :cartId
        ORDER BY    ec_cart_detail.id;
    ___SQL___;

    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':cartId',
            'value' => $cartId
        ]
    ];

    // SELECT文発行
    $result = db_select($db, $sql, $bind);

    // エラーチェック
    if ($result->err_flg) {
        $data->err_flg = $result->err_flg;
        $data->err_msg = $result->err_msg;
        return $data;
    }

    // カート明細レコード存在チェック
    if (count($result->data) === 0) {
        $data->err_flg = true;
        $data->err_msg = 'ショッピングカートが空です。';
        return $data;
    }

    // 商品チェック
    foreach ($result->data as $item) {
        // 商品存在チェック
        if (empty($item['product_id']) || empty($item['stock_qty'])) {
            $data->err_flg = true;
            $data->err_msg = '商品が存在しません。';
            return $data;
        }
        // 在庫数量チェック
        if ($item['order_qty'] > $item['stock_qty']) {
            $data->err_flg = true;
            $data->err_msg = '在庫が不足しています。商品名：' . $item['product_name'];
            return $data;
        }
    }

    // リターン
    return $data;
}
/**
 * 
 * チェックアウト後処理
 * 
 * @param object $db
 * @param int $cartId
 * @return object
 * 
 */
function postCheckout($db, $cartId)
{
    // 変数初期化
    $data = (object) ['err_flg' => false, 'err_msg' => ''];

    // カート明細レコード取得
    // SELECT文構築
    $sql = <<<___SQL___
        SELECT      ec_products.product_name
        ,           ec_cart_detail.product_id
        ,           ec_cart_detail.qty
        FROM        ec_cart_detail
            RIGHT JOIN  ec_products
                ON  ec_cart_detail.product_id   =   ec_products.id
        WHERE       ec_cart_detail.cart_id  =   :cartId
        ORDER BY    ec_cart_detail.id;
    ___SQL___;

    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':cartId',
            'value' => $cartId
        ]
    ];

    // SELECT文発行
    $result = db_select($db, $sql, $bind);

    // エラーチェック
    if ($result->err_flg) {
        $data->err_flg = $result->err_flg;
        $data->err_msg = $result->err_msg;
        return $data;
    }

    // 在庫更新処理
    // 明細処理
    foreach ($result->data as $item) {
        // UPDATE文構築
        $sql = <<<___SQL___
            UPDATE  ec_stocks
            SET     stock_qty   =   stock_qty - :order_qty
            ,       update_user =   :update_user
            ,       update_date =   NOW()
            WHERE   product_id  =   :product_id;
        ___SQL___;
        // バインドパラメーター設定
        $bind = [
            [
                'name' => ':product_id',
                'value' => $item['product_id']
            ],
            [
                'name' => ':order_qty',
                'value' => $item['qty']
            ],
            [
                'name' => ':update_user',
                'value' => $_SESSION['user-data']['id']
            ]
        ];
        // DB更新処理
        $result = db_execute($db, $sql, $bind, false);
        // エラー処理
        if ($result->err_flg) {
            // 実行結果取得
            $data->err_flg = $result->err_flg;
            $data->err_msg = $result->err_msg;
            // リターン
            return $data;
        }
        // 在庫数量チェック
        $sql = <<<___SQL___
            SELECT  stock_qty
            FROM    ec_stocks
            WHERE   product_id  =   :product_id;
        ___SQL___;
        // バインドパラメーター設定
        $bind = [
            [
                'name' => ':product_id',
                'value' => $item['product_id']
            ]
        ];
        // DB更新処理
        $result = db_select($db, $sql, $bind);
        // エラー処理
        if ($result->err_flg) {
            // 実行結果取得
            $data->err_flg = $result->err_flg;
            $data->err_msg = $result->err_msg;
            // リターン
            return $data;
        }
        // 数量チェック
        if ($result->data[0]['stock_qty'] < 0) {
            // 実行結果取得
            $data->err_flg = $result->err_flg;
            $data->err_msg = '在庫が不足しています。商品名：' . $item['product_name'];;
            // リターン
            return $data;
        }
    }

    // カート更新処理
    // UPDATE文構築
    $sql = <<<___SQL___
        UPDATE  ec_cart
        SET     checkout_flg    =   1
        ,       update_user     =   :update_user
        ,       update_date     =   NOW()
        WHERE   id              =   :id
    ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':id',
            'value' => $cartId
        ],
        [
            'name' => ':update_user',
            'value' => $_SESSION['user-data']['id']
        ]
    ];
    // DB更新処理
    $result = db_execute($db, $sql, $bind, false);
    // エラー処理
    if ($result->err_flg) {
        // 実行結果取得
        $data->err_flg = $result->err_flg;
        $data->err_msg = $result->err_msg . $sql;
        // リターン
        return $data;
    }

    // リターン
    return $data;
}
?>

<?php
// POST時の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF対策：トークンチェック
    if (checkCSRF(isset($_POST['csrf-token']) ? $_POST['csrf-token'] : null)) {
        // ボタン処理
        if ($_POST['action'] === 'checkout') {
            // トランザクション開始
            $db->beginTransaction();

            // 決済前処理
            $result = preCheckout($db, $cart->id);
            // メッセージ設定
            if ($result->err_flg) {
                // ロールバック
                $db->rollback();
                // メッセージ設定
                $_SESSION['message'] = $result->err_msg;
                // リロード
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            // 決済処理
            // TODO : ここに実際の決済処理を入れる？

            // 決済後処理
            $result = postCheckout($db, $cart->id);
            // メッセージ設定
            if ($result->err_flg) {
                // ロールバック
                $db->rollback();
                // メッセージ設定
                $_SESSION['message'] = $result->err_msg;
                // リロード
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            // 確定処理
            $db->commit();

            // メッセージ設定
            $_SESSION['message'] = '購入できました。ありがとうございました。';

            // 保存されているカートIDをクリア
            clearCartId();

            // リロード
            header('Location: ' . LINK_COMPLETE);
            exit;
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