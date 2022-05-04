var navs = App.Component.getEventState('PageNavigator') || [];

if(App.getUser().hasPrivilege('accessAdmin')) {
    navs.push({
        'name': 'Cấu hình', 'icon': 'fa fa-cogs',
        'navs': [
            { 'id': 'setting', 'name': 'Cấu hình', 'href': App.url('/:siteID/setting', { siteID: App.siteID }) , 'icon': 'fa fa-cog'},
            { 'id': 'setting-integrate', 'name': 'Tích hợp', 'href': App.url('/:siteID/settingIntegrate', { siteID: App.siteID }),'icon': 'fa fa-cubes' }
        ]
    });

    App.Component.trigger('PageNavigator', navs);
}
var Setting = {};
var SettingIntegrate = {};