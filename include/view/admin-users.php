<!DOCTYPE html>
<html lang="ja">

<head>
  <?php include_once '../../include/view/common.php' ?>
  <title>ユーザー管理：ECサイト</title>
</head>

<style>
.form-area-button {
  width: 30%;
  height: 3rem;
  line-height: 3rem;
  display: flex;
  flex-direction: row;
  margin: 0 auto;
  padding: 0;
}

.form-area-input {
  width: 35%;
  height: 3rem;
  line-height: 3rem;
  display: flex;
  flex-direction: row;
  margin: 0 auto;
  padding: 0;
}

.list-users-disable {
  background-color: #515a66;
}

.list-users-admin {
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
}
</style>

<script>
// ------------------------------------------------------------
// 入力項目チェック関数
// ------------------------------------------------------------
const validate = (event, form, mode) => {
  // フォーム要素ID接頭辞
  const base = mode === 'INSERT' ? 'form-' : 'list-';
  // ユーザーIDチェック
  if (!checkUserId(form.elements[base + 'user-id'].id)) {
    form.reportValidity();
    return;
  };
  // パスワードチェック
  if (mode === 'INSERT') {
    if (!checkUserPassword(form.elements[base + 'user-password'].id)) {
      // エラー報告
      form.reportValidity();
      // デフォルトアクションをキャンセル
      event.preventDefault();
      return;
    };
  };
  // 名前（漢字）チェック
  if (!checkUserName(form.elements[base + 'user-name'].id)) {
    // エラー報告
    form.reportValidity();
    // デフォルトアクションをキャンセル
    event.preventDefault();
    return;
  };
  // 名前（かな）チェック
  if (!checkUserKana(form.elements[base + 'user-kana'].id)) {
    // エラー報告
    form.reportValidity();
    // デフォルトアクションをキャンセル
    event.preventDefault();
    return;
  };
  // メールアドレスチェック
  if (!checkUserMailAddress(form.elements[base + 'user-mail'].id)) {
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
  // 登録ボタンにチェック関数設定
  const formCreate = document.forms['form-users'];
  const btnCreate = formCreate.elements['action'];
  btnCreate.addEventListener('click', (event) => validate(event, formCreate, 'INSERT'), false);

  // 更新ボタンにチェック関数設定
  const formUpdate = document.getElementsByClassName('list-form-users');
  Array.from(formUpdate).forEach((form) => {
    const btns = form.elements['action'];
    btns.forEach(btn => {
      if (btn.value === 'update') {
        btn.addEventListener('click', (event) => validate(event, form, 'UPDATE'), false);
      }
    })
  });
}, false);
</script>

<body>
  <?php include_once '../../include/view/header.php'; ?>
  <div class="wrapper">
    <div class="container">
      <div class="page-title">
        <h2>ユーザー管理</h2>
      </div>
      <?php include_once '../../include/view/message.php'; ?>
      <div class="container-item container-item-frame">
        <form class="form-register" id="form-users" action="./admin-users.php" method="POST">
          <input type="hidden" name="csrf-token" value="<?php echo $token; ?>">
          <div class="form-register-item">
            <label class="ec-label" for="form-users-user-id">I D</label>
            <span class="ec-input">
              <input class="ec-input-text" type="text" id="form-users-user-id" name="form-user-id"
                value="<?php echo $formUser->userId; ?>" placeholder="EcTaro">
            </span>
          </div>
          <div class="form-register-item">
            <label class="ec-label" for="form-users-user-password">パスワード</label>
            <span class="ec-input">
              <input class="ec-input-text" type="password" id="form-users-user-password" name="form-user-password"
                value="<?php echo $formUser->password; ?>" placeholder="ABCabc0123!@#$%^&*">
            </span>
          </div>
          <div class="form-register-item">
            <label class="ec-label" for="form-users-user-name">氏名（漢字）</label>
            <span class="ec-input">
              <input class="ec-input-text" type="text" id="form-users-user-name" name="form-user-name"
                value="<?php echo $formUser->name; ?>" placeholder="イーシー太郎">
            </span>
          </div>
          <div class="form-register-item">
            <label class="ec-label" for="form-users-user-kana">氏名（かな）</label>
            <span class="ec-input">
              <input class="ec-input-text" type="text" id="form-users-user-kana" name="form-user-kana"
                value="<?php echo $formUser->kana; ?>" placeholder="いーしーたろう">
            </span>
          </div>
          <div class="form-register-item">
            <label class="ec-label" for="form-users-user-mail">メールアドレス</label>
            <span class="ec-input">
              <input class="ec-input-text" type="email" id="form-users-user-mail" name="form-user-mail"
                value="<?php echo $formUser->mail; ?>" placeholder="ec-taro@example.local">
            </span>
          </div>
          <div class="form-register-item">
            <div class="form-area-input">
              <input class="ec-input-checkbox" type="checkbox" id="form-users-user-enable" name="form-user-enable"
                <?php echo $formUser->enable === 1 ? 'checked' : ''; ?>>
              <label class="ec-input-checkbox-label" for="form-users-user-enable">有効</label>
            </div>
            <div class="form-area-input">
              <?php if (isSuperUser()) : ?>
              <input class="ec-input-checkbox" type="checkbox" id="form-users-user-admin" name="form-user-admin"
                <?php echo $formUser->admin === 1 ? 'checked' : ''; ?>>
              <label class="ec-input-checkbox-label" for=" form-users-user-admin">管理者</label>
              <?php endif; ?>
            </div>
            <div class="form-area-button">
              <button class="ec-button" type="submit" form="form-users" name="action" value="create">
                <span class="ec-button-text-small">登録</span>
              </button>
            </div>
          </div>
        </form>
      </div>
      <div class="container-item">
        <?php include_once '../../include/view/message.php'; ?>
        <div class="list-update">
          <?php $result = getUserRecord($db); ?>
          <?php foreach ($result->data as $item) : ?>
          <div
            class="list-update-wrapper  <?php echo $item['enable_flg'] === 0 ? 'list-users-disable' : ''; ?> <?php echo $item['admin_flg'] === 1 ? 'list-users-admin' : ''; ?>">
            <form class="list-update-form" id="list-item-users-<?php echo $item['id']; ?>" action="./admin-users.php"
              method="POST">
              <input type="hidden" name="csrf-token" value="<?php echo $token; ?>">
              <input type="hidden" id="list-id-<?php echo $item['id']; ?>" name="list-id"
                value="<?php echo $item['id']; ?>">
              <input type="hidden" id="list-user-password-<?php echo $item['id']; ?>" name="list-user-password"
                value="<?php echo $item['password']; ?>">
              <input type="hidden" id="list-user-enable-<?php echo $item['id']; ?>" name="list-user-enable"
                value="<?php echo $item['enable_flg']; ?>">
              <input type="hidden" id="list-user-admin-<?php echo $item['id']; ?>" name="list-user-admin"
                value="<?php echo $item['admin_flg']; ?>">
              <div class="list-update-form-item">
                <div class="list-update-form-item-input">
                  <label class="ec-label" for="list-user-id-<?php echo $item['id']; ?>">I D</label>
                  <span class="ec-input">
                    <input class="ec-input-text" type="text" id="list-user-id-<?php echo $item['id']; ?>"
                      name="list-user-id" value="<?php echo $item['user_id']; ?>" placeholder="EcTaro"
                      <?php echo $item['user_id'] === $_SESSION['user-data']['user_id'] ? 'readonly' : ''; ?>>
                  </span>
                </div>
                <div class="list-update-form-item-input">
                  <label class="ec-label" for="list-user-name-<?php echo $item['id']; ?>">氏名（漢字）</label>
                  <span class="ec-input">
                    <input class="ec-input-text" type="text" id="list-user-name-<?php echo $item['id']; ?>"
                      name="list-user-name" value="<?php echo $item['user_name']; ?>" placeholder="イーシー太郎">
                  </span>
                </div>
                <div class="list-update-form-item-input">
                  <label class="ec-label" for="list-user-kana-<?php echo $item['id']; ?>">氏名（かな）</label>
                  <span class="ec-input">
                    <input class="ec-input-text" type="text" id="list-user-kana-<?php echo $item['id']; ?>"
                      name="list-user-kana" value="<?php echo $item['user_kana']; ?>" placeholder="いーしーたろう">
                  </span>
                </div>
                <div class="list-update-form-item-input">
                  <label class="ec-label" for="list-user-mail-<?php echo $item['id']; ?>">メールアドレス</label>
                  <span class="ec-input">
                    <input class="ec-input-text" type="email" id="list-user-mail-<?php echo $item['id']; ?>"
                      name="list-user-mail" value="<?php echo $item['email']; ?>" placeholder="ec-taro@example.local">
                  </span>
                </div>
              </div>
              <div class="list-update-form-item-buttons">
                <div class="list-update-form-item-button">
                  <button
                    class="ec-button <?php echo ($item['user_id'] !== $_SESSION['user-data']['user_id'] ? '' : 'ec-hidden'); ?>"
                    type="submit" form="list-item-users-<?php echo $item['id']; ?>" name="action" value="enable">
                    <span class="ec-button-text-small"><?php echo $item['enable_flg'] === 1 ? '無効' : '有効'; ?></span>
                  </button>
                </div>
                <div class="list-update-form-item-button">
                  <button class="ec-button <?php echo (isSuperUser() ? '' : 'ec-hidden') ?>" type="submit"
                    form="list-item-users-<?php echo $item['id']; ?>" name="action" value="admin">
                    <span class="ec-button-text-small"><?php echo $item['admin_flg'] === 1 ? '一般' : '管理者'; ?></span>
                  </button>
                </div>
                <div class="list-update-form-item-button">
                  <button class="ec-button" type="submit" form="list-item-users-<?php echo $item['id']; ?>"
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
          <?php usersPagenation($db); ?>
        </div>
      </div>
    </div>
  </div>
  <?php include_once '../../include/view/footer.php'; ?>
</body>

</html>