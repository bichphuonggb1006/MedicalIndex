var navs = App.Component.getEventState('PageNavigator') || [];

let navGroup = {
    'name': 'Teleclinic', 'icon': 'fa fa-hospital-o',
    'navs': []
}
if (App.getUser().hasPrivilege('QuanLyLichTruc') || App.getUser().hasPrivilege('TiepNhanBenhNhan')) {
    navGroup.navs.push({
        'id': 'Vclinic',
        'name': 'Phòng khám ảo',
        'href': App.url('/:siteID/teleclinic/vclinic', {siteID: App.siteID}),
        'icon': 'fa fa-user-md'
    })
}
let scheduledPushed = false
if (App.getUser().hasPrivilege('TiepNhanBenhNhan')) {
    navGroup.navs.push({
        'id': 'Unscheduled',
        'name': 'Chưa xếp lịch',
        'href': App.url('/:siteID/teleclinic/unscheduled', {siteID: App.siteID}),
        'icon': 'fa fa-calendar'
    })
    navGroup.navs.push({
        'id': 'Scheduled',
        'name': 'Đã xếp lịch',
        'href': App.url('/:siteID/teleclinic/scheduled', {siteID: App.siteID}),
        'icon': 'fa fa-calendar-check-o'
    })
    scheduledPushed = true
}
if (App.getUser().hasPrivilege('KhamChoBenhNhan')) {
    if (!scheduledPushed)
        navGroup.navs.push({
            'id': 'Scheduled',
            'name': 'Đã xếp lịch',
            'href': App.url('/:siteID/teleclinic/scheduled', {siteID: App.siteID}),
            'icon': 'fa fa-calendar'
        })
}
if (App.getUser().hasPrivilege('XemBaoCao')) {
    navGroup.navs.push({
        'id': 'Report',
        'name': 'Báo cáo',
        'href': App.url('/:siteID/teleclinic/report', {siteID: App.siteID}),
        'icon': 'fa fa-bookmark-o'
    })

    navGroup.navs.push({
        'id': 'Record',
        'name': 'Hồ sơ sức khỏe',
        'href': App.url('/:siteID/teleclinic/record', {siteID: App.siteID}),
        'icon': 'fa fa-archive'
    })
}
if (App.getUser().hasPrivilege('Todolist')) {
    navGroup.navs.push({
        'id': 'TodoList',
        'name': 'TodoList',
        'href': App.url('/:siteID/teleclinic/todolist', {siteID: App.siteID}),
        'icon': 'fa fa-calendar-check-o'
    })
}
// if (App.getUser().hasPrivilege('QuanLyLichTruc') || App.getUser().hasPrivilege('TiepNhanBenhNhan')) {
//     navGroup.navs.push({
//         'id': 'todolist',
//         'name': 'Danh sách các việc vần làm',
//         'href': App.url('/:siteID/teleclinic/todolist', {siteID: App.siteID}),
//         'icon': 'fa fa-user-md'
//     })
// }

navs.push(navGroup);


App.Component.trigger('PageNavigator', navs);

