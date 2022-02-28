<?php $this->layout('templates::boilerplate'); ?>

<?php $this->start('body'); ?>
<h1>404</h1>
<p>The requested entity "<?= $path ?>" was not found.</p>
<?php $this->stop(); ?>