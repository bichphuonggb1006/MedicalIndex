class CancelScheduleModal extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            comment: "",
            scheduleID: ""
        }

        this.model = new ScheduleModel();
    }

    open(scheduleID) {
        this.setState({'scheduleID': scheduleID}, () => {
            this.modal.showModal();
        })
        return new Promise((done) => {
            this.done = done
        })
    }

    handleSubmit(ev) {
        ev.preventDefault();
        var form = $(this.form);
        if (form[0].checkValidity() === false) {
            $(form).addClass('was-validated');
            return;
        }

        if (!this.state.comment)
            $.toast({
                text : "Vui lòng nhập lý do hủy",
                position : 'top-right'
            })

        this.model.cancelSchedule(this.state.scheduleID, {comment: this.state.comment}).then(resp => {
            if (resp.status) {
                this.modal.hideModal();
                if(this.done)
                    this.done()
            }
        }).catch((xhr) => {
            const err = (JSON.parse(xhr.responseText).data);
            if (this.editFail)
                this.editFail(xhr);
        });
    }

    render() {
        return (
            <form onSubmit={(ev) => { this.handleSubmit(ev); }} ref={(elm) => { this.form = elm; }} noValidate>
            <Modal className="mx-auto" ref={(elm) => {this.modal = elm}}>
                <Modal.Header>Hủy yêu cầu</Modal.Header>
                <Modal.Body>
                    <div className="form-group ">
                            <textarea className="form-control" id="txt-login" ref={(elm) => { this.nameSite = elm; }}
                                      rows={3} placeholder="nêu lý do hủy (bắt buộc)"
                                   required='required'
                                   value={this.state.comment}
                                   onChange={(ev) => {this.setPureState({ comment: ev.target.value }); }}
                            />
                            <div className="invalid-tooltip">
                                Vui lòng nhập lý do hủy
                            </div>
                    </div>
                </Modal.Body>
                <Modal.Footer>
                    <button type="submit" className="btn btn-primary">Xác nhận</button>
                    <button type="button" className="btn btn-secondary" data-dismiss="modal">Đóng lại</button>
                </Modal.Footer>
            </Modal>
            </form>
        );
    }
}