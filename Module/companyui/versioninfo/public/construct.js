
var navs = App.Component.getEventState('PageNavigator') || [];

navs.push({
    'name': 'Khác', 'icon': 'fa fa-info-circle',
    'navs': [
        {'id': 'versionInfo', 'name': 'Thông tin phiên bản', 'href': App.url('/:siteID/versionInfo', {siteID: App.siteID})}
    ]
});

App.Component.trigger('PageNavigator', navs);

var VersionInfo = {

};