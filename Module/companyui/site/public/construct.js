// chỉ hiện quản lý site khi là quản trị hệ thống
if (App.siteID == 'master' && App.getUser().hasPrivilege('fullcontrol')) {
    var navs = App.Component.getEventState('PageNavigator') || [];
    navs.push({
        'name': 'Cấu hình hệ thống', 'icon': 'ti ti-settings',
        'navs': [
            {'id': 'sites', 'name': 'Quản lý cơ sở y tế', 'href':App.url('/:siteID/sites',{siteID: App.siteID})}
        ]
    });
    App.Component.trigger('PageNavigator', navs);
}

