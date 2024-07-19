<header>
  <style>
  header {
    position: fixed;
    top: 0;
    width: 100%;
    height: 80px;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    background-color: rgba(176, 196, 222, 0.8);
    z-index: 10;
  }

  .header-margin {
    position: relative;
    bottom: 0;
    display: flex;
    height: 80px;
  }

  .header-title-wrapper {
    display: flex;
    vertical-align: middle;
    padding-left: 20px;
  }

  .header-link-wrapper {
    display: flex;
    height: 100%;
    padding-right: 20px;
  }

  .header-link {
    display: flex;
    padding: 0px;
    vertical-align: middle;
    gap: 8px;
    list-style-position: inside;
    list-style-type: none;
  }

  .header-link-item {
    display: inline;
    margin: auto;
    padding: 4px;
    color: #282d33;
    font-size: 2rem;
    font-weight: bold;
  }

  .icon-button {
    width: 2rem;
    height: 2rem;
    display: flex;
    margin: auto;
    background-color: rgba(176, 196, 222, 0);
    border: 0;
    border-radius: 4px;
  }

  .icon-button i {
    display: flex;
    margin: auto;
    font-size: 2rem;
    font-weight: bold;
    color: #282d33;
  }
  </style>

  <style>
  @media screen and (max-width: 799px) {
    header {
      height: 64px;
    }

    .header-margin {
      height: 64px;
    }

    .icon-button {
      width: 1.5rem;
      height: 1.5rem;
    }

    .icon-button i {
      font-size: 1.5rem;
    }

  }
  </style>

  <div class="header-title-wrapper">
    <h1>
      <a href="<?php echo LINK_TOP_PAGE; ?>">ECサイト</a>
    </h1>
  </div>
  <div class="header-link-wrapper">
    <ul class="header-link">
      <?php if (isLogin() && !isAdmin()) : ?>
      <li class="header-link-item">
        <a class="icon-button" href="./cart.php">
          <i class="fa-solid fa-cart-shopping" alt="ショッピングカート"></i>
        </a>
      </li>
      <?php endif; ?>
      <?php if (isLogin()) : ?>
      <li class="header-link-item">
        <form id="form-logout" action="./index.php" method="POST">
          <button class="icon-button" type="submit" form="form-logout" name="action" value="logout">
            <i class="fa-solid fa-right-from-bracket fa-2xl" alt="ログアウト"></i>
          </button>
        </form>
      </li>
      <?php endif; ?>
    </ul>
  </div>
</header>
<div class="header-margin"></div>