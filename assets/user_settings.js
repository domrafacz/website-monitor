var triggerTabList = [].slice.call(document.querySelectorAll('#user-settings-list-tab a'))

triggerTabList.forEach(function (triggerEl) {
    var tabTrigger = new bootstrap.Tab(triggerEl)

    triggerEl.addEventListener('click', function (event) {
        window.location.hash = event.target.hash
        event.preventDefault()
        tabTrigger.show()
    })
})

window.onload = function afterWebPageLoad() {
    if (window.location.hash !== '') {
        var target = window.location.hash.replace('#', 'list-');
        document.getElementById(target).click();
    }
}