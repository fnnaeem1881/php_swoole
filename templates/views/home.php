<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= \App\Core\Helpers::asset('css/style.css') ?>">
</head>
<body>
    <h1><?= $title ?></h1>
    <form method="post" enctype="multipart/form-data" action="/upload">
        <input type="file" name="file">
        <button type="submit">Upload</button>
    </form>
</body>
</html>
