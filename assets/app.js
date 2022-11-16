/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.scss in this case)
import './styles/global.scss';
import './styles/app.scss';

// start the Stimulus application
import './bootstrap';
window.bootstrap = require('bootstrap/dist/js/bootstrap.bundle.js');
var sidebar = document.getElementById("sidebar");
var sidebarScreenHover = document.getElementById("sidebar-screen-hover");


window.dashboardSidebarToggle = function() {

    if (document.body.clientWidth < 768) {
        sidebar.classList.toggle("show");
        sidebarScreenHover.classList.toggle("show");
    } else {
        sidebar.classList.toggle("hide");
    }
}

window.addEventListener("resize", function(event) {
    if (document.body.clientWidth < 768) {
        sidebar.classList.remove("hide")
    } else {
        sidebar.classList.remove("show");
        sidebarScreenHover.classList.remove("show");
    }
})