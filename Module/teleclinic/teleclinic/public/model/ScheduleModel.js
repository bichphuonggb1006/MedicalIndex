class ScheduleModel {
    getSchedule(filter, orderField) {
        if(filter.scheduledDate && filter.scheduledDate.getTime)
            filter.scheduledDate = filter.scheduledDate.getTime()
        if(filter.reqDate && filter.reqDate.getTime)
            filter.reqDate = filter.reqDate.getTime()

        return $.rest({
            url:  App.url('/rest/teleclinic/schedule'),
            method: 'get',
            'dataType': 'json',
            data: Object.assign({siteID: App.siteID}, filter, {order: orderField})
        })
    }

    getScheduleById(id) {
        return $.rest({
            url: App.url('/rest/teleclinic/schedule/:id', {'id': id})
        })
    }

    confirmSchedule(scheduleID, data) {
        return $.rest({
            url:  App.url('/rest/teleclinic/schedule/:id/schedule', {id: scheduleID}),
            method: 'post',
            'dataType': 'json',
            data: data
        })
    }

    updatePaymentStatus(scheduleID, paymentStatus) {
        return $.rest({
            url: App.url('/rest/teleclinic/schedule/:id/paymentStatus', {id: scheduleID}),
            method: 'post',
            data: {
                'paymentStatus': paymentStatus
            }
        })
    }

    diagnosis(scheduleID, data) {
        return $.rest({
            url:  App.url('/rest/teleclinic/schedule/:id/diagnosis', {id: scheduleID}),
            method: 'post',
            'dataType': 'json',
            data: data
        })
    }

    cancelSchedule(scheduleID, data) {
        return $.rest({
            url:  App.url('/rest/teleclinic/schedule/:id', {id: scheduleID}),
            method: 'delete',
            'dataType': 'json',
            data: data
        })
    }

    getClinicScheduleSummaries(clinicID, scheduledDate) {
        return $.rest({
            url: App.url('/rest/teleclinic/schedule/getClinicScheduleSummaries'),
            method: 'get',
            data: {
                'clinicID': clinicID,
                'scheduledDate': scheduledDate
            }
        })
    }

    sendSMS(data) {
        return $.rest({
            url: App.url('/:siteID/rest/teleclinic/schedule/sendNotification', {siteID: App.siteID}),
            method: 'post',
            data: data
        })
    }

    //Lấy danh sách các trường tham số theo id nhóm
    getFieldsFormId(id) {
        var url = App.url('/:siteID/rest/settings/forms/:id/fields', {siteID: App.siteID, id: id});
        return $.rest({
            'url': url,
            'dataType': 'json',
            'method': 'get'
        });
    }
}