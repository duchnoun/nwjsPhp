var mainMenu = new nw.Menu({ type: 'menubar' });
var submenu = new nw.Menu();
submenu.append(new nw.MenuItem({ label: 'Quit' , click: function() { nw.App.quit(); } }));

mainMenu.append(new nw.MenuItem({
    label: 'File',
    submenu: submenu
}));

nw.Window.get().menu = mainMenu;
