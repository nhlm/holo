<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $this->e($title ?? "Undefined Website") ?></title>

    <?= $this->section('meta') ?>
    
    <?= $this->section('styles') ?>
</head>
<body>
    <?= $this->section('body') ?>

    <?= $this->section('scripts') ?>
</body>
</html>