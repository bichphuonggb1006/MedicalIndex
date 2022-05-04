var navs = App.Component.getEventState('PageNavigator') || [];

if (App.isFullControl) {
    navs.push({
        'name': 'Tài khoản', 'icon': 'ti ti-user',
        'navs': [
            {'id': 'users', 'name': 'Tài khoản & Đơn vị', 'href':  App.url('/:siteID/users',{siteID: App.siteID})}
        ]
    });
    navs.push({
        'name': 'Tài khoản', 'icon': 'ti ti-user',
        'navs': [
            {'id': 'roles', 'name': 'Vai trò', 'href': App.url('/:siteID/roles',{siteID: App.siteID})}
        ]
    });

}

App.Component.trigger('PageNavigator', navs);