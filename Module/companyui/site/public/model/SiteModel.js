class SiteModel {

    getProvinces(provinces){
        return $.rest({
            'url': App.url('/rest/dvhc?parentID=0'),
            'method': 'get',
            'data': {}
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

    getDistricts(districts){
        return $.rest({
            'url': App.url('/rest/dvhc?parentID=:provincesID',{'provincesID':districts}),
            'method': 'get',
            'data': districts
        });
    }

    getWards(wards){
        return $.rest({
            'url': App.url('/rest/dvhc?parentID=:wardID',{'wardID':wards}),
            'method': 'get',
            'data': wards
        });
    }

    getAllTags() {
        var url = App.url('/:siteID/master/rest/sites/tags',{siteID: App.siteID});
        return $.rest({
            'url' : url
        });
    }

    // api lấy danh sách site trong quản trị
    getSite(filter) {
        var url = App.url('/:siteID/master/rest/sites',{siteID: App.siteID});
        return $.rest({
            'url': url,
            'data': filter
        });
    }

    // lấy danh sách các site đã được merge
    getUserSites(userID, filter) {
        var url = App.url('/:siteID/rest/users/:userID/sites',{siteID: App.siteID, userID: userID});
        return $.rest({
            'url': url,
            'data': filter
        });
    }

    // cập nhật site
    updateSite(siteID, updateData) {
        var url = App.url('/:siteID/master/rest/sites',{siteID: App.siteID});
        if (siteID) {
            url += '/' + siteID;
        }
        console.log(updateData)
        return $.rest({
            'url': url,
            'method': 'put',
            'data': updateData
        });
    }

    // xóa site
    deleteSite(siteID) {
        var url = App.url('/:siteID/master/rest/sites',{siteID: App.siteID});
        if (siteID) {
            url += '/' + siteID;
        }
        return $.rest({
            'url': url,
            'method': 'delete'
        });
    }

    // cập nhật khi ghép tài khoản
    updateMergeSite(updateData) {
        var url = App.url('/:siteID/rest/user/merge',{siteID: App.siteID});
        return $.rest({
            'url': url,
            'dataType': 'json',
            'method': 'put',
            'data': updateData
        });
    }

}