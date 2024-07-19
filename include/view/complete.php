<!DOCTYPE html>
<html lang="ja">

<head>
  <?php include_once '../../include/view/common.php' ?>
  <title>購入完了：ECサイト</title>
</head>

<style>
.page-navi {
  width: 100%;
  height: 3rem;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  margin: 0;
  padding: 4px 0px 4px 0px;
}

.page-navi-item {
  width: 100%;
  height: 100%;
  display: flex;
}

.page-button {
  width: 100%;
  height: 100%;
  display: flex;
  margin: 0;
  padding: 0px 4px 0px 4px;
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
  border-top: 2px solid #282d33;
  border-bottom: 2px solid #282d33;
}

.page-message-text {
  display: flex;
  margin: auto;
  padding: 32px 0;
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

.list-cart-detail {
  width: 100%;
  height: 16rem;
  display: flex;
  flex-direction: row;
  margin: 0px auto;
  padding: 4px;
  gap: 8px;
  border-top: 2px solid #282d33;
}

.list-left {
  width: 220px;
  height: 100%;
  display: flex;
}

.list-right {
  width: calc(100% - 220px);
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.list-image {
  width: 220px;
  height: 220px;
  display: flex;
  border: 1px solid #282d33;
}

.list-image img {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
  overflow: hidden;
}

.list-item {
  width: 100%;
  height: 3rem;
  line-height: 3rem;
  display: flex;
  flex-direction: row;
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

  .list-cart-detail {
    height: auto;
  }

  .list-left {
    width: 136px;
    margin: auto 0;
  }


  .list-right {
    width: calc(100% - 136px);
  }

  .list-image {
    width: 136px;
    height: 136px;
  }

  .list-item {
    height: 2rem;
    line-height: 2rem;
  }
}
</style>

<body>
  <?php include_once '../../include/view/header.php'; ?>
  <div class="wrapper">
    <div class="container">
      <div class="page-title">
        <h2>購入完了</h2>
      </div>
      <?php include_once '../../include/view/message.php'; ?>
      <div class="container-item">
        <div class="page-navi">
          <div class="page-navi-item">
            <a class="page-button" href="./item-list.php">
              <span class="page-button-text">商品一覧</span>
            </a>
          </div>
        </div>
        <div class="list-cart">
          <?php $result = getCartDetailRecord($db, $cart->id); ?>
          <?php foreach ($result->data as $item) : ?>
          <div class="list-cart-detail">
            <div class="list-left">
              <div class="list-image">
                <img id="list-image-<?php echo $item['id']; ?>" src="./image.php?id=<?php echo $item['product_id']; ?>">
              </div>
            </div>
            <div class="list-right">
              <div class="list-item">
                <span class="ec-label-short">名称</span>
                <span class="ec-input-short" inert>
                  <input class="ec-input-text ec-input-no-border ec-text-right" type="text" readonly
                    value=<?php echo htmlspecialchars($item['product_name']); ?>>
                </span>
              </div>
              <div class="list-item">
                <span class="ec-label-short">価格</span>
                <span class="ec-input-short" inert>
                  <input class="ec-input-number ec-input-no-border" type="number" readonly
                    value=<?php echo htmlspecialchars($item['price']); ?>>
                  <span class="ec-input-number-unit">円</span>
                </span>
              </div>
              <div class="list-item">
                <label class="ec-label-short" for="list-cart-detail-qty">数量</label>
                <span class="ec-input-short" inert>
                  <input class="ec-input-number ec-input-no-border" type="number" readonly
                    value=<?php echo htmlspecialchars($item['qty']); ?>>
                  <label class="ec-input-number-unit" for="list-cart-detail-qty">個</label>
                </span>
              </div>
              <div class="list-item">
                <span class="ec-label-short">小計</span>
                <span class="ec-input-short" inert>
                  <input class="ec-input-number ec-input-no-border" type="number" readonly
                    value=<?php echo htmlspecialchars((int) $item['price'] * (int) $item['qty']); ?>>
                  <span class="ec-input-number-unit">円</span>
                </span>
              </div>
            </div>
          </div>
          <?php endforeach ?>
        </div>
      </div>
      <div class="pagenation">
        <?php cartDetailPagenation($db, $cart->id); ?>
      </div>
      <div class="page-total">
        <?php $result = getCartTotal($db, $cart->id); ?>
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
      </div>
    </div>
  </div>
  <?php include_once '../../include/view/footer.php'; ?>
</body>

</html>