var navs = App.Component.getEventState('PageNavigator') || [];

if (App.isFullControl)
    navs.push({
        'name': 'Cấu hình hệ thống', 'icon': 'ti ti-settings',
        'navs': [
        { 'id': 'license', 'name': 'License', 'href': App.url('/:siteID/license', {siteID: App.siteID}) }
        ]
    });

App.Component.trigger('PageNavigator', navs);

var License = {};

setTimeout(() => {
    var licenseModel = new License.Model;
    licenseModel.autoCheckLicense();
}, 1500);