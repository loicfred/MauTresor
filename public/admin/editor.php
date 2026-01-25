<?php
include __DIR__ . '/../../config/auth.php';
checksForAdmin();

$id = $_GET['id'] ?? 0;
$class = $_GET['class'];
$fullClass = "assets\\obj\\$class";

require_once __DIR__ . "/../../config/obj/$class.php";
require_once __DIR__ . "/../../config/obj/DBObject.php";


$edited_object = $id == 0 ? new $fullClass() : $fullClass::getByID($id);
if (!$edited_object && $_SERVER['REQUEST_METHOD'] !== 'POST') header("Location: /admin/editor?class=$class");

?>

<!DOCTYPE html>
<html lang="en">
<head id="master-head">
    <title>Database Editor | MauTresor</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#822BD9">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="icon" href="/assets/img/logo_transparent.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/assets/css/main.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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
require_once __DIR__ . '/../assets/fragments/header.php';
?>

<main>
    <div class="settings-card">

        <?php
        echo '<h2 class="settings-header">' . ucfirst($class) . ' Manager</h2>';

        $reflection = new ReflectionClass($edited_object);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                foreach ($properties as $prop):
                    $type = $prop->getType()?->getName() ?? 'string';
                    if (isset($_FILES[$prop->getName()]) && strlen($_FILES[$prop->getName()]['name']) > 0) {
                        if (!isset($_FILES['Image']) || $_FILES['Image']['error'] !== UPLOAD_ERR_OK) {
                            echo "<div class='alert alert-danger'>Upload failed. File size might be too big.</div>";
                        }
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mime = $finfo->file($_FILES['Image']['tmp_name']);
                        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                        if (!in_array($mime, $allowed, true)) {
                            throw new RuntimeException('Invalid image type');
                        }
                        $edited_object->Image = base64_encode(file_get_contents($_FILES['Image']['tmp_name']));
                        $edited_object->MimeType = $mime;
                    } else {
                        if (!isset($_POST[$prop->getName()]) && $type !== 'bool') continue;
                        $newVal = match($type) {
                            'bool' => (isset($_POST[$prop->getName()]) && $_POST[$prop->getName()] == 'true' ? 1 : 0),
                            'int' => isset($_POST[$prop->getName()]) ? (int) $_POST[$prop->getName()] : null,
                            'string' => trim($_POST[$prop->getName()]),
                            default => $_POST[$prop->getName()]
                        };
                        $prop->setAccessible(true);
                        $prop->setValue($edited_object, $newVal);
                    }
                endforeach;
                $edited_object->Upsert();
                echo "<div class='alert alert-success'>Successfully saved this entry !</div>";
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";

            }
        }
        ?>

        <form action="/admin/editor?class=<?= $class ?><?= (isset($_GET['id']) ? '&id=' . $id : '') ?>" enctype="multipart/form-data" method="POST" class="d-flex flex-column">
            <?php foreach ($properties as $prop):
                if ($prop->getName() === 'ID') continue;
                if ($prop->getName() === 'MimeType') continue;
                $prop->setAccessible(true);
                $name = $prop->getName();
                $value = isset($_GET['id']) ? $prop->getValue($edited_object) : null;
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
                        <textarea class="form-control" id="<?= $name ?>" name="<?= $name ?>" maxlength="512" rows="4"><?= $value ?></textarea>
                    <?php elseif ($name === "Description"): ?>
                        <label class="form-label" for="<?= $name ?>"><?= $name ?></label>
                        <textarea class="form-control" id="<?= $name ?>" name="<?= $name ?>" maxlength="1024" rows="8"><?= $value ?></textarea>
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
                        <input class="form-control" type="file" accept="image/*" id="<?= $name ?>" name="<?= $name ?>">
                        <script>
                            const MAX_SIZE = 2 * 1024 * 1024;
                            document.getElementById("<?= $name ?>").addEventListener('change', function () {
                                const file = this.files[0];
                                if (!file) return;
                                if (file.size > MAX_SIZE) {
                                    alert('File too large (max 2MB)');
                                    this.value = '';
                                }
                            });
                        </script>
                    <?php elseif ($name === "Longitude"): ?>
                    <script>
                        document.getElementById("Longitude").value = <?= $value ?>;
                    </script>
                    <?php elseif ($name === "Latitude"): ?>
                        <div style="border: 1px solid #ced4da; border-radius: 5px; padding: 5px">
                            <div class="d-flex gap-1">
                                <div class="mb-3 w-50">
                                    <label class="form-label" for="Latitude">Latitude</label>
                                    <input class="form-control" type="number" step="any" min="-90" max="90" id="Latitude" value="<?= $value ?>" name="Latitude">
                                </div>
                                <div class="mb-3 w-50">
                                    <label class="form-label" for="Longitude">Longitude</label>
                                    <input class="form-control" type="number" step="any" min="-90" max="90" id="Longitude" name="Longitude">
                                </div>
                            </div>
                            <div id="map" style="height:200px"></div>
                        </div>
                    <?php elseif ($inputType === "number"): ?>
                        <label class="form-label" for="<?= $name ?>"><?= $name ?></label>
                        <input class="form-control" type="number" id="<?= $name ?>" name="<?= $name ?>" value="<?= $value ?>">
                    <?php elseif ($inputType === "checkbox"): ?>
                        <input class="form-check-input" type="checkbox" id="<?= $name ?>" name="<?= $name ?>" value="true"<?= !empty($value) ? 'checked' : '' ?>>
                        <label class="form-label" for="<?= $name ?>"><?= $name ?></label>
                    <?php elseif ($inputType === "text"): ?>
                        <label class="form-label" for="<?= $name ?>"><?= $name ?></label>
                    <input class="form-control" type="text" id="<?= $name ?>" name="<?= $name ?>" maxlength="64" value="<?= $value ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-success">Save Entry</button>
        </form>
        <?php if (isset($_GET['id'])): ?>
            <form action="/admin/editor?class=<?= $class ?>&id=<?= $id ?>&delete" class="d-flex flex-column mt-3" method="post" onsubmit="return confirm('Are you sure you want to delete this entry?');">
                <button type="submit" class="btn btn-danger flex-grow-1">Delete Entry</button>
            </form>
            <div class="d-flex flex-row mt-3 gap-2">
                <?php if ($fullClass::getByID($id-1)): ?>
                    <a href="/admin/editor?class=<?= $class ?>&id=<?= $id-1 ?>" class="btn btn-primary flex-grow-1">Prev. Entry</a>
                <?php endif; ?>
                <?php if ($fullClass::getByID($id+1)): ?>
                    <a href="/admin/editor?class=<?= $class ?>&id=<?= $id+1 ?>" class="btn btn-primary flex-grow-1">Next Entry</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<script src="/assets/js/app.js"></script>

<script>
    const map = L.map('map').setView([0, 0], 2);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    let marker;

    map.on('click', function (e) {
        const { lat, lng } = e.latlng;

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }

        document.getElementById('Latitude').value = lat.toFixed(6);
        document.getElementById('Longitude').value = lng.toFixed(6);
    });
</script>
</body>
</html>
