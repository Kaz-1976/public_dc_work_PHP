<!DOCTYPE html>
<html lang="ja">

<head>
  <?php include_once '../../include/view/common.php' ?>
  <title>ユーザー登録：ECサイト</title>
</head>

<style>
.form-users {
  width: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 4px;
}

.form-users-item {
  width: 100%;
  height: 3rem;
  line-height: 3rem;
  display: flex;
  flex-direction: row;
}

.form-area-button {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
}

.form-area-input {
  width: 100%;
  height: 100%;
  display: flex;
  margin: auto;
}

.to-login {
  display: flex;
  width: 100%;
  height: 3rem;
  margin: 8px auto;
}

.to-login p {
  display: flex;
  margin: auto;
  font-size: 1.5rem;
  color: #282d33;
}

.to-login p a {
  font-size: 1.5rem;
  font-weight: bold;
  color: #282d33;
}
</style>

<style>
@media screen and (max-width: 799px) {
  .form-users-item {
    height: 2rem;
    line-height: 2rem;
  }

  .to-login p {
    font-size: 1.25rem;
  }

  .to-login p a {
    font-size: 1.25rem;
  }
}
</style>

<script>
// ------------------------------------------------------------
// 入力項目チェック関数
// ------------------------------------------------------------
const validate = (event) => {
  // フォームを取得
  const form = document.getElementById('form-users');

  // ユーザーIDチェック
  if (!checkUserId('form-users-user-id')) {
    // エラー報告
    form.reportValidity();
    // デフォルトアクションをキャンセル
    event.preventDefault();
    return;
  }

  // パスワードチェック
  if (!checkUserPassword('form-users-user-password')) {
    // エラー報告
    form.reportValidity();
    // デフォルトアクションをキャンセル
    event.preventDefault();
    return;
  }

  // 名前（漢字）チェック
  if (!checkUserName('form-users-user-name')) {
    // エラー報告
    form.reportValidity();
    // デフォルトアクションをキャンセル
    event.preventDefault();
    return;
  }

  // 名前（かな）チェック
  if (!checkUserKana('form-users-user-kana')) {
    // エラー報告
    form.reportValidity();
    // デフォルトアクションをキャンセル
    event.preventDefault();
    return;
  }

  // メールアドレスチェック
  if (!checkUserMailAddress('form-users-user-mail')) {
    // エラー報告
    form.reportValidity();
    // デフォルトアクションをキャンセル
    event.preventDefault();
    return;
  }
}

// ------------------------------------------------------------
// 画面ロード時実行関数
// ------------------------------------------------------------
window.addEventListener('DOMContentLoaded', () => {
  // 登録ボタン取得 
  const btnSubmit = document.getElementById('button-submit');
  // 登録ボタンにチェック関数設定
  btnSubmit.addEventListener('click', (event) => validate(event), false);
}, false);
</script>

<body>
  <?php include_once '../../include/view/header.php'; ?>
  <div class="wrapper">
    <div class="container">
      <div class="page-title">
        <h2>ユーザー登録</h2>
      </div>
      <?php include_once '../../include/view/message.php'; ?>
      <div class="container-item container-item-frame">
        <form class="form-users" id="form-users" action="./registration.php" method="POST">
          <input type="hidden" name="csrf-token" value="<?php echo $token; ?>">
          <div class="form-users-item">
            <label class="ec-label" for="form-users-user-id">I D</label>
            <span class="ec-input">
              <input class="ec-input-text" type="text" id="form-users-user-id" name="form-user-id"
                value="<?php echo $formUser->userId; ?>" placeholder="EcTaro" minlength="5" maxlength="64">
            </span>
          </div>
          <div class="form-users-item">
            <label class="ec-label" for="form-users-user-password">パスワード</label>
            <span class="ec-input">
              <input class="ec-input-text" type="password" id="form-users-user-password" name="form-user-password"
                value="<?php echo $formUser->password; ?>" placeholder="ABCabc0123!@#$%^&*" minlength="8"
                maxlength="64">
            </span>
          </div>
          <div class="form-users-item">
            <label class="ec-label" for="form-users-user-name">氏名（漢字）</label>
            <span class="ec-input">
              <input class="ec-input-text" type="text" id="form-users-user-name" name="form-user-name"
                value="<?php echo $formUser->name; ?>" placeholder="イーシー太郎" maxlength="64">
            </span>
          </div>
          <div class="form-users-item">
            <label class="ec-label" for="form-users-user-kana">氏名（かな）</label>
            <span class="ec-input">
              <input class="ec-input-text" type="text" id="form-users-user-kana" name="form-user-kana"
                value="<?php echo $formUser->kana; ?>" placeholder="いーしーたろう" maxlength="64">
            </span>
          </div>
          <div class="form-users-item">
            <label class="ec-label" for="form-users-user-mail">メールアドレス</label>
            <span class="ec-input">
              <input class="ec-input-text" type="email" id="form-users-user-mail" name="form-user-mail"
                value="<?php echo $formUser->mail; ?>" placeholder="ec-taro@example.local" maxlength="64">
            </span>
          </div>
          <div class="form-users-item">
            <button class="ec-button" id="button-submit" type="submit" form="form-users" name="action" value="create">
              <span class="ec-button-text-normal">登録</span>
            </button>
          </div>
        </form>
      </div>
      <div class="to-login">
        <p>ログインページは <a href="./index.php">こちら</a></p>
      </div>
    </div>
  </div>
  <?php include_once '../../include/view/footer.php'; ?>
</body>

</html>