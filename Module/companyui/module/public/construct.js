if (App.siteID == 'master' && App.getUser().hasPrivilege('fullcontrol')) {
    var navs = App.Component.getEventState('PageNavigator') || [];
    navs.push({
        'name': 'Cấu hình hệ thống', 'icon': 'ti ti-settings',
        'navs': [
            { 'id': 'modules', 'name': 'Quản lý module', 'href':App.url('/:siteID/modules',{siteID: App.siteID})}
        ]
    });

    App.Component.trigger('PageNavigator', navs);
}
