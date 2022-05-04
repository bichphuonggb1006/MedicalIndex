class PaymentModel {
    //Lấy danh sách các trường tham số theo id nhóm
    getFieldsFormId(id, siteID) {
        var url = App.url('/:siteID/rest/settings/forms/:id/fields', {siteID: siteID, id: id});
        return $.rest({
            'url': url,
            'dataType': 'json',
            'method': 'get'
        });
    }

    getPayments(data) {
        var url = App.url('/rest/payment/payments', {});
        return $.rest({
            'url': url,
            'dataType': 'json',
            'method': 'get',
            'data': data
        });
    }
}