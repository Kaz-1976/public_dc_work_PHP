<!DOCTYPE html>
<html lang="ja">

<head>
  <?php include_once '../../include/view/common.php' ?>
  <title>管理メニュー：ECサイト</title>
</head>

<style>

</style>

<body>
  <?php include_once '../../include/view/header.php'; ?>
  <div class="wrapper">
    <div class="container">
      <div class="page-title">
        <h2>管理メニュー</h2>
      </div>
      <?php include_once '../../include/view/message.php'; ?>
      <div class="container-item container-item-frame">
        <form class="ec-menu-button" id="form-link-admin-users" action="./admin-users.php" method="POST">
          <input type="hidden" name="csrf-token" value="<?php echo $token; ?>">
          <button class="ec-button" type="submit" form="form-link-admin-users" name="action" value="admin-users">
            <span class="ec-menu-button-text">ユーザー管理</span>
          </button>
        </form>
        <?php if (!isSuperUser()) : ?>
          <form class="ec-menu-button" id="form-link-admin-products" action="./admin-products.php" method="POST">
            <input type="hidden" name="csrf-token" value="<?php echo $token; ?>">
            <button class="ec-button" type="submit" form="form-link-admin-products" name="action" value="admin-products">
              <span class="ec-menu-button-text">商品管理</span>
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php include_once '../../include/view/footer.php'; ?>
</body>

</html>