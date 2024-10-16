var mainMenu = new nw.Menu({ type: 'menubar' });
var submenu = new nw.Menu();
submenu.append(new nw.MenuItem({ label: 'Quit' , click: function() { nw.App.quit(); } }));

mainMenu.append(new nw.MenuItem({
    label: 'File',
    submenu: submenu
}));
// nw.Window.get().menu = mainMenu;
nw.Window.getAll(function(windows)
{
    for (var i = 0; i < windows.length; i++)
    {
        windows[i].menu = mainMenu;
    }
})

var win = nw.Window.get();

win.on('new-win-policy', function(frame, url, policy) {
    // Bloquer l'ouverture de nouvelles fenêtres
    policy.ignore();
});





// // Bloquer la navigation arrière ou avant
// window.onpopstate = function(event) {
//     // Empêcher toute action de retour en arrière
//     history.pushState(null, null, window.location.href);
// };
//
// // Ajouter un nouvel état à l'historique pour empêcher la navigation
// history.pushState(null, null, window.location.href);
//
//
//
// window.addEventListener("keydown", function(e) {
//     if ((e.altKey && (e.key === "ArrowLeft" || e.key === "ArrowRight")) ||
//         (e.key === "Backspace")) {
//         e.preventDefault(); // Bloquer les raccourcis clavier
//     }
// });
//
// window.addEventListener("mousedown", function(e) {
//     // e.button 3 et 4 correspondent souvent à Précédent/Suivant sur les souris avec boutons latéraux
//     if (e.button === 3 || e.button === 4) {
//         e.preventDefault(); // Empêche les boutons Précédent/Suivant de la souris
//     }
// });
