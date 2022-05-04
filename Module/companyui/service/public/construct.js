var navs = App.Component.getEventState('PageNavigator') || [];

if (App.siteID === 'master') {
    navs.push({
        'name': 'Service', 'icon': 'ti ti-list',
        'navs': [
            {'id': 'ServiceList', 'name': 'ServiceList', 'href': App.url('/:siteID/services', {siteID: App.siteID})}
        ]
    });
}
App.Component.trigger('PageNavigator', navs);

