<?php
include __DIR__ . '/../../config/auth.php';
include __DIR__ . '/../../config/mailer.php';
checksForAdmin();

require_once __DIR__ . "/../../config/obj/User.php";
require_once __DIR__ . "/../../config/obj/Notification.php";
require_once __DIR__ . "/../../config/obj/DBObject.php";
use assets\obj\User;
use assets\obj\Notification;
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


        #bottomTable {
            padding: 0 50px;
        }
        #entriesTable th, #entriesTable td {
            font-size: 12px;
            max-width: 80px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #databaseForm {
            padding: 10px 300px;
        }

        @media (max-width: 900px) {
            #bottomTable {
                padding: 0;
            }
            #entriesTable th, #entriesTable td {
                font-size: 10px;
                max-width: 60px;
            }
            .disableOnPhone {
                display: none;
            }
            #databaseForm {
                padding: 10px 10px;
            }

            #emailForm {
                padding: 10px 10px !important;
            }
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
            <h2 class="settings-header">Send Customized Notification</h2>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sendEmail'])) {
                $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
                $title = $_POST['subject'] ?? '';
                $content = $_POST['content'] ?? '';
                if ($email && !empty($title) && !empty($content)) {
                    $user = User::getByEmail($email);
                    if ($user) {
                        sendEmail($email, $title, $content);
                        $notification = new Notification();
                        $notification->UserID = $user->ID;
                        $notification->Title = $title;
                        $notification->Message = $content;
                        $notification->CreatedAt = date('Y-m-d H:i:s');
                        $notification->isRead = false;
                        $notification->Write();
                        echo "<div class='alert alert-success'>Email sent successfully.</div>";
                    } else {
                        echo "<div class='alert alert-danger'>User not found.</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>Please fill in all fields.</div>";
                }
            }
            ?>
            <form id="emailForm" action="/?page=0&sendmail" method="post" style="padding: 10px 300px;">
                <div class="mb-3 d-flex align-items-center">
                    <label for="email" class="form-label mb-0 w-25 me-1">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter email address..." required>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label for="subject" class="form-label mb-0 w-25 me-1">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-control" placeholder="Enter subject..." required>
                </div>
                <div class="mb-3 d-flex align-items-start">
                    <label for="content" class="form-label mb-0 w-25 me-1">Content</label>
                    <textarea id="content" name="content" class="form-control" rows="8" placeholder="Enter content..." required></textarea>
                </div>
                <div class="d-flex mb-3">
                    <button type="submit" class="btn btn-primary w-100">Send Email</button>
                </div>
            </form>
        </section>

        <!-- PAGE 2 -->
        <section class="page">

        </section>

        <!-- PAGE 3 -->
        <section class="page" style="padding: 5px 5px;">
            <h2 class="settings-header">Database Accessor</h2>

            <form id="databaseForm">
                <div class="mb-3 d-flex align-items-center">
                    <label for="tableSelect" class="form-label mb-0 w-25 me-1">Table</label>
                    <select id="tableSelect" onchange="addSelect(this)" class="form-select">
                        <option value="" disabled selected>- Select a table -</option>
                        <option value="User">User Accounts</option>
                        <option value="Place">Place</option>
                        <option value="Place_Image">Place Images</option>
                        <option value="Culture">Culture</option>
                        <option value="Culture_Image">Culture Images</option>
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
                    <input type="text" id="ID" maxlength="16" style="width: 50%" class="form-control" placeholder="Enter ID manually..." disabled>
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

                                const tableHead = document.getElementById('entriesTableHead');
                                const tableBody = document.getElementById('entriesTableBody');
                                tableHead.innerHTML = '';
                                tableBody.innerHTML = '';

                                if (data[1].length > 0) {
                                    const blackList = ["Image", "MimeType", "Password"];
                                    const disableOnPhone = ["Verified", "Enabled", "Gender", "DateOfBirth", "CreatedAt", "QRCode", "Category", "ThumbnailID", "UpdatedAt", "AccountProvider"];
                                    const headerRow = document.createElement('tr');
                                    Object.keys(data[1][0]).forEach(key => {
                                        if (blackList.includes(key)) return;
                                        const th = document.createElement('th');
                                        if (disableOnPhone.includes(key)) th.classList.add('disableOnPhone');
                                        th.innerText = key;
                                        if (key === "ID") headerRow.prepend(th);
                                        else headerRow.appendChild(th);
                                    });
                                    tableHead.appendChild(headerRow);
                                    headerRow.insertAdjacentHTML('beforeend', "<th></th>")

                                    data[1].forEach(item => {
                                        const row = document.createElement('tr');
                                        Object.entries(item).forEach(([key, value]) => {
                                            if (blackList.includes(key)) return;
                                            const td = document.createElement('td');
                                            if (disableOnPhone.includes(key)) td.classList.add('disableOnPhone');
                                            td.innerHTML = value !== null ? (value + '').replaceAll('<br>', '') : '-';
                                            if (key === "ID") row.prepend(td);
                                            else row.appendChild(td);
                                        });
                                        tableBody.appendChild(row);
                                        row.insertAdjacentHTML('beforeend', `<td class="p-0" style="width: 80px;"><a class="btn btn-secondary h-100 w-100" style="font-size: 12px" href='/admin/editor?class=${tableSelect.value}&id=${item.ID}'>View</a></td>`)

                                        if (select.value === 'Event' || select.value === 'Hint' || select.value === 'Place') {
                                            ids.innerHTML += `<option value="${item.ID}">${item.ID} - ${item.Name}</option>`;
                                        } else if (select.value === 'User' || select.value === 'Staff') {
                                            ids.innerHTML += `<option value="${item.ID}">${item.ID} - ${item.FirstName} - ${item.LastName}</option>`;
                                        } else if (select.value.includes('Image')) {
                                            ids.innerHTML += `<option value="${item.ID}">${item.ID} - ${item.Name}</option>`;
                                        } else {
                                            ids.innerHTML += `<option value="${item.ID}">${item.ID}</option>`;
                                        }
                                    });
                                }
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

            <div id="bottomTable">
                <table id="entriesTable" class="table table-striped table-bordered">
                    <thead id="entriesTableHead"></thead>
                    <tbody id="entriesTableBody"></tbody>
                </table>
            </div>
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