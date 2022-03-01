<?php $this->layout('templates::boilerplate'); ?>

<?php $this->start('body'); ?>
<h1>Oops!</h1>
<p><?= $this->e($message) ?></p>
<small>Interface: <?= $this->e($interface) ?> (<?= $this->e($code) ?>) - Path: <?= $this->e($path) ?>
<?php $this->stop(); ?>