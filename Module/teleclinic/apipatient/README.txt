Cấu hình đơn vị trong site

{
  "charge": [
    {
      "stk": "Số tài khoản",
      "owner": "Chủ tài khoản",
      "bank": "Tên ngân hàng",
      "sms": {
        "unit": "Tên viết tắt đơn vị",
        "owner": "tên viết tắt chủ tài khoản",
        "bank": "Tên viết tắt ngân hàng"
      }
    }
  ],
  "supportPhone": "Số ĐT hỗ trợ",
  "chargeTimeout": {
    "enable": "Bật/tắt(1 | 0) đếm ngược",
    "bankingSecTimeleftConf": "Số giây đếm ngược(vd: 900)",
    "timeoutDefaultServiceIdConf": "id dịch vụ mặc định chuyển sau khi quá hạn thanh toán (vd: 83)"
  },
  "healthInsuranceDirIdConf": "ID của Loại dịch vụ dùng làm BHYT(vd: 8)",
  "confirmDirIDFee": 8,
  "confirmDirIDFree": 3,
  "descriptions": {
    "ID thư mục DV" : "<html mô tả>",
    "0": "Điều kiện khám online, khách hàng đã khám tại bệnh viện Hoàn Mỹ Sài Gòn có toa thuốc kết quả cận lâm sàng của chuyên khoa (kết quả XN, CĐHA, ảnh...) gần nhất không quá <span style='color:red;'>6 tháng</span>.",
    "3": "Bệnh viện sẽ tư vấn tổng quát chế độ sinh hoạt, triệu chứng bệnh lý thông thường, hỗ trợ Khách hàng quyết định khám chuyên khoa.",
    "8": "<ul style='padding-left:10px'><li>Điều kiện khám online: Khách hàng đã khám tại bệnh viện Hoàn Mỹ Sài Gòn có toa thuốc kết quả cận lâm sàng của chuyên khoa (kết quả XN, CĐHA...) gần nhất không quá <span style='color:red;'>6 tháng</span>.</li><li> Khách hàng không có hoặc không sử dùng BHYT.</li></ul>"
  },
  "smsPayment": "Bật/tắt(1 | 0) nút gửi SMS phí khám",
  "payments": [
      {
        "provider": "VNPAY",
        "config": {
          "vnp_TmnCode": "XKIKOHAR",
          "vnp_HashSecret": "XJDPKLDQDIOANVJIOTEMFKBTESQHYJSC",
          "vnp_Url": "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html",
          "vnp_returnUrl": "http://172.16.10.192:580/clinic/vnpay/return",
          "vnp_apiUrl": "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html",
          "vnp_phoneSuport": "0123456789"
        }
      }
    ],
  "paymentCartUrl": "http://172.16.10.192:580/clinic/payment/cart"
}

Trong đó:
charge[i].sms : Cấu hình thông tin tài khoản (viết tắt giảm dung lượng tin nhắn) khi gửi SMS.
charge[i].sms.smsPayment: 1: Có hiện nút sms phí khám | 0 : Ko hiện nút sms phí khám
chargeTimeout : Cấu hình đếm ngược thông báo gửi thanh toán phí khám
healthInsuranceDirIdConf: Cấu hình id của Loại dịch vụ dùng làm dịch vụ BHYT (BHYT ko tính tiền), Ko set thì k có chọn BHYT
descriptions : Hiện mô tả theo id của Loại dịch vụ cấu hình. Dạng id dịch vụ => HTML mô tả
payments : Danh sách cấu hình đơn vị tích hợp thanh toán (vd: VNPAY, ..), ko cấu hình thì chỉ có hình thức chuyển khoản ngân hàng
paymentCartUrl: Link lịch sử thanh toán

Chú ý:
Do mỗi site sẽ có 1 tên miền khác nhau nên cấu hình thánh toán (payment) phải setting cứng theo tên miền