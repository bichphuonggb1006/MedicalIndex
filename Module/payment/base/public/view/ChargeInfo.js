class ChargeInfo extends Component {
    constructor(props) {
        super(props);
        this.state = {
            'payment': {},
            'siteConf': []
        }

        this.paymentModel = new PaymentModel()
    }

    open(payment) {
        var field = 'SiteConfig';
        this.paymentModel.getFieldsFormId(field, payment.siteID).then((resp) => {
            delete resp["version"];
            if (!resp.length) return;

            this.setState({payment: payment, siteConf: JSON.parse(resp[0].value)}, () => {
                console.log("state", this.state);
                this.modal.showModal();
            });
        });
    }

    formatMoney(money) {
        return new Intl.NumberFormat('vn-VN', {style: 'currency', currency: 'VND'}).format(money);
    }

    render() {
        return (<Modal className="modal-charge-info" ref={(elm) => {
            this.modal = elm
        }}>
            <Modal.Header>Thông tin chuyển khoản ngân hàng</Modal.Header>
            <Modal.Body>
                <div className="container banking">
                    <h5>Số tiền: <b style={{"color": "#FD3259"}}>{this.formatMoney(this.state.payment.amount)}</b>
                    </h5>
                    {Object.keys(this.state.siteConf).length > 0 && this.state.siteConf.charge.length > 1 && this.state.siteConf.charge.map((charge, idx) =>
                        <div key={idx} className={"bank-account"}>
                            <h5><b>Tài khoản {idx + 1}</b>:
                                : <b
                                    style={{"color": "#79BC4B"}}>{charge.stk}</b>
                            </h5>
                            <h5>Chủ tài khoản
                                : <b
                                    style={{"color": "#79BC4B"}}>{charge.owner}</b>
                            </h5>
                            <h5>Ngân hàng
                                : <b
                                    style={{"color": "#79BC4B"}}>{charge.bank}</b>
                            </h5>
                        </div>
                    )}
                    {Object.keys(this.state.siteConf).length > 0 && this.state.siteConf.charge.length == 1 && <div>
                        <h5>Số tài khoản
                            : <b
                                style={{"color": "#79BC4B"}}>{this.state.siteConf.charge[0].stk}</b>
                        </h5>
                        <h5>Chủ tài khoản
                            : <b
                                style={{"color": "#79BC4B"}}>{this.state.siteConf.charge[0].owner}</b>
                        </h5>
                        <h5>Ngân hàng
                            : <b
                                style={{"color": "#79BC4B"}}>{this.state.siteConf.charge[0].bank}</b>
                        </h5>
                    </div>
                    }
                    <h5>Nội dung chuyển
                        khoản: <b>{this.state.payment.paymentContent}</b>
                    </h5>
                </div>
            </Modal.Body>
        </Modal>)
    }
}