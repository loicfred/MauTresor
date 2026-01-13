<?php
include __DIR__ . '/../../config/auth.php';
checksForAdmin();

require_once __DIR__ . "/../../config/obj/User.php";
require_once __DIR__ . "/../../config/obj/DBObject.php";
use assets\obj\DBObject;
?>

<!DOCTYPE html>
<html lang="en">
<head id="master-head">
    <title>Admin | MauTresor</title>

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

    <style>
        main {
            background-color: #00000099;
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

<main class="page-wrap" id="pageWrap">
    <div class="page-carousel" id="pageCarousel">

        <!-- PAGE 1 -->
        <section class="page">

        </section>

        <!-- PAGE 2 -->
        <section class="page">

        </section>

        <!-- PAGE 3 -->
        <section class="page">
            <h2 class="settings-header">Database Accessor</h2>

            <form id="databaseForm">
                <div class="mb-3 d-flex align-items-center">
                    <label for="tableSelect" class="form-label mb-0 w-25 me-1">Table</label>
                    <select id="tableSelect" onchange="addSelect(this)" class="form-select">
                        <option value="" disabled selected>- Select a table -</option>
                        <option value="User">User Accounts</option>
                        <option value="Place">Place</option>
                        <option value="Place_Image">Place Images</option>
                        <option value="Event">Event</option>
                        <option value="Event_Image">Event Images</option>
                        <option value="Event_Participant">Event Participants</option>
                        <option value="Hint">Hint</option>
                        <option value="Hint_Image">Hint Image</option>
                        <option value="" disabled>- Other -</option>
                        <option value="Email_Verification">Email verifications</option>
                        <option value="Notification">Notifications</option>
                    </select>
                </div>

                <div class="mb-3 d-flex align-items-center">
                    <label for="ID" class="form-label w-25 me-1">ID</label>
                    <select id="IDs" class="form-control me-1" style="width: 50%" disabled></select>
                    <input type="text" id="ID" maxlength="16" style="width: 50%" class="form-control" disabled>
                </div>

                <div class="mb-3">
                    <h5 class="form-label">Database information:</h5>
                    <?php
                    $databaseStats =  DBObject::getDatabaseDetails();
                    echo "<p class='mb-0'>Table count: " . $databaseStats['tables'] . "</p>";
                    echo "<p class='mb-0'>Views count: " . $databaseStats['views'] . "</p>";
                    echo "<p class='mb-0'>Total rows: " . $databaseStats['rows'] . "</p>";
                    ?>
                </div>
                <div class="mb-3 d-none" id="TableDetails">
                    <h5 class="form-label">Table information:</h5>
                    <p class="mb-0" id="TableDetailsName"></p>
                    <p class="mb-0" id="TableDetailsRows"></p>
                    <p class="mb-0" id="TableDetailsColumns"></p>
                </div>


                <div class="d-flex mb-3">
                    <button type="submit" id="submitDb" class="btn btn-primary w-50 me-1" disabled>Go to entry</button>
                    <button type="button" id="insertDb" class="btn btn-primary w-50 ms-1" disabled>Add new entry</button>
                </div>

                <script>
                    const submitDb = document.getElementById('submitDb');
                    const insertDb = document.getElementById('insertDb');
                    const tableSelect = document.getElementById('tableSelect');

                    const id = document.getElementById('ID');
                    const ids = document.getElementById('IDs');

                    function addSelect(select) {
                        fetch('/api/v1/admin/table/' + select.value, {
                            credentials: 'include',
                        }).then(res => res.json())
                            .then(data => {
                                insertDb.disabled = false;
                                id.disabled = false;
                                ids.disabled = false;
                                document.getElementById('TableDetailsName').innerText = 'Table name: ' + data[0].table;
                                document.getElementById('TableDetailsRows').innerText = 'Rows count: ' + data[0].rows;
                                document.getElementById('TableDetailsColumns').innerText = 'Columns count: ' + data[0].columns;
                                document.getElementById('TableDetails').classList.remove('d-none');
                                ids.innerHTML = '<option value="" disabled selected>- Select an item -</option>';
                                data[1].forEach(item => {
                                    if (select.value === 'Event' || select.value === 'Hint' || select.value === 'Place') {
                                        ids.innerHTML += `<option value="${item.ID}">${item.ID} - ${item.Name}</option>`;
                                    }
                                    else if (select.value === 'User' || select.value === 'Staff') {
                                        ids.innerHTML += `<option value="${item.ID}">${item.ID} - ${item.FirstName} - ${item.LastName}</option>`;
                                    }
                                    else if (select.value.includes('Image')) {
                                        ids.innerHTML += `<option value="${item.ID}">${item.ID} - ${item.Name}</option>`;
                                    }
                                    else {
                                        ids.innerHTML += `<option value="${item.ID}">${item.ID}</option>`;
                                    }
                                })
                            })
                            .catch(err => console.error(err));
                    }

                    document.getElementById('databaseForm').addEventListener('submit', function (e) {
                        e.preventDefault();
                        window.location.href = '/admin/editor?class=' + tableSelect.value + '&id=' + (id.value ? id.value : ids.value);
                    });
                    insertDb.addEventListener("click", () => {
                        window.location.href = '/admin/editor?class=' + tableSelect.value;
                    })

                    id.addEventListener("input", () => {
                        ids.disabled = !!id.value;
                        submitDb.disabled = !(id.value || ids.value) || tableSelect.value === '';
                    });
                    ids.addEventListener("change", () => {
                        submitDb.disabled = !(id.value || ids.value) || tableSelect.value === '';
                    });
                </script>
            </form>
        </section>
    </div>

    <script>
        const reqTitle = document.getElementById("reqValidationTitle")
        function showRequestValidationPopup(button, accept) {
            const modal = new bootstrap.Modal(document.getElementById("reqValidationModal"));
            if (accept) {
                reqTitle.innerText = "Accept donation request";
                document.getElementById("reqValidationModal").action = "/admin/request/validate/" + button.getAttribute("req-id") + "/accept";
            } else {
                reqTitle.innerText = "Deny donation request";
                document.getElementById("reqValidationModal").action = "/admin/request/validate/" + button.getAttribute("req-id") + "/deny";
            }
            modal.show();
        }
    </script>
</main>

<?php
require_once __DIR__ . '/../assets/fragments/bottom-nav-admin.html';
?>

<!-- Scripts -->
<script src="/assets/js/app.js"></script>
<script src="/assets/js/pagecarousel.js"></script>

</body>
</html>