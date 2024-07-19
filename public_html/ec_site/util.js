// ------------------------------------------------------------
// 画像表示関数
// ------------------------------------------------------------
const loadImage = (idFile, idImage) => {
  const fileImage = document.getElementById(idFile);
  fileImage.addEventListener('change', (e) => {
    const file = e.target.files[0];
    const reader = new FileReader();
    const image = document.getElementById(idImage);
    reader.addEventListener('load', () => { image.src = reader.result; }, false);
    // ファイルが存在するなら読み込む
    if (file) {
      reader.readAsDataURL(file);
    }
  });
};
// ------------------------------------------------------------
// ユーザー：ユーザーIDチェック関数
// ------------------------------------------------------------
const checkUserId = (elm) => {
  // 要素を取得
  const item = document.getElementById(elm);
  // カスタムメッセージを初期化
  item.setCustomValidity('');
  // 検証：未入力チェック
  if (item.value === '') {
    item.setCustomValidity('ユーザーIDが入力されていません。');
    return false;
  }
  // 検証：文字数チェック
  if (item.value.length < 5 || item.value.length > 64) {
    item.setCustomValidity('ユーザーIDの文字数は5～64文字です。');
    return false;
  }
  // 検証：正規表現チェック
  const strRegex = '^[a-zA-Z0-9]+$'; // ユーザーIDチェックの正規表現
  const regex = new RegExp(strRegex);
  if (!regex.test(item.value)) {
    item.setCustomValidity('ユーザーIDは半角英数字のみ入力できます。');
    return false;
  }
  // チェック通過
  return true;
};
// ------------------------------------------------------------
// ユーザー：パスワードチェック関数
// ------------------------------------------------------------
const checkUserPassword = (elm) => {
  // 要素を取得
  const item = document.getElementById(elm);
  // カスタムメッセージを初期化
  item.setCustomValidity('');
  // 検証：未入力チェック
  if (item.value === '') {
    item.setCustomValidity('パスワードが入力されていません。');
    return false;
  }
  // 検証：文字数チェック
  if (item.value.length < 8 || item.value.length > 64) {
    item.setCustomValidity('パスワードの文字数は8～64文字です。');
    return false;
  }
  // 検証：正規表現チェック
  const strRegex = '^[a-zA-Z0-9!@#$%^&*]+$'; // パスワードチェックの正規表現
  const regex = new RegExp(strRegex);
  if (!regex.test(item.value)) {
    item.setCustomValidity(
      'パスワードは半角英数字および半角記号（! @ # $ % ^ & *）のみ入力できます。'
    );
    return false;
  }
  // チェック通過
  return true;
};
// ------------------------------------------------------------
// ユーザー：メールアドレスチェック関数
// ------------------------------------------------------------
const checkUserMailAddress = (elm) => {
  // 要素を取得
  const item = document.getElementById(elm);
  // カスタムメッセージを初期化
  item.setCustomValidity('');
  // 検証：未入力チェック
  if (item.value === '') {
    item.setCustomValidity('メールアドレスが入力されていません。');

    return false;
  }
  // 検証：文字数チェック
  if (item.value.length > 64) {
    item.setCustomValidity('メールアドレスの文字数は64文字までです。');
    return false;
  }
  // 検証：正規表現チェック
  const strRegex =
    '^[a-zA-Z0-9_+-]+(.[a-zA-Z0-9_+-]+)*@([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]*.)+[a-zA-Z]{2,}$'; // メールアドレスチェックの正規表現
  const regex = new RegExp(strRegex);
  if (!regex.test(item.value)) {
    item.setCustomValidity('メールアドレスの形式が誤っています。');
    return false;
  }
  // チェック通過
  return true;
};
// ------------------------------------------------------------
// ユーザー：名前（漢字）チェック関数
// ------------------------------------------------------------
const checkUserName = (elm) => {
  // 要素を取得
  const item = document.getElementById(elm);
  // カスタムメッセージを初期化
  item.setCustomValidity('');
  // 検証：未入力チェック
  if (item.value === '') {
    item.setCustomValidity('名前（漢字）が入力されていません。');
    return false;
  }
  // 検証：文字数チェック
  if (item.value.length > 64) {
    item.setCustomValidity('名前（漢字）は64文字までです。');
    return false;
  }
  // チェック通過
  return true;
};
// ------------------------------------------------------------
// ユーザー：名前（かな）チェック関数
// ------------------------------------------------------------
const checkUserKana = (elm) => {
  // 要素を取得
  const item = document.getElementById(elm);
  // カスタムメッセージを初期化
  item.setCustomValidity('');
  // 検証：未入力チェック
  if (item.value === '') {
    item.setCustomValidity('名前（かな）が入力されていません。');
    return false;
  }
  // 検証：文字数チェック
  if (item.value.length < 5 || item.value.length > 64) {
    item.setCustomValidity('名前（かな）は64文字までです。');
    return false;
  }
  // チェック通過
  return true;
};
// ------------------------------------------------------------
// 商品管理：名前チェック関数
// ------------------------------------------------------------
const checkProductName = (elm) => {
  // 要素を取得
  const item = document.getElementById(elm);
  // カスタムメッセージを初期化
  item.setCustomValidity('');
  // 検証：未入力チェック
  if (item.value === '') {
    item.setCustomValidity('名称が入力されていません。');
    return false;
  }
  // 検証：文字数チェック
  if (item.value.length > 64) {
    item.setCustomValidity('名称は64文字までです。');
    return false;
  }
  // チェック通過
  return true;
};
// ------------------------------------------------------------
// 商品管理：数量チェック関数
// ------------------------------------------------------------
const checkProductQty = (elm) => {
  // 要素を取得
  const item = document.getElementById(elm);
  // カスタムメッセージを初期化
  item.setCustomValidity('');
  // 検証：未入力チェック
  if (item.value === '') {
    item.setCustomValidity('数量が入力されていません。');
    return false;
  }
  // 検証：数値チェック
  if (item.value < 0) {
    item.setCustomValidity('数量は0以上の整数を入力してください。');
    return false;
  }
  // チェック通過
  return true;
};
// ------------------------------------------------------------
// 商品管理：価格チェック関数
// ------------------------------------------------------------
const checkProductPrice = (elm) => {
  // 要素を取得
  const item = document.getElementById(elm);
  // カスタムメッセージを初期化
  item.setCustomValidity('');
  // 検証：未入力チェック
  if (item.value === '') {
    item.setCustomValidity('価格が入力されていません。');
    return false;
  }
  // 検証：数値チェック
  if (item.value < 1) {
    item.setCustomValidity('価格は1以上の整数を入力してください。');
    return false;
  }
  // チェック通過
  return true;
};
// ------------------------------------------------------------
// 商品管理：画像チェック関数
// ------------------------------------------------------------
const checkProductImage = (elm) => {
  // 要素を取得
  const item = document.getElementById(elm);
  // カスタムメッセージを初期化
  item.setCustomValidity('');
  // チェック通過
  return true;
};
// ------------------------------------------------------------
// 商品一覧・カート：数量チェック関数
// ------------------------------------------------------------
const checkOrderQty = (elm) => {
  // 要素を取得
  const item = document.getElementById(elm);
  // カスタムメッセージを初期化
  item.setCustomValidity('');
  // 検証：未入力チェック
  if (item.value === '') {
    item.setCustomValidity('数量が入力されていません。');
    return false;
  }
  // 検証：数値チェック
  if (item.value < 1) {
    item.setCustomValidity('数量は1以上の整数を入力してください。');
    return false;
  }
  // チェック通過
  return true;
};
