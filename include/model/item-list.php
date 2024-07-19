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
$listProduct = (object)[
    'productId'     => isset($_POST['list-order-product-id']) ? (int) htmlspecialchars($_POST['list-order-product-id']) : null,
    'productName'   => isset($_POST['list-order-product-name']) ? htmlspecialchars($_POST['list-order-product-name']) : null,
    'stockId'       => isset($_POST['list-order-stock-id']) ? (int) htmlspecialchars($_POST['list-order-stock-id']) : null,
    'price'         => isset($_POST['list-order-price']) ? (int) htmlspecialchars($_POST['list-order-price']) : null,
    'qty'           => isset($_POST['list-order-qty']) ? (int) htmlspecialchars($_POST['list-order-qty']) : null
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
function productsPagenation($db)
{
    // 最終ページ取得
    $pageMax = (int) ceil(getProductsCount($db) / ITEM_PER_PAGE_THUMB);
    // ページネーション出力
    writePagenation(LINK_ITEM_LIST, $pageMax);
}
/**
 * 
 * リスト用商品ID出力関数
 * 
 * @param object $db
 * 
 */
function writeProductId($db)
{
    // パラメーター取得
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    // SELECT文構築
    $sql = <<<___SQL___
        SELECT      id
        FROM        ec_products
        WHERE       public_flg  = 1
        ORDER BY    id
        LIMIT       :offset
        ,           :limit;
        ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':offset',
            'value' => ITEM_PER_PAGE_THUMB * ($page - 1)
        ],
        [
            'name' => ':limit',
            'value' => ITEM_PER_PAGE_THUMB
        ]
    ];
    // SELECT文発行
    $record = db_select($db, $sql, $bind);
    // リターン
    if (!$record->err_flg) {
        foreach ($record->data as $item) {
            echo $item['id'] . ',';
        }
    }
}

/**
 * 
 * バリデーション関数（商品一覧）
 *
 * @param object $db
 * @param object $product
 * @return object
 *
 */
function Validate($db, $product): object
{
    // 変数初期化
    $obj_error = (object) ['err_flg' => false, 'err_msg' => ''];

    // 数量チェック
    if ($product->qty < 1) {
        $obj_error->err_flg = true;
        $obj_error->err_msg = '数量は1以上の整数を入力してください。';
        return $obj_error;
    }

    // 商品データー取得
    $result = getProductData($db, $product);
    if ($result->err_flg) {
        $obj_error->err_flg = $result->err_flg;
        $obj_error->err_msg = $result->err_msg;
        return $obj_error;
    }

    // 在庫チェック
    if ($result->data[0]['qty'] < $product->qty) {
        $obj_error->err_flg = true;
        $obj_error->err_msg = '在庫が不足しています。';
        return $obj_error;
    }

    // リターン
    return $obj_error;
}
/**
 * 
 * 商品総数取得関数
 *
 * @param object $db
 * @return int
 */
function getProductsCount($db): int
{
    // SELECT文構築
    $sql = 'SELECT count(*) count FROM ec_products WHERE public_flg = 1;';
    // バインドパラメーター設定
    $bind = [];
    // SELECT文発行
    $record = db_select($db, $sql, $bind);
    // リターン
    return (int) $record->data[0]['count'];
}
/**
 * 
 * 商品レコード取得関数
 *
 * @param object $db
 * @param object $product
 * @return object
 */
function getProductData($db, $product)
{
    // SELECT文構築
    $sql = <<<___SQL___
        SELECT      ec_products.id          product_id
        ,           ec_stocks.id            stock_id
        ,           ec_products.price       price
        ,           ec_stocks.stock_qty     qty
        FROM        ec_products
        ,           ec_stocks
        WHERE       ec_products.id          =   :product_id
        AND         ec_products.id          =   ec_stocks.product_id;
        ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':product_id',
            'value' => $product->productId
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
 * 商品レコード取得関数
 *
 * @param object $db
 * @return mixed
 */
function getProductRecord($db)
{
    // パラメーター取得
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    // SELECT文構築
    $sql = <<<___SQL___
        SELECT      ec_products.id                  product_id
        ,           ec_stocks.id                    stock_id
        ,           ec_products.product_name        name
        ,           ec_products.product_image       image
        ,           ec_products.product_image_type  image_type
        ,           ec_products.price               price
        ,           ec_stocks.stock_qty             qty
        FROM        ec_products
        ,           ec_stocks
        WHERE       ec_products.id          =   ec_stocks.product_id
        AND         ec_products.public_flg  =   1
        ORDER BY    ec_products.id
        LIMIT       :offset
        ,           :limit;
        ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':offset',
            'value' => ITEM_PER_PAGE_THUMB * ($page - 1)
        ],
        [
            'name' => ':limit',
            'value' => ITEM_PER_PAGE_THUMB
        ]
    ];
    // SELECT文発行
    $record = db_select($db, $sql, $bind);
    echo $record->err_msg;
    // リターン
    if ($record->err_flg) {
        return null;
    } else {
        return $record;
    }
}
/**
 * 
 * カートレコード登録関数
 *
 * @param object $db
 * @param object $product
 * @return mixed
 */
function createCartRecord($db, $product)
{
    // 変数初期化
    $data = (object) ['err_flg' => false, 'err_msg' => ''];
    // トランザクション開始
    $db->beginTransaction();
    // カートレコート登録処理
    if (!isset($_SESSION['cart-id'])) {
        // cookie保存期間を算出
        $cookie_expiration = time() + COOKIE_EXPIRATION_PERIOD;
        // INSERT文構築：カートテーブル
        $sql = <<<___SQL___
            INSERT INTO ec_cart (
                user_id,
                checkout_flg,
                create_user,
                create_date,
                update_user,
                update_date
            )
            VALUES (
                :user_id,
                0,
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
                'value' => $_SESSION['user-data']['id']
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
        $result = db_execute($db, $sql, $bind, false);
        if ($result->err_flg) {
            // ロールバック
            $db->rollback();
            // 実行結果取得
            $data->err_flg = $result->err_flg;
            $data->err_msg = $result->err_msg;
            // リターン
            return $data;
        }
        // カートIDを保存
        $_SESSION['cart-id'] = $db->lastInsertId();
        $_SESSION['checkout-cart-id'] = $_SESSION['cart-id'];
    }
    // カート明細レコード登録処理
    $sql = <<<___SQL___
        INSERT INTO ec_cart_detail (
            cart_id,
            product_id,
            price,
            qty,
            create_user,
            create_date,
            update_user,
            update_date
        )
        VALUES (
            :insert_cart_id,
            :insert_product_id,
            :insert_price,
            :insert_qty,
            :insert_create_user,
            NOW(),
            :insert_update_user,
            NOW()
        )
        ON DUPLICATE KEY UPDATE
            price       =   :update_price,
            qty         =   qty + :update_qty,
            update_user =   :update_update_user,
            update_date =   NOW();
    ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':insert_cart_id',
            'value' => $_SESSION['cart-id']
        ],
        [
            'name' => ':insert_product_id',
            'value' => $product->productId
        ],
        [
            'name' => ':insert_price',
            'value' => $product->price
        ],
        [
            'name' => ':insert_qty',
            'value' => $product->qty
        ],
        [
            'name' => ':insert_create_user',
            'value' => $_SESSION['user-data']['id']
        ],
        [
            'name' => ':insert_update_user',
            'value' => $_SESSION['user-data']['id']
        ],
        [
            'name' => ':update_price',
            'value' => $product->price
        ],
        [
            'name' => ':update_qty',
            'value' => $product->qty
        ],
        [
            'name' => ':update_update_user',
            'value' => $_SESSION['user-data']['id']
        ]
    ];
    // DB登録処理
    $result = db_execute($db, $sql, $bind, false);
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
            case 'to-cart':
                // バリデーション
                $result = Validate($db, $listProduct);
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit;
                }
                // 登録処理
                $result = createCartRecord($db, $listProduct);
                // メッセージ設定
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                } else {
                    $_SESSION['message'] = 'カートに商品を追加しました： ' . $listProduct->productName . ' ' . $listProduct->qty . '点';
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