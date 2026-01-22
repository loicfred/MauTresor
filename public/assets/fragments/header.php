<?php

require __DIR__ . "/../../../config/obj/Notification.php";

use assets\obj\Notification;

?>
<div class="position-relative">
    <nav id="header" class="top-nav navbar navbar-dark">
        <div class="container d-flex align-items-center gap-2" style="flex-wrap: nowrap;">
            <button class="hamburger-btn" id="hamburgerBtn" aria-label="Open menu">☰</button>
            <a class="navbar-brand me-auto" href="/">
                <img src="/assets/img/logo_transparent.png" draggable="false" height="40" alt="logo.png">
            </a>

            <div class="sidebar d-flex flex-column" id="sidebar">
                <h4 class="mb-2">Menu</h4>
                <button onclick="goTo1()">Home</button>
                <button onclick="goTo2()">Heritage Sites</button>
                <button onclick="goTo3()">Cultures</button>
                <button onclick="goTo4()">Events</button>
                <hr>
                <a href="/map">Map</a>
                <a href="/about">About Us</a>
                <?= !isset($_SESSION['user_id']) ? "<a href='/accounts/login'>Log in</a>" : '' ?>
                <?= isset($_SESSION['user_id']) ? "<a href='/settings'>Settings</a>" : '' ?>
                <?= isset($_SESSION['user_id']) ? "<a href='/accounts/login?logout'>Log out</a>" : '' ?>
                <script>
                    const url = window.location.href.split('?')[0];
                    function goTo1() {
                        if (url.endsWith('mu/')) goToPage(0);
                        else window.location.href = "/?page=0";
                    }
                    function goTo2() {
                        if (url.endsWith('mu/')) goToPage(1);
                        else window.location.href = "/?page=1";
                    }
                    function goTo3() {
                        if (url.endsWith('mu/')) goToPage(2);
                        else window.location.href = "/?page=2";
                    }
                    function goTo4() {
                        if (url.endsWith('mu/')) goToPage(3);
                        else window.location.href = "/?page=3";
                    }
                </script>

                <?= isAdmin() ? "<hr>" : '' ?>
                <?= isAdmin() ? "<button onclick='goToAdmin1()'>Admin - Notifications</button>" : '' ?>
                <?= isAdmin() ? "<button onclick='goToAdmin2()'>Admin - Events</button>" : '' ?>
                <?= isAdmin() ? "<button onclick='goToAdmin3()'>Admin - Database</button>" : '' ?>
                <hr>
                <a href="/api/v1/docs/">API Documentation</a>
                <script>
                    function goToAdmin1() {
                        if (url.endsWith('/admin')) goToPage(0);
                        else window.location.href = "/admin?page=0";
                    }
                    function goToAdmin2() {
                        if (url.endsWith('/admin')) goToPage(1);
                        else window.location.href = "/admin?page=1";
                    }
                    function goToAdmin3() {
                        if (url.endsWith('/admin')) goToPage(2);
                        else window.location.href = "/admin?page=2";
                    }
                </script>
            </div>
            <div class="sidebar-overlay" id="sidebarOverlay"></div>


            <div class="position-relative nav-item search-bar d-none d-flex flex-column" id="searchBar" style="border: 1px solid #ccc; border-radius: 4px; padding: 8px; width: 100%; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <input type="text" placeholder="Search..." id="searchField" style="border: 1px solid #ddd; border-radius: 4px; padding: 6px 10px; width: 100%; outline: none;">
            </div>
            <div class="nav-item">
                <svg viewBox="0 0 24 24" class="nav-icon" id="searchBtn">
                    <path fill="currentColor" d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
            </div>

            <a class="nav-item" href="/map">
                <svg viewBox="0 0 24 24" class="nav-icon">
                    <path fill="currentColor" d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z"/>
                </svg>
            </a>

            <?php if (isLoggedIn()):
                $notifs = Notification::getOfUser($_SESSION['user_id']);?>
                <div class="nav-item notification position-relative">
                    <svg viewBox="0 0 24 24" class="nav-icon" id="notificationBtn" style="position: relative;">
                        <path fill="currentColor" d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/>
                        <?php if (in_array(false, array_column($notifs, 'isRead'), true)): ?>
                            <div id="notification-dot" style="position: absolute; top: -5px; right: -5px; width: 16px; height: 16px; background: red; border-radius: 50%;">
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
                                    <div class="d-block align-items-center">
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
                            fetch("/api/readnotif", {method: "POST"});
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
                            document.location.href = "/accounts/login";
                        });
                    </script>
                </div>
            <?php endif; ?>

        </div>
    </nav>
    <div class="position-absolute d-none flex-column w-100" style="z-index: 10; max-height: 80vh; overflow-y: scroll;" id="resultBox">

    </div>
</div>
<script>
    const searchBtn = document.getElementById("searchBtn");
    const searchBar = document.getElementById("searchBar");
    const searchField = document.getElementById("searchField");
    const resultBox = document.getElementById("resultBox");
    searchBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        if (searchBar.classList.contains("d-none")) searchBar.classList.remove("d-none");
        if (resultBox.classList.contains("d-none")) resultBox.classList.remove("d-none");
        searchBtn.classList.add("d-none")
        searchBar.querySelector("input").focus();
    });
    document.addEventListener("click", (e) => {
        if (!searchBar.contains(e.target)) {
            searchBar.classList.add("d-none");
            resultBox.classList.add("d-none");
            searchBtn.classList.remove("d-none")
        }
    });
    searchField.addEventListener("input", (e) => {
         fetch(`/api/searchbar?s=${searchField.value}`, {method: "GET"}
        ).then(res => res.json()).then(
            datas => {
                resultBox.innerHTML = "";
                datas.forEach(data => {
                    if (data.type === "event") {
                        resultBox.innerHTML += `<div class="d-flex p-2">
                                                              <svg viewBox="0 0 24 24" class="nav-icon">
                                                                  <path fill="white" d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/>
                                                              </svg>
                                                              <a style="text-decoration: none;" href="/event/${data.id}">${data.name}</a>
                                                            </div>`;
                    }
                    else if (data.type === "place") {
                        resultBox.innerHTML += `<div class="d-flex p-2">
                                                              <svg viewBox="0 0 24 24" class="nav-icon">
                                                                  <path fill="white" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                                              </svg>
                                                              <a style="text-decoration: none;" href="/site/${data.id}">${data.name}</a>
                                                            </div>`;
                    }
                });
            }
        )
    });
</script>