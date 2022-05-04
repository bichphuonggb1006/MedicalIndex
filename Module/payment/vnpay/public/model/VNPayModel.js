class VNPayModel {
//Lấy danh sách các trường tham số theo id nhóm
    getFieldsFormId(id, siteID) {
        var url = App.url('/:siteID/rest/settings/forms/:id/fields', {siteID: siteID, id: id});
        return $.rest({
            'url': url,
            'dataType': 'json',
            'method': 'get'
        });
    }

    vnpCreate(data, siteID) {
        var url = App.url('/:siteID/rest/vnpay/create', {siteID: siteID});
        return $.rest({
            'url': url,
            'dataType': 'json',
            'method': 'post',
            'data': data
        });
    }
}