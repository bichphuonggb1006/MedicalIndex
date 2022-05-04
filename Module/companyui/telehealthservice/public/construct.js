var navs = App.Component.getEventState('PageNavigator') || [];

if (App.isFullControl) {
    navs.push({
        'name': 'Dịch vụ', 'icon': 'ti ti-support',
        'navs': [
            {
                'id': 'service_dir',
                'name': 'Nhóm dịch vụ',
                'href':  App.url('/:siteID/serviceDir',{siteID: App.siteID}),
                'icon': 'fa fa-folder-open-o'
            }
        ]
    });
    navs.push({
        'name': 'Dịch vụ', 'icon': 'ti ti-support',
        'navs': [
            {
                'id': 'service_list',
                'name': 'Dịch vụ',
                'href': App.url('/:siteID/serviceList',{siteID: App.siteID}),
                'icon': 'fa fa-folder-o'
            }
        ]
    });

}

App.Component.trigger('PageNavigator', navs);