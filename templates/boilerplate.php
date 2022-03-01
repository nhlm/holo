<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $this->e($title ?? "Undefined Website") ?></title>

    <?= $this->section('meta') ?>
    
    <link rel="stylesheet" href="/assets/semantic.min.css" />
    <?= $this->section('styles') ?>
</head>
<body>
    <?= $this->section('body') ?>

    <script src="/assets/jquery-3.6.0.min.js"></script>
    <script src="/assets/semantic.min.js"></script>
    <?= $this->section('scripts') ?>
</body>
</html>