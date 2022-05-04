class MedicalFlowModel {

    getTreatmentProcess(data) {
            let url = App.url('/rest/teleclinic/schedule/getTreatmentProcess');
            return $.rest({
                'url': url,
                'method': 'POST',
                'data': data
            });
        }

    updateTreatmentProcess(data) {
        let url = App.url('/rest/teleclinic/schedule/updateTreatmentProcess');
        return $.rest({
            'url': url,
            'method': 'POST',
            'data': data
        });
    }

    updateStopProcess(data) {
        let url = App.url('/rest/teleclinic/schedule/updateStopProcess');
        return $.rest({
            'url': url,
            'method': 'POST',
            'data': data
        });
    }
}