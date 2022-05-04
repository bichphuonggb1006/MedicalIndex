class ToDoListModel {
    getClinics(filter) {
        return $.rest({
            url:  App.url('/rest/teleclinic/vclinic'),
            method: 'get',
            'dataType': 'json',
            data: filter
        })
    }

    getMedicalRecord(filter) {
        return $.rest({
            url:  App.url('/rest/teleclinic/medicalRecord/:siteID',{siteID : App.siteID}),
            method: 'get',
            'dataType': 'json',
            data: filter
        })
    }

    getClinic(id) {
        return $.rest({
            url: App.url('/rest/teleclinic/vclinic/:id', {id: id}),
            method: 'get'
        })
    }

    updateSchedule(clinicID, date, schedule) {
        return $.rest({
            url: App.url('/rest/teleclinic/vclinic/:clinicID/schedule', {'clinicID': clinicID} ),
            method: 'post',
            data: {
                'date': date,
                'schedule': schedule
            }
        })
    }

    deleteClinic(id) {
        return $.rest({
            url: App.url('/rest/teleclinic/vclinic/' + id),
            method: 'delete'
        })
    }

    save(clinic) {
        if(clinic.department) {
            clinic.depID = clinic.department.id
            delete clinic.department
        }
        return $.rest({
            url: App.url('/rest/teleclinic/vclinic/' + clinic.id),
            method: 'post',
            data: clinic
        })
    }

    getServiceClinics(serviceID) {
        return $.rest({
            url: App.url('/rest/teleclinic/vclinic/:serviceID/service', {serviceID: serviceID}),
            method: 'get'
        })
    }
}