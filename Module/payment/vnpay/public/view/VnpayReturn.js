class VnpayReturn extends Component {
    constructor(props) {
        super(props);
        this.state = {
            'vnp_TxnRef': '',
            'vnp_Amount': '',
            'vnp_OrderInfo': '',
            'vnp_ResponseCode': '',
            'vnp_TransactionNo': '',
            'vnp_BankCode': '',
            'vnp_PayDate': '',
            'vnp_Result': '',
            'vnp_PhoneSupport': '',
            'paymentCartUrl': '',
            'userPhone': '',
            'salt': 'sAAOIQkjwwiq__7273720' // Mã bảo mật
        }

        this.vnpModel = new VNPayModel();
    }

    componentDidMount() {
        this.state.vnp_TxnRef = pageData.vnp_TxnRef;
        this.state.vnp_Amount = pageData.vnp_Amount;
        this.state.vnp_OrderInfo = pageData.vnp_OrderInfo;
        this.state.vnp_ResponseCode = pageData.vnp_ResponseCode;
        this.state.vnp_TransactionNo = pageData.vnp_TransactionNo;
        this.state.vnp_BankCode = pageData.vnp_BankCode;
        this.state.vnp_PayDate = pageData.vnp_PayDate;
        this.state.vnp_Result = pageData.vnp_Result;
        this.state.vnp_PhoneSupport = pageData.vnp_PhoneSupport;
        this.state.paymentCartUrl = pageData.paymentCartUrl;
        this.state.userPhone = pageData.userPhone;
        this.setState(this.state, () => {
            console.log("state", this.state);
        });
    }

    http_build_query(formdata, numeric_prefix, arg_separator) {
        var key, use_val, use_key, i = 0, j = 0, tmp_arr = [];

        if (!arg_separator) {
            arg_separator = '&';
        }

        for (key in formdata) {
            use_val = encodeURIComponent(formdata[key].toString());
            use_key = encodeURIComponent(key);

            if (numeric_prefix && !isNaN(key)) {
                use_key = numeric_prefix + j;
                j++;
            }
            tmp_arr[i++] = use_key + '=' + use_val;
        }

        return tmp_arr.join(arg_separator);
    }

    gotoCart() {
        var inputs = {
            'phone': this.state.userPhone,
            'salt': this.state.salt
        }

        var signature = md5(this.http_build_query(inputs));
        delete inputs.salt;

        if (this.state.paymentCartUrl.length)
            window.location.href = this.state.paymentCartUrl + "?" + this.http_build_query(inputs) + "&signature=" + signature;
        // console.log(this.state.vnp_cartUrl + "?" + this.http_build_query(inputs) + "&signature=" + signature);
    }

    render() {
        return (<NoLayout>
            <meta name="viewport" content="width=device-width, initial-scale=1.0"></meta>
            <div className="navbar navbar-expand-md navbar-light sticky-top nav-payment-menu">
                <div className="container-fluid">
                    <a className="navbar-brand" href="#">
                        <img src={App.url('/modules/payment/vnpay/public/images/logo.svg')}
                             height="40"/>
                    </a>
                    <form className="form-inline my-2 my-lg-0">
                        <span className="mr-sm-2" aria-hidden="true"><i className="fa fa-user-circle fa-lg"
                                                                        aria-hidden="true"></i> &nbsp; Tổng đài hỗ trợ</span>
                        <span className="call-support-phone"> <a
                            href={'tel:' + this.state.vnp_PhoneSupport}>{this.state.vnp_PhoneSupport}</a>   </span>
                    </form>

                </div>
            </div>
            <div className="container-fluid payment-home">
                <div className="jumbotron   text-white jumbotron-image shadow jumbotron-banner">
                    <h3 className="">Cổng thanh toán VNPAY</h3>
                </div>
            </div>
            <div className="container payment-container">
                <div className="row">
                    <div className="col-md-12 ">
                        <div className="card">
                            <div className="card-header  text-white">
                                <h4>Thông tin thanh toán </h4>
                            </div>
                            <div className="card-body">
                                <br/>
                                <br/>
                                {this.state.vnp_Result.status && <div className="text-center">
                                    <img
                                        src={App.url('/modules/payment/vnpay/public/images/success_icon.png')}
                                        height="100"/>
                                </div>
                                }

                                {!this.state.vnp_Result.status && <div className="text-center">
                                    <img
                                        src={App.url('/modules/payment/vnpay/public/images/error_icon.png')}
                                        height="100"/>
                                </div>
                                }
                                <h5 className={"text-center " + (this.state.vnp_Result.status ? "result-success" : "result-error")}
                                    style={{
                                        margin: "10px",
                                        fontSize: "25px"
                                    }}>{this.state.vnp_Result.data}</h5>
                                <div className="form-group text-center" style={{margin: 0}}>
                                    <table style={{margin: "0 auto"}}>
                                        <tbody>
                                        <tr>
                                            <td align={"right"}><label className={"lbl-vnp-name"}>Mã hóa đơn:</label>
                                            </td>
                                            <td align={"left"}><label
                                                className={"lbl-vnp-value"}>{this.state.vnp_TxnRef}</label></td>
                                        </tr>
                                        <tr>
                                            <td align={"right"}><label className={"lbl-vnp-name"}>Số tiền:</label></td>
                                            <td align={"left"}><label
                                                className={"lbl-vnp-value"}>{this.state.vnp_Amount}</label></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <br/>
                                <br/>
                                <div className={"text-center payment-footer"}>
                                    <button className={"btn btn-primary"}
                                            onClick={(ev) => {
                                                ev.currentTarget.blur();
                                                this.gotoCart();
                                            }}>Lịch sử thanh toán
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </NoLayout>)
    }
}
