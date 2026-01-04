<?php
include __DIR__ . '/../config/auth.php';
checksForAdmin();

$id = $_GET['id'];
$class = $_GET['class'];
$fullClass = "assets\\obj\\$class";

require_once __DIR__ . "/../assets/obj/$class.php";
require_once __DIR__ . "/../assets/obj/DBObject.php";


$edited_object = $fullClass::getByID($id);

?>

<!DOCTYPE html>
<html lang="en">
<head id="master-head">
    <title>Database Editor | MauDonate</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#822BD9">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="icon" href="/assets/img/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://assets.mautresor.mu/css/main.css">
    <style>
        main {
            padding: 1rem;
        }

        .settings-card {
            max-width: 600px;
            margin: auto;
            padding: 2rem;
            background: var(--primary-color-lighter);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .settings-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

<?php
require_once '../assets.fragments/header.php';
?>

<main>
    <div class="settings-card">

        <?php
        echo '<h2 class="settings-header">' . ucfirst($class) . ' Manager</h2>';

        $reflection = new ReflectionClass($edited_object);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_GET['success'])) echo '<div class="alert alert-success">Successfully updated this entry !</div>';
            if (isset($_GET['error'])) echo '<div class="alert alert-success">An error occurred. Try again alter.</div>';
        }

        ?>

        <form action="editor?item=<?= $class ?>&id=<?= $id ?>" method="post" class="d-flex flex-column">
            <?php foreach ($properties as $prop): ?>

                <?php
                $prop->setAccessible(true);
                $name = $prop->getName();
                $value = $prop->getValue($edited_object);
                $type = $prop->getType()?->getName() ?? 'string';
                $inputType = match($type) {
                    'int', 'float', 'double' => 'number',
                    'bool' => 'checkbox',
                    default => 'text'
                };
                ?>

                <div class="mb-3">
                    <?php if ($name === "Email"): ?>
                        <label class="form-label" for="<?= $name ?>"><?= $name ?></label>
                        <input class="form-control" type="email" id="<?= $name ?>" name="<?= $name ?>" maxlength="64" value="<?= $value ?>">
                    <?php elseif ($name === "Title" || $name === "Address"): ?>
                        <label class="form-label" for="<?= $name ?>"><?= $name ?></label>
                        <input class="form-control" type="text" id="<?= $name ?>" name="<?= $name ?>" maxlength="128" value="<?= $value ?>">
                    <?php elseif ($name === "Message"): ?>
                        <label class="form-label" for="<?= $name ?>"><?= $name ?></label>
                        <textarea class="form-control" id="<?= $name ?>" name="<?= $name ?>" maxlength="512" rows="3"><?= $value ?></textarea>
                    <?php elseif ($name === "Gender"): ?>
                        <label class="form-label" for="<?= $name ?>"><?= $name ?></label>
                        <select class="form-select" id="<?= $name ?>" name="<?= $name ?>">
                            <option value="" disabled selected>Select Gender</option>
                            <option value="Male" <?= $value === "Male" ? "selected" : "" ?>>Male</option>
                            <option value="Female" <?= $value === "Female" ? "selected" : "" ?>>Female</option>
                            <option value="Other" <?= $value === "Other" ? "selected" : "" ?>>Other</option>
                        </select>
                    <?php elseif ($name === "ID"): ?>
                        <label class="form-label" for="<?= $name ?>"><?= $name ?></label>
                        <input class="form-control" type="number" id="<?= $name ?>" name="<?= $name ?>" value="<?= $value ?>" disabled>
                    <?php elseif ($name === "Image"): ?>
                        <label class="form-label" for="<?= $name ?>"><?= $name ?></label>
                        <input class="form-control" type="file" id="<?= $name ?>" name="<?= $name ?>" value="<?= $value ?>">
                    <?php elseif ($inputType === "number"): ?>
                        <label class="form-label" for="<?= $name ?>"><?= $name ?></label>
                        <input class="form-control" type="number" id="<?= $name ?>" name="<?= $name ?>" value="<?= $value ?>">
                    <?php elseif ($inputType === "checkbox"): ?>
                        <input class="form-check-input" type="checkbox" id="<?= $name ?>" name="<?= $name ?>" value="<?= $value ?>">
                        <label class="form-label" for="<?= $name ?>"><?= $name ?></label>
                    <?php elseif ($inputType === "text"): ?>
                        <label class="form-label" for="<?= $name ?>"><?= $name ?></label>
                        <input class="form-control" type="text" id="<?= $name ?>" name="<?= $name ?>" maxlength="64" value="<?= $value ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-success">Save Entry</button>
        </form>
        <form action="/admin/delete/{item}/{id}(item=${item}, id=${id}" class="d-flex flex-column mt-4" method="post" onsubmit="return confirm('Are you sure you want to delete this entry?');">
            <button type="submit" class="btn btn-danger flex-grow-1">Delete Entry</button>
        </form>
    </div>
</main>

<script src="https://assets.mautresor.mu/js/app.js"></script>

</body>
</html>
