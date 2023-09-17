<?php echo $this->extend('templates/template_bot') ?>
<?php echo $this->section('content') ?>

<section class="admin-users">
  <div class="container">
    <h2 class="users__info">
      <?= $users_info ?>
    </h2>
    <ul class="users__list">
    <?php foreach ($users_data as $item) :  ?>
      <li class="users_item d-flex flex-column align-items-start mb-4">
        <span><?= "ID: {$item['user_id']}" ?></span>
        <span><?= "Имя: {$item['user_name']}" ?></span>
        <a><?= "Ссылка: {$item['user_link']}" ?></a>
        <span><?= "Регистрация: {$item['created_at']}" ?></span>
        <span><?= "Окончание подписки: {$item['date_pay']}" ?></span>        
        <button name="<?= $item['user_id'] ?>" type="submit">Выбрать</button>
      </li>
    <?php endforeach; ?>
    </ul>   
  </div>
</section>


<?php echo $this->endSection() ?>