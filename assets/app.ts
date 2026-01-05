import './app.css';
import { initDarkMode } from './navbar';
import { initPasswordToggles } from './passwordToggle';

const sidebar = document.getElementById("sidebar");
const sidebarScreenHover = document.getElementById("sidebar-screen-hover");
const userMenuButton = document.getElementById("user-menu-button");
const userMenu = document.getElementById("user-menu");
const deleteWebsiteButton = document.getElementById("delete-website-button");
const deleteWebsiteModal = document.getElementById("delete-website-modal");
const cancelDeleteButton = document.getElementById("cancel-delete-button");
const settingsTabItems = document.querySelectorAll(".settings-tab-item");
const settingsTabPanes = document.querySelectorAll(".settings-tab-pane");

if (sidebar && sidebarScreenHover) {
    (window as any).dashboardSidebarToggle = function() {
        sidebar.classList.toggle("-translate-x-full");
        sidebarScreenHover.classList.toggle("hidden");
    }
}

if (userMenuButton && userMenu) {
    userMenuButton.addEventListener("click", () => {
        userMenu.classList.toggle("hidden");
    });

    document.addEventListener("click", (event) => {
        if (!userMenu.contains(event.target as Node) && !userMenuButton.contains(event.target as Node)) {
            userMenu.classList.add("hidden");
        }
    });
}

if (deleteWebsiteButton && deleteWebsiteModal && cancelDeleteButton) {
    deleteWebsiteButton.addEventListener("click", () => {
        deleteWebsiteModal.classList.remove("hidden");
    });

    cancelDeleteButton.addEventListener("click", () => {
        deleteWebsiteModal.classList.add("hidden");
    });
}

if (settingsTabItems && settingsTabPanes) {
    settingsTabItems.forEach((item) => {
        item.addEventListener("click", (event) => {
            event.preventDefault();

            settingsTabItems.forEach((i) => {
                i.classList.remove("active", "bg-gray-100", "dark:bg-gray-700");
                i.classList.add("text-gray-500", "dark:text-gray-300");
            });

            item.classList.add("active", "bg-gray-100", "dark:bg-gray-700");
            item.classList.remove("text-gray-500", "dark:text-gray-300");


            const tabId = item.getAttribute("href");

            settingsTabPanes.forEach((pane) => {
                if (`#${pane.id}` === tabId) {
                    pane.classList.remove("hidden");
                } else {
                    pane.classList.add("hidden");
                }
            });
        });
    });
}

function initializeApp(): void {
    initDarkMode();
    initPasswordToggles();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    initializeApp();
}
