<style>
.message-box {
  width: 100%;
  display: flex;
  justify-content: center;
  padding: 8px;
}

.message {
  width: 100%;
  display: block;
  text-align: center;
  font-size: 1.5rem;
  font-weight: bold;
  color: red;
}
</style>

<style>
@media screen and (max-width: 799px) {
  .message {
    font-size: 1rem;
  }
}
</style>

<?php if (isset($_SESSION['message'])) : ?>
<div class="message-box">
  <span class="message"><?php echo $_SESSION['message']; ?></span>
</div>
<?php $_SESSION['message'] = ''; ?>
<?php endif ?>