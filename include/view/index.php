<!DOCTYPE html>
<html lang="ja">

<head>
  <?php include_once '../../include/view/common.php' ?>
  <title>ログイン：ECサイト</title>
</head>


<style>
.form-login {
  width: 100%;
  display: flex;
  flex-direction: column;
  margin: auto;
  gap: 16px;
}

.form-login-item {
  width: 100%;
  height: 3rem;
  line-height: 3rem;
  display: flex;
  vertical-align: middle;
}

.no-account {
  display: flex;
  width: 100%;
  height: 3rem;
  margin: 8px auto;
}

.no-account p {
  display: flex;
  margin: auto;
  font-size: 1.5rem;
  color: #282d33;
}

.no-account p a {
  font-size: 1.5rem;
  font-weight: bold;
  color: #282d33;
}
</style>

<style>
@media screen and (max-width: 799px) {
  .form-login {
    width: 100%;
    margin: 0;
  }

  .form-login-item {
    height: 2rem;
    line-height: 2rem;
  }

  .no-account p {
    font-size: 1.25rem;
  }

  .no-account p a {
    font-size: 1.25rem;
  }
}
</style>

<body>
  <?php include_once '../../include/view/header.php'; ?>
  <div class="wrapper">
    <div class="container">
      <div class="container-item">
        <div class="page-title">
          <h2>ログイン</h2>
        </div>
      </div>
      <div class="container-item">
        <?php include_once '../../include/view/message.php'; ?>
      </div>
      <div class="container-item container-item-frame">
        <form class="form-login" id="form-login" action='./index.php' method="POST">
          <input type="hidden" name="csrf-token" value="<?php echo $token; ?>">
          <div class="form-login-item">
            <label class="ec-label" for="login-id">I D</label>
            <span class="ec-input">
              <input class="ec-input-text" type="text" name="login-id" id="login-id" value="<?php echo $login_id; ?>">
            </span>
          </div>
          <div class="form-login-item">
            <label class="ec-label" for="login-pw">パスワード</label>
            <span class="ec-input">
              <input class="ec-input-text" type="password" name="login-pw" id="login-pw"
                value="<?php echo $login_pw; ?>">
            </span>
          </div>
          <div class="form-login-item">
            <input class="ec-input-checkbox" type="checkbox" name="cookie-confirmation" id="cookie-confirmation"
              value="checked" <?php echo $cookie_confirmation; ?>>
            <label class="ec-input-checkbox-label" for="cookie-confirmation">次回ログインIDの入力を省略する</label>
          </div>
          <div class="form-login-item">
            <button class="ec-button" type="submit" form="form-login" name='action' value='login'>
              <span class="ec-button-text-normal">ログイン</span>
            </button>
          </div>
        </form>
      </div>
      <div class="container-item">
        <div class="no-account">
          <p>アカウントをお持ちでない方は<a href="./registration.php">こちら</a></p>
        </div>
      </div>
    </div>
  </div>
  <?php include_once '../../include/view/footer.php'; ?>
</body>

</html>