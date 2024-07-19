<!DOCTYPE html>
<html lang="ja">

<head>
  <?php include_once '../../include/view/common.php' ?>
  <title>ショッピングカート：ECサイト</title>
</head>

<style>
.page-navi {
  width: 100%;
  height: 3rem;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  margin: 0;
  padding: 4px 0;
  gap: 4px;
}

.page-navi-item {
  width: calc(100% / 3);
  height: 100%;
  display: flex;
}

.page-button {
  width: 100%;
  height: 100%;
  display: flex;
  margin: 0;
  padding: 0 4px;
  vertical-align: middle;
  background-color: #a1b4cc;
  border: 0;
  border-radius: 4px;
}

.page-button-text {
  display: flex;
  margin: auto;
  font-size: 24px;
  font-weight: bold;
  color: #282d33;
}

.page-message {
  width: 100%;
  display: flex;
  margin: 8px 0;
  padding: 64px 0;
  border-top: 2px solid #282d33;
  border-bottom: 2px solid #282d33;
}

.page-message-text {
  display: flex;
  margin: auto;
  font-size: 32px;
  font-weight: bold;
  color: #282d33;
}

.page-total {
  width: 100%;
  display: flex;
  margin: 8px 0;
  padding: 8px 0;
  border-top: 2px solid #282d33;
  border-bottom: 2px solid #282d33;
}

.page-total-label {
  display: flex;
  margin: auto;
  font-size: 32px;
  font-weight: bold;
  color: #282d33;
}

.page-total-value {
  display: flex;
  margin: auto;
  font-size: 32px;
  font-weight: bold;
  color: #282d33;
}

.list-cart {
  width: 100%;
  display: flex;
  flex-direction: column;
  margin: auto;
}

.list-form-cart-detail {
  width: 100%;
  height: 16rem;
  display: flex;
  flex-direction: row;
  margin: 0px auto;
  padding: 4px;
  gap: 8px;
  border-top: 2px solid #282d33;
}

.list-form-input {
  width: 80%;
  display: flex;
  gap: 8px;
}

.list-form-left {
  width: 220px;
  height: 100%;
  display: flex;
  padding: 8px 0px;
}

.list-form-right {
  width: calc((100% - 220px));
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.list-form-image {
  width: 220px;
  height: 220px;
  display: flex;
  margin: 4px;
  border: 1px solid #282d33;
}

.list-form-image img {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
  overflow: hidden;
}

.list-form-item {
  width: 100%;
  height: 3rem;
  line-height: 3rem;
  display: flex;
  flex-direction: row;
  margin: auto;
  gap: 4px;
}

.list-form-buttons {
  width: 20%;
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 0 4px;
}

.list-form-button {
  width: 100%;
  height: 100%;
  display: flex;
}
</style>

<style>
@media screen and (max-width: 799px) {
  .page-button-text {
    font-size: 20px;
  }

  .page-message-text {
    font-size: 24px;
  }

  .page-total-label {
    font-size: 24px;
  }

  .page-total-value {
    font-size: 24px;
  }

  .list-form-cart-detail {
    height: 14rem;
    flex-direction: column;
  }

  .list-form-input {
    width: 100%;
    flex-direction: row;
    gap: 4px;
  }

  .list-form-left {
    width: 136px;
  }

  .list-form-right {
    width: calc((100% - 136px));
  }

  .list-form-image {
    width: 136px;
    height: 136px;
  }

  .list-form-item {
    height: 2rem;
    line-height: 2rem;
  }

  .list-form-buttons {
    width: 100%;
    height: 2rem;
    line-height: 2rem;
    flex-direction: row;
    flex-wrap: nowrap;
    gap: 4px;
    margin: 0;
    padding: 0 4px;
  }

}
</style>

<script>
// ------------------------------------------------------------
// 入力項目チェック関数
// ------------------------------------------------------------
const validate = (event, form) => {
  // 数量チェック
  if (!checkOrderQty(form.elements['list-cart-detail-qty'].id)) {
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
  const forms = document.getElementsByClassName('list-form-cart-detail');
  Array.from(forms).forEach((form) => {
    const btns = form.elements['action'];
    btns.forEach((btn) => {
      if (btn.value === 'update') {
        btn.addEventListener('click', (event) => validate(event, form), false);
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
        <h2>ショッピングカート</h2>
      </div>
      <?php include_once '../../include/view/message.php'; ?>
      <div class="container-item">
        <?php if (!isExistCart($db)) : ?>
        <div class="page-navi">
          <a class="page-button" href="./item-list.php">
            <span class="page-button-text">商品一覧</span>
          </a>
        </div>
        <div class="page-message">
          <p class="page-message-text">ショッピングカートは空です。</p>
        </div>
        <div class="page-navi">
          <a class="page-button" href="./item-list.php">
            <span class="page-button-text">商品一覧</span>
          </a>
        </div>
        <?php else : ?>
        <div class="page-navi">
          <div class="page-navi-item">
            <a class="page-button" href="./item-list.php">
              <span class="page-button-text">商品一覧</span>
            </a>
          </div>
          <form class="page-navi-item" id="page-button-top-checkout" action="./complete.php" method="POST">
            <input type="hidden" name="csrf-token" value="<?php echo $token; ?>">
            <input type="hidden" name="list-cart-id" value="<?php echo $_SESSION['cart-id']; ?>">
            <button class="page-button" type="submit" form="page-button-top-checkout" name="action" value="checkout">
              <span class="page-button-text">購入</span>
            </button>
          </form>
          <form class="page-navi-item" id="page-button-top-empty" action="./cart.php" method="POST">
            <input type="hidden" name="csrf-token" value="<?php echo $token; ?>">
            <input type="hidden" name="list-cart-id" value="<?php echo $_SESSION['cart-id']; ?>">
            <button class="page-button" type="submit" form="page-button-top-empty" name="action" value="empty">
              <span class="page-button-text">空にする</span>
            </button>
          </form>
        </div>
        <div class="list-cart">
          <?php $result = getCartDetailRecord($db, $_SESSION['cart-id']); ?>
          <?php foreach ($result->data as $item) : ?>
          <form class="list-form-cart-detail" id="list-form-cart-detail-<?php echo $item['id']; ?>" action="./cart.php"
            method="POST">
            <input type="hidden" name="csrf-token" value="<?php echo $token; ?>">
            <input type="hidden" name="list-cart-detail-id" value="<?php echo $item['id']; ?>">
            <input type="hidden" name="list-cart-detail-product-id" value="<?php echo $item['product_id']; ?>">
            <input type="hidden" name="list-cart-detail-product-name" value="<?php echo $item['product_name']; ?>">
            <input type="hidden" name="list-cart-detail-stock-id" value="<?php echo $item['stock_id']; ?>">
            <div class="list-form-input">
              <div class="list-form-left">
                <div class="list-form-image">
                  <img id="list-form-image-<?php echo $item['id']; ?>"
                    src="./image.php?id=<?php echo $item['product_id']; ?>">
                </div>
              </div>
              <div class="list-form-right">
                <div class="list-form-item">
                  <span class="ec-label-short">名称</span>
                  <span class="ec-input-short" inert>
                    <input class="ec-input-text ec-input-no-border ec-text-right" type="text" readonly
                      value=<?php echo htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8'); ?>>
                  </span>
                </div>
                <div class="list-form-item">
                  <span class="ec-label-short">価格</span>
                  <span class="ec-input-short" inert>
                    <input class="ec-input-number ec-input-no-border" type="number" readonly
                      value=<?php echo htmlspecialchars($item['price'], ENT_QUOTES, 'UTF-8'); ?>>
                    <span class="ec-input-number-unit">円</span>
                  </span>
                </div>
                <div class="list-form-item">
                  <label class="ec-label-short" for="list-cart-detail-qty">数量</label>
                  <span class="ec-input-short">
                    <input class="ec-input-number" type="number" id="list-cart-detail-qty-<?php echo $item['id']; ?>"
                      name="list-cart-detail-qty" value=<?php echo $item['qty']; ?>>
                    <label class="ec-input-number-unit" for="list-cart-detail-qty">個</label>
                  </span>
                </div>
                <div class="list-form-item">
                  <span class="ec-label-short">小計</span>
                  <span class="ec-input-short" inert>
                    <input class="ec-input-number ec-input-no-border" type="number" readonly
                      value=<?php echo htmlspecialchars((int) $item['price'] * (int) $item['qty'], ENT_QUOTES, 'UTF-8'); ?>>
                    <span class="ec-input-number-unit">円</span>
                  </span>
                </div>
              </div>
            </div>
            <div class="list-form-buttons">
              <div class="list-form-button">
                <button class="ec-button" id="list-form-button-delete-<?php echo $item['id']; ?>" type="submit"
                  form="list-form-cart-detail-<?php echo $item['id']; ?>" name="action" value="delete">
                  <span class="ec-button-text-small">削除</span>
                </button>
              </div>
              <div class="list-form-button">
                <button class="ec-button" id="list-form-button-update-<?php echo $item['id']; ?>" type="submit"
                  form="list-form-cart-detail-<?php echo $item['id']; ?>" name="action" value="update">
                  <span class="ec-button-text-small">更新</span>
                </button>
              </div>
            </div>
          </form>
          <?php endforeach ?>
        </div>
        <div class="pagenation">
          <?php cartDetailPagenation($db); ?>
        </div>
        <div class="page-total">
          <?php $result = getCartTotal($db); ?>
          <span class="page-total-label">合計</span>
          <span class="page-total-value"><?php echo (string) number_format($result['qty']); ?>点</span>
          <span class="page-total-value"><?php echo (string) number_format($result['price']); ?>円</span>
        </div>
        <div class="page-navi">
          <div class="page-navi-item">
            <a class="page-button" href="./item-list.php">
              <span class="page-button-text">商品一覧</span>
            </a>
          </div>
          <form class="page-navi-item" id="page-button-bottom-checkout" action="./complete.php" method="POST">
            <input type="hidden" name="csrf-token" value="<?php echo $token; ?>">
            <input type="hidden" name="list-cart-id" value="<?php echo $_SESSION['cart-id']; ?>">
            <button class="page-button" type="submit" form="page-button-bottom-checkout" name="action" value="checkout">
              <span class="page-button-text">購入</span>
            </button>
          </form>
          <form class="page-navi-item" id="page-button-bottom-empty" action="./cart.php" method="POST">
            <input type="hidden" name="csrf-token" value="<?php echo $token; ?>">
            <input type="hidden" name="list-cart-id" value="<?php echo $_SESSION['cart-id']; ?>">
            <button class="page-button" type="submit" form="page-button-bottom-empty" name="action" value="empty">
              <span class="page-button-text">空にする</span>
            </button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php include_once '../../include/view/footer.php'; ?>
</body>

</html>