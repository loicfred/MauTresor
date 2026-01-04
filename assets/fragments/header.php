<?php

require_once __DIR__ . "/../../assets/obj/Notification.php";

use assets\obj\Notification;

?>
<nav class="navbar navbar-dark" style="padding: 3px">
    <div class="container d-flex align-items-center" style="justify-content: space-between;">
        <button class="hamburger-btn me-3" id="hamburgerBtn" aria-label="Open menu">☰</button>
        <a class="navbar-brand me-auto" href="/">
            <img style="border-radius: 50%" src="https://assets.mautresor.mu/img/logo.png" draggable="false" height="40" alt="BeatCam">
        </a>

        <div class="sidebar" id="sidebar">
            <h5 class="mb-3">Menu</h5>
            <button onclick="goToHome()">Home</button>
            <button onclick="goToDonate()">Donate</button>
            <button onclick="goToCampaign()">Campaigns</button>
            <button onclick="goToAbout()">About</button>
            <hr>
            <?= !isset($_SESSION['user_id']) ? "<a href='https://accounts.mautresor.mu/login'>Log in</a>" : '' ?>
            <?= isset($_SESSION['user_id']) ? "<a href='/fundraise'>Fundraise</a>" : '' ?>
            <?= isset($_SESSION['user_id']) ? "<a href='/settings'>Settings</a>" : '' ?>
            <?= isset($_SESSION['user_id']) ? "<a href='/accounts/login?logout'>Log out</a>" : '' ?>
            <script>
                const url = window.location.href;
                function goToHome() {
                    if (url.includes("index")) goToPage(0);
                    else window.location.href = "index?page=0";
                }
                function goToDonate() {
                    if (url.includes("home")) goToPage(1);
                    else window.location.href = "index?page=1";
                }
                function goToCampaign() {
                    if (url.includes("home")) goToPage(2);
                    else window.location.href = "index?page=2";
                }
                function goToAbout() {
                    if (url.includes("home")) goToPage(3);
                    else window.location.href = "index?page=3";
                }
            </script>
            <hr>
            <?= isAdmin() ? "<button class='mt-auto' onclick='goToAdminDonation()'>Admin - Review Donations</button>" : '' ?>
            <?= isAdmin() ? "<button class='mt-auto' onclick='goToAdminRequest()'>Admin - Review Requests</button>" : '' ?>
            <?= isAdmin() ? "<button class='mt-auto' onclick='goToAdminWarehouse()'>Admin - Warehouse</button>" : '' ?>
            <?= isAdmin() ? "<button class='mt-auto' onclick='goToAdminDatabase()'>Admin - Database</button>" : '' ?>

            <script>
                function goToAdminDonation() {
                    if (url.includes("/admin?")) goToPage(0);
                    else window.location.href = "admin?page=0";
                }
                function goToAdminRequest() {
                    if (url.includes("/admin?")) goToPage(1);
                    else window.location.href = "admin?page=1";
                }
                function goToAdminWarehouse() {
                    if (url.includes("/admin?")) goToPage(2);
                    else window.location.href = "admin?page=2";
                }
                function goToAdminDatabase() {
                    if (url.includes("/admin?")) goToPage(3);
                    else window.location.href = "admin?page=3";
                }
            </script>
        </div>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <?php if (isLoggedIn()):
            $notifs = Notification::getOfUser($_SESSION['user_id']);?>
            <div class="nav-item notification">
                <svg viewBox="0 0 24 24" class="nav-icon" id="notificationBtn" style="position: relative;">
                    <path fill="currentColor" d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/>
                    <?php if (in_array(false, array_column($notifs, 'isRead'), true)): ?>
                        <div id="notification-dot" style="position: absolute; top: 10px; right: 10px; width: 16px; height: 16px; background: red; border-radius: 50%;">
                            <div style="position: absolute; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: bold;">
                                !
                            </div>
                        </div>
                    <?php else: ?>
                        <div id="notification-dot" class="d-none" style="position: absolute; top: 10px; right: 10px; width: 16px; height: 16px; background: red; border-radius: 50%;">
                            <div style="position: absolute; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: bold;">
                                !
                            </div>
                        </div>
                    <?php endif; ?>
                </svg>

                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">Notifications</div>

                    <div id="notification-list">
                        <?php
                        if (count($notifs) == 0) echo "<div style='justify-content: center; align-items: center; text-align: center; padding: 6px;'>No notifications yet.</div>";
                        foreach ($notifs as $notif): ?>
                            <div class="notification-item">
                                <h1 class="m-0">🔔</h1>
                                <div class="d-flex align-items-center" style="flex-direction: column;">
                                    <h6 class="mb-0 me-auto"><?= strlen($notif->Title) > 40 ? substr($notif->Title, 0, 40) : $notif->Title ?></h6>
                                    <p class="mb-0"><?= strlen($notif->Message) > 80 ? substr($notif->Message, 0, 80) : $notif->Message ?></p>
                                    <small class="ms-auto"><?= $notif->CreatedAt?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (count($notifs) >= 10): ?>
                        <div class="notification-footer">
                            <a href="/profile">View all</a>
                        </div>
                    <?php endif; ?>
                </div>
                <script>
                    const notificationBtn = document.getElementById("notificationBtn");
                    const notificationDot = document.getElementById("notification-dot");
                    const notificationDropdown = document.getElementById("notificationDropdown");
                    notificationBtn.addEventListener("click", (e) => {
                        e.stopPropagation();
                        notificationDropdown.classList.toggle("active");
                        notificationDot.classList.add("d-none");
                    });
                    document.addEventListener("click", () => {
                        notificationDropdown.classList.remove("active");
                    });
                </script>
            </div>
        <?php endif; ?>

        <?php if (!isLoggedIn()): ?>
            <div class="nav-item">
                <svg viewBox="0 0 24 24" class="nav-icon" id="loginBtn">
                    <path fill="currentColor" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                <script>
                    const loginBtn = document.getElementById("loginBtn");
                    loginBtn.addEventListener("click", (e) => {
                        e.stopPropagation();
                        document.location.href = "https://accounts.mautresor.mu/login";
                    });
                </script>
            </div>
        <?php endif; ?>
    </div>
</nav>
