
var navs = App.Component.getEventState('PageNavigator') || [];

if (App.siteID == 'master') {
    navs.push({
        'name': 'Danh mục động', 'icon': 'ti ti-list',
        'navs': [
            { 'id': 'dict.collection', 'name': 'Danh mục', 'href': App.url('/:siteID/dict/collection', { siteID: App.siteID }) }
        ]
    });
    navs.push({
        'name': 'Danh mục động', 'icon': 'ti ti-list',
        'navs': [
            { 'id': 'dict.item', 'name': 'Đối tượng danh mục', 'href': App.url('/:siteID/dict/item', { siteID: App.siteID }) }
        ]
    });
}

App.Component.trigger('PageNavigator', navs);

var Dict = {
    'Collection': {},
    'Item': {}
};