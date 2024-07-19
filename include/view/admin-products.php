<!DOCTYPE html>
<html lang="ja">

<head>
  <?php include_once '../../include/view/common.php' ?>
  <title>商品管理：ECサイト</title>
</head>

<style>
.form-area-button {
  width: 50%;
  display: flex;
  flex-direction: row;
  margin: 0 auto;
  padding: 0;
}

.form-area-input {
  width: 50%;
  display: flex;
  flex-direction: row;
  margin: 0 auto;
  padding: 0;
}

.upload-image {
  width: 280px;
  height: 280px;
  display: flex;
  flex-grow: 0;
  flex-shrink: 0;
  margin: auto 0;
  border: 1px solid #282d33;
}

.upload-image img {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
  overflow: hidden;
}

.list-upload-image {
  width: 200px;
  height: 200px;
  display: flex;
  flex-grow: 0;
  flex-shrink: 0;
  margin: auto 0;
  border: 1px solid #282d33;
}

.list-upload-image img {
  max-width: 100%;
  height: auto;
  display: flex;
  object-fit: cover;
  overflow: hidden;
}

.list-product-private {
  background-color: #798799;
}
</style>

<style>
@media screen and (max-width: 799px) {
  .form-area-button {
    height: 2rem;
    line-height: 2rem;
  }

  .form-area-input {
    height: 2rem;
    line-height: 2rem;
  }

  .upload-image {
    width: 180px;
    height: 180px;
  }

  .list-upload-image {
    width: 160px;
    height: 160px;
  }
}
</style>

<script>
// ------------------------------------------------------------
// 入力項目チェック関数
// ------------------------------------------------------------
const validate = (event, form, mode) => {
  // フォーム要素ID接頭辞
  const id = mode === 'INSERT' ? 'form-products-' : 'list-products-';
  // 名称チェック
  if (!checkProductName(form.elements[id + 'product-name'].id)) {
    // エラー報告
    form.reportValidity();
    // デフォルトアクションをキャンセル
    event.preventDefault();
    return;
  };
  // 数量チェック
  if (!checkProductQty(form.elements[id + 'product-qty'].id)) {
    // エラー報告
    form.reportValidity();
    // デフォルトアクションをキャンセル
    event.preventDefault();
    return;
  };
  // 価格チェック
  if (!checkProductPrice(form.elements[id + 'product-price'].id)) {
    // エラー報告
    form.reportValidity();
    // デフォルトアクションをキャンセル
    event.preventDefault();
    return;
  };
  // 画像チェック
  if (!checkProductImage(form.elements[id + 'product-image'].id)) {
    // エラー報告
    form.reportValidity();
    // デフォルトアクションをキャンセル
    event.preventDefault();
    return;
  };
}
// ------------------------------------------------------------
// 画面ロード時実行関数
// ------------------------------------------------------------
window.addEventListener('DOMContentLoaded', () => {
  // 画像ロード処理（登録フォーム用）
  loadImage('form-products-product-image', 'form-upload-image');
  // 画像ロード処理（更新フォーム用）
  list = [<?php writeProductId($db); ?>];
  list.forEach(id => {
    loadImage('list-products-product-image-' + id, 'list-upload-image-' + id);
  });
  // バリデーション（登録フォーム用）
  const formCreate = document.forms['form-product'];
  const btnCreate = formCreate.elements['action'];
  btnCreate.addEventListener('click', (event) => validate(event, formCreate, 'INSERT'), false);
  // バリデーション（更新フォーム用）
  const formUpdate = document.getElementsByClassName('list-form-product');
  Array.from(formUpdate).forEach((form) => {
    const btns = form.elements['action'];
    btns.forEach((btn) => {
      if (btn.value === 'update') {
        btn.addEventListener('click', (event) => validate(event, form, 'UPDATE'), false);
      }
    });
  });
}, false);
</script>

<body>
  <?php include_once '../../include/view/header.php'; ?>
  <div class="wrapper">
    <div class="container">
      <div class="page-title">
        <h2>商品管理</h2>
      </div>
      <?php include_once '../../include/view/message.php'; ?>
      <div class="container-item container-item-frame">
        <div class="form-wrapper">
          <div class="upload-image">
            <img id="form-upload-image">
          </div>
          <form id="form-product" class="form-register" action="./admin-products.php" method="POST"
            enctype="multipart/form-data">
            <input type="hidden" name="csrf-token" value="<?php echo $token; ?>">
            <div class="form-register-item">
              <label class="ec-label-short" for="form-products-product-name">名称</label>
              <span class="ec-input-short">
                <input class="ec-input-text" type="text" id="form-products-product-name"
                  name="form-products-product-name" value="<?php echo $formProduct->name; ?>" maxlength="64">
              </span>
            </div>
            <div class="form-register-item">
              <label class="ec-label-short" for="form-products-product-qty">数量</label>
              <span class="ec-input-short">
                <input class="ec-input-number" type="number" id="form-products-product-qty"
                  name="form-products-product-qty" value=<?php echo $formProduct->qty; ?>>
                <label class="ec-input-number-unit" for="form-products-product-qty">個</label>
              </span>
            </div>
            <div class="form-register-item">
              <label class="ec-label-short" for="form-products-product-price">価格</label>
              <span class="ec-input-short">
                <input class="ec-input-number" type="number" id="form-products-product-price"
                  name="form-products-product-price" value=<?php echo $formProduct->price; ?>>
                <label class="ec-input-number-unit" for="form-products-product-price">円</label>
              </span>
            </div>
            <div class="form-register-item">
              <label class="ec-label-short" for="form-products-product-image">画像</label>
              <span class="ec-input-short">
                <input class="ec-input-file" type="file" id="form-products-product-image"
                  name="form-products-product-image" accept="image/png, image/jpeg">
              </span>
            </div>
            <div class="form-register-item">
              <div class="form-area-input">
                <input class="ec-input-checkbox" type="checkbox" id="form-products-product-public"
                  name="form-products-product-public[]" value="public"
                  <?php echo $formProduct->public === 1 ? 'checked' : ''; ?>>
                <label class="ec-input-checkbox-label" for="form-products-product-public">公開</label>
              </div>
              <div class="form-area-button">
                <button class="ec-button" type="submit" form="form-product" name="action" value="create">
                  <span class="ec-button-text-small">登録</span>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
      <div class="container-item">
        <?php include_once '../../include/view/message.php'; ?>
        <div class="list-update">
          <?php $result = getProductRecord($db); ?>
          <?php foreach ($result->data as $item) : ?>
          <div class="list-update-wrapper  <?php echo $item['public_flg'] === 0 ? 'list-product-private' : ''; ?>">
            <div class="list-upload-image">
              <img id="list-upload-image-<?php echo $item['product_id']; ?>"
                src="./image.php?id=<?php echo $item['product_id']; ?>">
            </div>
            <form id="list-form-product-<?php echo $item['product_id']; ?>" class="list-update-form"
              action="./admin-products.php" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="csrf-token" value="<?php echo $token; ?>">
              <input type="hidden" name="list-products-product-id" value="<?php echo $item['product_id'] ?>">
              <input type="hidden" name="list-products-stock-id" value="<?php echo $item['stock_id'] ?>">
              <input type="hidden" name="list-products-product-public" value="<?php echo $item['public_flg'] ?>">
              <div class="list-update-form-item">
                <div class="list-update-form-item-input">
                  <label class="ec-label-short"
                    for="list-products-product-name-<?php echo $item['product_id']; ?>">名称</label>
                  <span class="ec-input-short">
                    <input class="ec-input-text" type="text"
                      id="list-products-product-name-<?php echo $item['product_id']; ?>"
                      name="list-products-product-name" value="<?php echo $item['name']; ?>" maxlength="64">
                  </span>
                </div>
                <div class="list-update-form-item-input">
                  <label class="ec-label-short"
                    for="list-products-product-qty-<?php echo $item['product_id']; ?>">数量</label>
                  <span class="ec-input-short">
                    <input class="ec-input-number" type="number"
                      id="list-products-product-qty-<?php echo $item['product_id']; ?>" name="list-products-product-qty"
                      value=<?php echo $item['qty']; ?>>
                    <label class="ec-input-number-unit"
                      for="list-products-product-qty-<?php echo $item['product_id']; ?>">個</label>
                  </span>
                </div>
                <div class="list-update-form-item-input">
                  <label class="ec-label-short"
                    for="list-products-product-price-<?php echo $item['product_id']; ?>">価格</label>
                  <span class="ec-input-short">
                    <input class="ec-input-number" type="number"
                      id="list-products-product-price-<?php echo $item['product_id']; ?>"
                      name="list-products-product-price" value=<?php echo $item['price']; ?>>
                    <label class="ec-input-number-unit"
                      for="list-products-product-price-<?php echo $item['product_id']; ?>">円</label>
                  </span>
                </div>
                <div class="list-update-form-item-input">
                  <label class="ec-label-short"
                    for="list-products-product-image-<?php echo $item['product_id']; ?>">画像</label>
                  <span class="ec-input-short">
                    <input class="ec-input-file" type="file"
                      id="list-products-product-image-<?php echo $item['product_id']; ?>"
                      name="list-products-product-image" accept="image/png, image/jpeg">
                  </span>
                </div>
              </div>
              <div class="list-update-form-item-buttons">
                <div class="list-update-form-item-button">
                  <button class="ec-button" type="submit" form="list-form-product-<?php echo $item['product_id']; ?>"
                    name="action" value="publish">
                    <span class="ec-button-text-small"><?php echo $item['public_flg'] === 0 ? '公開' : '非公開'; ?></span>
                  </button>
                </div>
                <div class="list-update-form-item-button">
                  <button class="ec-button" type="submit" form="list-form-product-<?php echo $item['product_id']; ?>"
                    name="action" value="update">
                    <span class="ec-button-text-small">更新</span>
                  </button>
                </div>
              </div>
            </form>
          </div>
          <?php endforeach ?>
        </div>
        <div class="pagenation">
          <?php productsPagenation($db); ?>
        </div>
      </div>
    </div>
  </div>
  <?php include_once '../../include/view/footer.php'; ?>
</body>

</html>