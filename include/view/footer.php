<style>
footer {
  position: fixed;
  bottom: 0;
  width: 100%;
  height: 80px;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  background-color: rgba(176, 196, 222, 0.8);
  z-index: 10;
}

.footer-item {
  display: flex;
  vertical-align: middle;
  margin: auto;
}

.footer-margin {
  position: relative;
  bottom: 0;
  display: flex;
  height: 80px;
}

.footer-string {
  margin: auto;
  color: #282d33;
  font-size: 1.5rem;
  font-weight: bold;
}
</style>

<style>
@media screen and (max-width: 799px) {
  footer {
    height: 64px;
  }

  .footer-margin {
    height: 64px;
  }

  .footer-string {
    font-size: 1.25rem;
  }
}
</style>

<div class="footer-margin"></div>
<footer>
  <div class="footer-item">
    <?php if (isLogin()) : ?>
    <p class="footer-string"><?php echo $_SESSION['user-data']['user_name'] . 'さんがログイン中'; ?></p>
    <?php endif; ?>
  </div>
</footer>