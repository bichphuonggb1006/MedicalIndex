class Cart extends Component {

    constructor(props) {
        super(props);
        this.state = {
            'payments': [],
            'filter': {
                'phone': '',
                'signature': ''
            }
        }

        this.paymentModel = new PaymentModel();
        this.vnpModel = new VNPayModel();
    }

    componentDidMount() {
        this.state.filter.phone = pageData.phone;
        this.state.filter.signature = pageData.signature;
        this.setState({filter: this.state.filter}, () => {
            this.getPayments();
        })
    }

    getPayments() {
        this.paymentModel.getPayments(this.state.filter).then((resp) => {
            if (resp && resp.status) {
                this.state.payments = resp.data.result || [];
                this.setState({payments: this.state.payments});
            }
        });
    }

    getPaymentConfig(configs, provider, name) {
        if (!configs || !configs.length)
            return "";

        var ret = "";
        for (var i in configs) {
            var conf = configs[i];
            if (!conf || !conf.hasOwnProperty('provider') || conf.provider != provider)
                continue;

            var config = !conf.hasOwnProperty('config') ? [] : conf.config;
            ret = !config.hasOwnProperty(name) ? "" : config[name];
            break;
        }

        return ret;
    }

    renderPaymentStatus(stt) {
        var titles = {
            'processing': 'Đang thực hiện',
            'unpaid': 'Chưa thanh toán',
            'paid': 'Đã thanh toán',
            'refund': 'Đã hoàn trả',
            'fail': 'Lỗi',
        }

        return !titles.hasOwnProperty(stt) ? "Không xác định" : titles[stt];
    }

    chargeInfo(payment) {
        this.eleChargeInfo.open(payment);
    }

    vnpCreate(payment) {
        this.vnpModel.vnpCreate(payment, payment.siteID).then((resp) => {
            if (resp.status && resp.data.hasOwnProperty('url') && resp.data.url.length) {
                window.location.href = resp.data.url;
            }
        });
    }

    formatDateDmYHi(date) {
        let regDate = new Date(date);
        return regDate.getDate() + '-' + (regDate.getMonth() + 1) + '-' + regDate.getFullYear() + ' ' + regDate.getHours() + ":" + regDate.getMinutes();
    }

    renderPaymentName(name){
        var titles = {
            'BANK_TRANSFER': 'Chuyển khoản ngân hàng',
            'VNPAY': 'Cổng thanh toán VNPAY',
        }

        return !titles.hasOwnProperty(name) ? "Không xác định" : titles[name];
    }

    render() {
        return (<NoLayout>
            <meta name="viewport" content="width=device-width, initial-scale=1.0"></meta>
            <div className="navbar navbar-expand-md navbar-light sticky-top nav-payment-menu" style={{height: '66px'}}>
                <div className="container-fluid">
                    <a className="navbar-brand" href="#">
                        <img src={App.url('/modules/payment/vnpay/public/images/logo.svg')} height="40"/>
                    </a>
                    <form className="form-inline my-2 my-lg-0">
                        <span className="mr-sm-2" aria-hidden="true"><i className="fa fa-user-circle fa-lg"
                                                                        aria-hidden="true"></i> &nbsp; Tổng đài hỗ trợ</span>
                        <span className="call-support-phone"> <a
                            href={'tel:'}></a>   </span>
                    </form>
                </div>
            </div>
            <div className="container-fluid payment-home">
                <div className="jumbotron   text-white jumbotron-image shadow jumbotron-banner">
                    <h3 className="">Lịch sử thanh toán</h3>
                </div>
            </div>
            <div className="container payment-container">
                <div className="row">
                    <div className="col-md-12 ">
                        <div className="card">
                            <div className="card-body">
                                <div className={"table-scrollable"}>
                                    <table className="table table-striped table-hover table-bordered margin-none">
                                        <thead>
                                        <tr>
                                            <th style={{'minWidth': '40px'}}>STT</th>
                                            <th style={{'minWidth': '130px'}}>Số hóa đơn</th>
                                            <th style={{'minWidth': '110px'}}>Số tiền</th>
                                            <th style={{'minWidth': '130px'}}>Cổng thanh toán</th>
                                            <th style={{'width': '100%'}}>Nội dung thanh toán</th>
                                            <th style={{'minWidth': '140px', 'textAlign': 'left'}}>Trạng thái</th>
                                            <th style={{'minWidth': '110px', 'textAlign': 'center'}}>Thời gian</th>
                                            <th style={{
                                                'minWidth': '140px',
                                                'textAlign': 'center'
                                            }}>Thao tác
                                            </th>
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div className={"table-fixed-body"}>
                                    <table className="table table-bordered">
                                        <tbody>
                                        {this.state.payments.length > 0 && this.state.payments.map((p, idx) =>
                                            <tr key={idx}>
                                                <td style={{minWidth: '40px', verticalAlign: "top"}}>{idx + 1}</td>
                                                <td style={{minWidth: '130px', verticalAlign: "top"}}>{p.orderID}</td>
                                                <td style={{minWidth: '110px', verticalAlign: "top"}}>{p.amount}</td>
                                                <td style={{minWidth: '130px', verticalAlign: "top"}}>{this.renderPaymentName(p.payment)}</td>
                                                <td style={{
                                                    width: '100%',
                                                    verticalAlign: "top"
                                                }}>{p.paymentContent}</td>
                                                <td style={{
                                                    minWidth: '140px',
                                                    textAlign: 'left',
                                                    verticalAlign: "top"
                                                }}>{this.renderPaymentStatus(p.status)}</td>
                                                <td style={{
                                                    minWidth: '110px',
                                                    textAlign: 'center',
                                                    verticalAlign: "top"
                                                }}>{this.formatDateDmYHi(p.createdTime)}
                                                </td>
                                                <td style={{
                                                    minWidth: '140px',
                                                    textAlign: 'center',
                                                    verticalAlign: "top"
                                                }} className={"actions"}>
                                                    <a href="javascript:;" title={"Thông tin chuyển khoản"}
                                                       onClick={() => {
                                                           this.chargeInfo(p)
                                                       }} className={"btn btn-default btn-xs"} style={{borderColor: "#e9eaec"}}><img
                                                        src={App.url('/modules/payment/base/public/images/print.png')}
                                                        height={"30px"}/></a>
                                                    {(p.payment == "VNPAY" || p.payment == "BANK_TRANSFER") &&
                                                    <a href="javascript:;" title={"Thanh toán VNPAY"} onClick={() => {
                                                        this.vnpCreate(p)
                                                    }} className={"btn btn-default btn-xs"} style={{borderColor: "#e9eaec"}}><img
                                                        src={App.url('/modules/payment/vnpay/public/images/vnpay.png')}
                                                        height={"30px"}/></a>
                                                    }
                                                </td>
                                            </tr>
                                        )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <ChargeInfo ref={(elm) => {
                this.eleChargeInfo = elm;
            }}/>
        </NoLayout>)
    }
}
