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
$formProduct = (object) [
    'name'      => isset($_POST['form-products-product-name']) ? htmlspecialchars($_POST['form-products-product-name']) : '',
    'qty'       => isset($_POST['form-products-product-qty']) ? (int) htmlspecialchars($_POST['form-products-product-qty']) : '',
    'price'     => isset($_POST['form-products-product-price']) ? (int) htmlspecialchars($_POST['form-products-product-price']) : '',
    'image'     => isset($_FILES['form-products-product-image']) ? $_FILES['form-products-product-image'] : '',
    'public'    => isset($_POST['form-products-product-public']) ? 1 : 0
];
// POSTされた入力項目を格納（更新フォーム）
$listProduct = (object)[
    'productId' => isset($_POST['list-products-product-id']) ? (int) htmlspecialchars($_POST['list-products-product-id']) : null,
    'stockId'   => isset($_POST['list-products-stock-id']) ? (int) htmlspecialchars($_POST['list-products-stock-id']) : null,
    'name'      => isset($_POST['list-products-product-name']) ? htmlspecialchars($_POST['list-products-product-name']) : null,
    'qty'       => isset($_POST['list-products-product-qty']) ? (int) htmlspecialchars($_POST['list-products-product-qty']) : null,
    'price'     => isset($_POST['list-products-product-price']) ? (int) htmlspecialchars($_POST['list-products-product-price']) : null,
    'image'     => isset($_FILES['list-products-product-image']) ? $_FILES['list-products-product-image'] : null,
    'public'    => isset($_POST['list-products-product-public']) ? htmlspecialchars($_POST['list-products-product-public']) : null
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
    $pageMax = (int) ceil(getProductsCount($db) / ITEM_PER_PAGE_LIST);
    // ページネーション出力
    writePagenation(LINK_ADMIN_PRODUCTS, $pageMax);
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
        ORDER BY    id
        LIMIT       :offset
        ,           :limit;
        ___SQL___;
    // バインドパラメーター設定
    $bind = [
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
    if (!$record->err_flg) {
        foreach ($record->data as $item) {
            echo $item['id'] . ',';
        }
    }
}

/**
 * 
 * バリデーション関数（商品管理）
 *
 * @param object $db
 * @param object $product
 * @param string $mode INSERT | UPDATE
 * @return object
 *
 */
function Validate($product, $mode): object
{
    // 変数初期化
    $obj_error = (object) ['err_flg' => false, 'err_msg' => ''];

    // 未入力チェック
    if (!isset($product->name) || !isset($product->qty)) {
        $obj_error->err_flg = true;
        $obj_error->err_msg = '未入力の項目があります。';
        return $obj_error;
    }

    // 商品名称チェック
    if (strlen($product->name) > 64) {
        $obj_error->err_flg = true;
        $obj_error->err_msg = '名称は64文字以内の半角英数字で入力してください。';
        return $obj_error;
    }

    // 商品価格チェック
    if ($product->price < 0) {
        $obj_error->err_flg = true;
        $obj_error->err_msg = '価格は0以上の数値を入力してください。';
        return $obj_error;
    }

    // 商品数量チェック
    if ($product->qty < 0) {
        $obj_error->err_flg = true;
        $obj_error->err_msg = '数量は0以上の数値を入力してください。';
        return $obj_error;
    }

    // 商品画像チェック
    switch ($mode) {
        case 'INSERT':
            // 未選択チェック
            if ($product->image['tmp_name'] === '') {
                $obj_error->err_flg = true;
                $obj_error->err_msg = '画像が選択されていません。';
                return $obj_error;
            }
            // 形式チェック
            if ($product->image['type'] !== 'image/jpeg' && $product->image['type'] !== 'image/png') {
                $obj_error->err_flg = true;
                $obj_error->err_msg = '画像はJPEGまたはPNGを選択してください。';
                return $obj_error;
            }
            break;
        case 'UPDATE':
            if (!empty($product->image['tmp_name'])) {
                if ($product->image['type'] !== 'image/jpeg' && $product->image['type'] !== 'image/png') {
                    $obj_error->err_flg = true;
                    $obj_error->err_msg = '画像はJPEGまたはPNGを選択してください。';
                    return $obj_error;
                }
            }
            break;
        default:
            break;
    }

    // リターン
    return $obj_error;
}
/**
 * 
 * 商品テーブル：公開フラグ更新関数
 * 
 * @param object $db        DB接続情報
 * @param object $product   商品レコード
 *
 * @return object
 * 
 */
function setProductPublish($db, $product): object
{
    // 変数初期化
    $data = (object) ['data' => null, 'err_flg' => false, 'err_msg' => ''];
    // SQL文生成
    $sql = <<<___SQL___
        UPDATE  ec_products
        SET     public_flg  =   :public_flg
        ,       update_user =   :update_user
        ,       update_date =   NOW()
        WHERE   id          =   :id;
        ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':id',
            'value' => $product->productId
        ],
        [
            'name' => ':public_flg',
            'value' => $product->public === "1" ? 0 : 1
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
 * 商品総数取得関数
 *
 * @param object $db
 * @return int
 */
function getProductsCount($db): int
{
    // SELECT文構築
    $sql = 'SELECT count(*) count FROM ec_products;';
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
 * @return object
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
        ,           ec_products.public_flg          public_flg
        FROM        ec_products
        ,           ec_stocks
        WHERE       ec_products.id  =   ec_stocks.product_id
        ORDER BY    ec_products.id
        LIMIT       :offset
        ,           :limit;
        ___SQL___;
    // バインドパラメーター設定
    $bind = [
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
 * 商品レコード登録関数
 *
 * @param object $db
 * @param object $product
 * @return mixed
 */
function createProductRecord($db, $product)
{
    // 変数初期化
    $data = (object) ['data' => null, 'err_flg' => false, 'err_msg' => ''];
    // トランザクション開始
    $db->beginTransaction();
    // 画像ファイル
    $content = file_get_contents($product->image['tmp_name']);
    $type = $product->image['type'];
    // INSERT文構築：商品テーブル
    $sql = <<<___SQL___
        INSERT INTO ec_products (
            product_name,
            product_image,
            product_image_type,
            price,
            public_flg,
            create_user,
            create_date,
            update_user,
            update_date
        )
        VALUES (
            :product_name,
            :product_image,
            :product_image_type,
            :price,
            :public_flg,
            :create_user,
            NOW(),
            :update_user,
            NOW()
        );
    ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':product_name',
            'value' => $product->name
        ],
        [
            'name' => ':product_image',
            'value' => $content
        ],
        [
            'name' => ':product_image_type',
            'value' => $type
        ],
        [
            'name' => ':price',
            'value' => $product->price
        ],
        [
            'name' => ':public_flg',
            'value' => $product->public
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
    // INSERT文構築：在庫テーブル
    $sql = <<<___SQL___
        INSERT INTO ec_stocks (
            product_id,
            stock_qty,
            create_user,
            create_date,
            update_user,
            update_date
        )
        VALUES (
            :product_id,
            :stock_qty,
            :create_user,
            NOW(),
            :update_user,
            NOW()
        );
    ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':product_id',
            'value' => (int) $db->lastInsertId()
        ],
        [
            'name' => ':stock_qty',
            'value' => (int) $product->qty
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
    // コミット
    $db->commit();
    // リターン
    return $data;
}
/**
 * 
 * 商品レコード更新関数
 *
 * @param object $db
 * @param object $product
 * @return mixed
 */
function updateProductRecord($db, $product)
{
    // 変数初期化
    $data = (object) ['data' => null, 'err_flg' => false, 'err_msg' => ''];
    // トランザクション開始
    $db->beginTransaction();
    // UPDATE文構築：商品テーブル（商品情報）
    $sql = <<<___SQL___
            UPDATE  ec_products
            SET     product_name        =   :product_name
            ,       price               =   :price
            ,       public_flg          =   :public_flg
            ,       update_user         =   :update_user
            ,       update_date         =   NOW()
            WHERE   id                  =   :id
        ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':id',
            'value' => $product->productId
        ],
        [
            'name' => ':product_name',
            'value' => $product->name
        ],
        [
            'name' => ':price',
            'value' => $product->price
        ],
        [
            'name' => ':public_flg',
            'value' => $product->public
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
        // ロールバック
        $db->rollback();
        // 実行結果取得
        $data->err_flg = $result->err_flg;
        $data->err_msg = $result->err_msg;
        // リターン
        return $data;
    }
    // UPDATE文構築：商品テーブル（商品画像）
    if (!empty($product->image['tmp_name'])) {
        // 画像データー取得
        $content = file_get_contents($product->image['tmp_name']);
        $type = $product->image['type'];
        $sql = <<<___SQL___
                UPDATE  ec_products
                SET     product_image       =   :product_image
                ,       product_image_type  =   :product_image_type
                ,       update_user         =   :update_user
                ,       update_date         =   NOW()
                WHERE   id                  =   :id;
            ___SQL___;
        // バインドパラメーター設定
        $bind = [
            [
                'name' => ':id',
                'value' => $product->productId
            ],
            [
                'name' => ':product_image',
                'value' => $content
            ],
            [
                'name' => ':product_image_type',
                'value' => $type
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
            // ロールバック
            $db->rollback();
            // 実行結果取得
            $data->err_flg = $result->err_flg;
            $data->err_msg = $result->err_msg;
            // リターン
            return $data;
        }
    }
    // UPDATE文構築：在庫テーブル
    $sql = <<<___SQL___
            UPDATE  ec_stocks
            SET     stock_qty   =   :stock_qty
            ,       update_user =   :update_user
            ,       update_date =   NOW()
            WHERE   id          =   :id;
        ___SQL___;
    // バインドパラメーター設定
    $bind = [
        [
            'name' => ':id',
            'value' => $product->stockId
        ],
        [
            'name' => ':stock_qty',
            'value' => $product->qty
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
    // CSRF対策
    if (checkCSRF(isset($_POST['csrf-token']) ? $_POST['csrf-token'] : null)) {
        // ボタン処理
        switch ($_POST['action']) {
            case 'admin-products':
                // リロード
                header('Location: ' . LINK_ADMIN_PRODUCTS);
                exit;
            case 'create':
                // バリデーション
                $result = Validate($formProduct, 'INSERT');
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit;
                }
                // 登録処理
                $result = createProductRecord($db, $formProduct);
                // メッセージ設定
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                } else {
                    $_SESSION['message'] = '商品を登録しました。';
                }
                // リロード
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            case 'update':
                // バリデーション
                $result = Validate($listProduct, 'UPDATE');
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit;
                }
                // 更新処理
                $result = updateProductRecord($db, $listProduct);
                // メッセージ設定
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                } else {
                    $_SESSION['message'] = '商品を更新しました。';
                }
                // リロード
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            case 'publish':
                // 公開フラグ更新処理
                $result = setProductPublish($db, $listProduct);
                // メッセージ設定
                if ($result->err_flg) {
                    $_SESSION['message'] = $result->err_msg;
                } else {
                    switch ($listProduct->public) {
                        case 0:
                            $_SESSION['message'] = '商品を公開しました。';
                            break;
                        case 1:
                            $_SESSION['message'] = '商品を非公開にしました。';
                            break;
                        default:
                            // $_SESSION['message'] = $listProduct->public;
                            break;
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