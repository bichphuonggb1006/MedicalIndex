class TeleclinicServiceModel {
    getServicesDir(filter = null) {
        let url = App.url('/:siteID/rest/serviceDir', {siteID: App.siteID});
        return $.rest({
            'url': url,
            'data': filter
        });
    }

    getSite(filter) {
        var url = App.url('/:siteID/rest/sites', {siteID: App.siteID});
        return $.rest({
            'url': url,
            'data': filter
        });
    }

    deleteServiceDir(id) {
        let url = App.url('/rest/serviceDir/:id', {id: id});

        return $.rest({
            'url': url,
            'method': 'delete'
        });
    }

    uploadBanner(fileUpload,type) {
        let uri = App.url('/rest/upload');
        return $.restUpload({
            url: uri,
            file: fileUpload,
            type: type
        });
    }

    updateServiceDir(id, data) {
        let url = App.url('/rest/serviceDir');

        if (id)
            url += "/" + id;

        return $.rest({
            'url': url,
            'method': 'post',
            'data': $.extend(data, {siteID: App.siteID})
        });
    }

    getServicesList(filter = null) {
        let url = App.url('/:siteID/rest/serviceList', {siteID: App.siteID});
        return $.rest({
            'url': url,
            'data': filter
        });
    }

    // xóa MyAe
    deleteServiceList(id) {
        let url = App.url('/rest/serviceList/:id', {id: id});

        return $.rest({
            'url': url,
            'method': 'delete'
        });
    }

    updateServiceList(id, data) {
        let url = App.url('/rest/serviceList');

        if (id)
            url += "/" + id;

        return $.rest({
            'url': url,
            'method': 'post',
            'data': data
        });
    }

    getVclinicSchedule(serviceID, filter) {
        let url = App.url('/rest/serviceList/:serviceID/checkTime', {serviceID: serviceID});
        return $.rest({
            'url': url,
            'method': 'get',
            'data': filter
        });
    }
}