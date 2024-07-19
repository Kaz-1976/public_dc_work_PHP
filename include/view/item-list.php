<!DOCTYPE html>
<html lang="ja">

<head>
  <?php include_once '../../include/view/common.php' ?>
  <title>商品一覧：ECサイト</title>
</head>

<style>
.list-products {
  width: 100%;
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  justify-content: start;
  margin: auto;
}

.list-item {
  width: 25%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 16px;
}

.list-name {
  width: 100%;
  height: 2rem;
  line-height: 2rem;
  display: flex;
  flex-direction: row;
  align-content: space-between;
  justify-content: space-between;
  margin: auto;
}

.list-name p {
  display: flex;
  margin: auto;
  font-size: 1.5rem;
  font-weight: bold;
  white-space: nowrap;
  overflow: hidden
}

.list-data {
  width: 100%;
  display: flex;
  flex-direction: column;
  margin: auto;
}

.list-image {
  width: auto;
  height: auto;
  display: flex;
}

.list-image img {
  width: 260px;
  height: auto;
  display: flex;
  margin: 0 auto;
  padding: 0;
  object-fit: cover;
  object-position: center;
  overflow: hidden;
}

.list-form {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.list-input {
  width: 100%;
  height: 2rem;
  line-height: 2rem;
  display: flex;
  flex-direction: row;
}

.list-button {
  width: 100%;
  height: 2rem;
  display: flex;
  vertical-align: middle;
  background-color: #a1b4cc;
  border: 0;
  border-radius: 4px;
}

.list-button i {
  display: flex;
  margin: auto;
  font-size: 1.25rem;
  font-weight: bold;
  color: #282d33;
}
</style>

<style>
@media screen and (max-width: 799px) {
  .list-item {
    width: 50%;
  }

  .list-name p {
    font-size: 1.25rem;
  }

}
</style>

<script>
// ------------------------------------------------------------
// 入力項目チェック関数
// ------------------------------------------------------------
const validate = (event, form) => {
  // 数量チェック
  if (!checkOrderQty(form.elements['list-order-qty'].id)) {
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
  // バリデーション
  const formUpdate = document.getElementsByClassName('list-form');
  Array.from(formUpdate).forEach((form) => {
    const btn = form.elements['action'];
    btn.addEventListener('click', (event) => validate(event, form), false);
  });
}, false);
</script>

<body>
  <?php include_once '../../include/view/header.php'; ?>
  <div class="wrapper">
    <div class="container">
      <div class="page-title">
        <h2>商品一覧</h2>
      </div>
      <?php include_once '../../include/view/message.php'; ?>
      <div class="container-item">
        <div class="list-products">
          <?php $result = getProductRecord($db); ?>
          <?php foreach ($result->data as $item) : ?>
          <div class="list-item">
            <div class="list-name">
              <p><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="list-image">
              <img src="./image.php?id=<?php echo $item['product_id']; ?>">
            </div>
            <div class="list-data">
              <div class="list-name">
                <span class="ec-label-list">価格</span>
                <div class="ec-input-list" inert>
                  <input class="ec-input-number ec-input-no-border" type="number" readonly
                    value=<?php echo htmlspecialchars($item['price'], ENT_QUOTES, 'UTF-8'); ?>>
                  <div class="ec-input-number-unit">円</div>
                </div>
              </div>
              <div class="list-name">
                <span class="ec-label-list">在庫</span>
                <div class="ec-input-list" inert>
                  <input class="ec-input-number ec-input-no-border" type="number" readonly
                    value=<?php echo htmlspecialchars($item['qty'], ENT_QUOTES, 'UTF-8'); ?>>
                  <div class="ec-input-number-unit">個</div>
                </div>
              </div>
              <form class="list-form" id="list-form-<?php echo $item['product_id']; ?>" action="./item-list.php"
                method="POST">
                <input type="hidden" name="csrf-token" value="<?php echo $token; ?>">
                <input type="hidden" name="list-order-product-id" value="<?php echo $item['product_id']; ?>">
                <input type="hidden" name="list-order-product-name" value="<?php echo $item['name']; ?>">
                <input type="hidden" name="list-order-stock-id" value="<?php echo $item['stock_id']; ?>">
                <input type="hidden" name="list-order-price" value="<?php echo $item['price']; ?>">
                <div class="list-input">
                  <label class="ec-label-list" for="list-form-qty-<?php echo $item['product_id']; ?>">数量</label>
                  <div class="ec-input-list">
                    <input class="ec-input-number" id="list-form-qty-<?php echo $item['product_id']; ?>" type="number"
                      name="list-order-qty" value="1" <?php echo $item['qty'] === 0 ? 'disabled' : ''; ?>>
                    <div class="ec-input-number-unit">個</div>
                  </div>
                </div>
                <div>
                  <?php if ($item['qty'] === 0) : ?>
                  <button class="list-button" disabled>
                    <i class="fa-solid fa-minus"></i>
                  </button>
                  <?php else : ?>
                  <button class="list-button" type="submit" form="list-form-<?php echo $item['product_id']; ?>"
                    name="action" value="to-cart">
                    <i class="fa-solid fa-cart-arrow-down fa-2xl" alt="カートに入れる"></i>
                  </button>
                  <?php endif; ?>
                </div>
              </form>
            </div>
          </div>
          <?php endforeach ?>
          <div class="pagenation">
            <?php productsPagenation($db); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php include_once '../../include/view/footer.php'; ?>
</body>

</html>