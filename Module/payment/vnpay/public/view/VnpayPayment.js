class VnpayPayment extends Component {
    constructor(props) {
        super(props);
        this.vnpModel = new VNPayModel();
    }

    componentWillReceiveProps(nextProps) {
        // console.log("nextProps", nextProps);
    }

    handleClickCheckbox() {
        if (this.props.onClick) {
            this.props.onClick();
        }
    }

    vnpCreate(payment, siteID) {
        this.vnpModel.vnpCreate(payment, siteID).then((resp) => {
            if (resp.status && resp.data.hasOwnProperty('url') && resp.data.url.length) {
                window.location.href = resp.data.url;
            }
        });
        // window.location = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?vnp_Amount=1806000&vnp_Command=pay&vnp_CreateDate=20210801153333&vnp_CurrCode=VND&vnp_IpAddr=127.0.0.1&vnp_Locale=vn&vnp_OrderInfo=Thanh+toan+don+hang+%3A5&vnp_OrderType=other&vnp_ReturnUrl=https%3A%2F%2Fdomainmerchant.vn%2FReturnUrl&vnp_TmnCode=DEMOV210&vnp_TxnRef=5&vnp_Version=2.1.0&vnp_SecureHash=3e0d61a0c0534b2e36680b3f7277743e8784cc4e1d68fa7d276e79c23be7d6318d338b477910a27992f5057bb1582bd44bd82ae8009ffaf6d141219218625c42";
    }

    render() {
        return (<Radio id="rdo-vnpay-payment"
                       onClick={() => {
                           this.handleClickCheckbox();
                       }}
                       checked={'checked' in this.props ? this.props.checked : false}
                       label={"Thanh toán qua VNPAY"}
        />)
    }
}
