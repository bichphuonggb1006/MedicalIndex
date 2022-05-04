class MedicalRecordDetail extends Component {
  constructor(props) {
    super(props);
    // this.clinicModel = new VclinicModel();
    // this.medicalRecordModel = new MedicalRecordModel();
    this.state = {
      // 'modalOptions': this.handleModalOptions(props.modal),
      // 'isModal': (props && 'modal' in props) ? true : false,
      clinics: [],
      medicalRecods : [],
      'patient' : pageData.patient,
      'schedules': pageData.schedules,
      filter: {
        siteID: App.siteID,
        depID: 0,
        dep: {
          'name': ''
        },
        name: '',
        phone :''
      },
      'activeSchedule': pageData.schedules[0] ? pageData.schedules[0] : {} ,
      'showHistory': 0,
      'activeMenuID': 0,
      'errorLog' :''
    }

    console.log('giang123124');
    console.log('patient',this.state.patient);
    console.log('schedules',pageData.schedules);
    console.log('activeSchedule',this.state.activeSchedule);


      // this.setState({'activeSchedule':pageData.schedules[0].diagnosis}, () => {   });
      // this.setState({'activeMenuID': 0}, () => {   });
    // this.bindThis(['deleteClinic']);
    // Lang.load('teleclinic', 'teleclinic');
  }


  formatDate(date){
      if(!date){
          return ;
      }
    let regDate = new Date(date);
    return regDate.getDate() + '-' + (regDate.getMonth()+1)+'-'+ regDate.getFullYear();
  }

  setActiveSchedule(idx, schedule) {
    this.setState({'activeSchedule': schedule}, () => {
      this.setActiveList(idx);
    });
  }

  setActiveList(keyID) {
    this.setState({'activeMenuID': keyID}, () => {
      // console.log('selected');
      // console.log(this.state.activeMenuID);
      // console.log(this.state.activeSchedule);
    });
  }
    pageContent() {

        return (
         <div>
           <ul className="nav nav-tabs">
             <li className="nav-item">
               <a className="nav-link active" data-toggle="tab" href="#infor">Thông tin chung</a>
             </li>
             <li className="nav-item">
               <a className="nav-link" data-toggle="tab" href="#history">Lịch sử khám - Tư vấn</a>
             </li>
           </ul>
              <div className="tab-content">
                 <div id="infor" className="tab-pane active"> <br />
                    <div className="__infor">
                        <form action="#">
                            <h5 style={{ paddingLeft:20 }}>THÔNG TIN CÁ NHÂN</h5>
                            <div className="row">
                            <div className="col-lg-4">
                                <label>Họ tên: </label>
                                <span>&ensp;{this.state.patient.name}</span>
                            </div>
                            <div className="col-lg-4">
                                <label>Giới tính: </label>
                                <span>&ensp;{this.state.patient.sex === 'F' ? 'Nữ' : 'Nam'}</span>
                            </div>
                            <div className="col-lg-4">
                                <label>Doanh nghiệp:</label>
                                <span>&ensp;---</span>
                            </div>
                            </div>
                            <div className="row">
                              <div className="col-lg-4">
                                <label>Ngày sinh:</label>
                                <span>&ensp;{this.state.patient.birthDate}</span>
                              </div>
                              <div className="col-lg-4">
                                <label>Số điện thoại:</label>
                                <span>&ensp;{this.state.patient.phone}</span>
                              </div>
                              <div className="col-lg-4">
                                <label>Mã nhân viên:</label>
                                <span>&ensp;---</span>
                              </div>
                            </div>
                            <div className="row">
                              <div className="col-lg-4">
                                <label>Email:</label>
                                <span>&ensp;---</span>
                              </div>
                              <div className="col-lg-4">
                                <label>Số CCCD:</label>
                                <span>&ensp;---</span>
                              </div>
                              <div className="col-lg-4">
                                <label>Bộ phận sản xuất:</label>
                                <span>&ensp;---</span>
                              </div>
                            </div>
                            <div className="row">
                              <div className="col-lg-4">
                                <label>Quận/Huyện:</label>
                                <span>&ensp;---</span>
                              </div>
                              <div className="col-lg-4">
                                <label>Lao động nặng:</label>
                                <span>&ensp;---</span>
                              </div>
                            </div>
                            <div className="row">
                              <div className="col-lg-4">
                                <label>Tỉnh/thành phố:</label>
                                <span>&ensp;---</span>
                              </div>
                              <div className="col-lg-4">
                                <label>Địa chỉ chi tiết:</label>
                                <span>&ensp;{this.state.patient.addressText}</span>
                              </div>
                            </div>
                            <div className="row">
                              <h5 className="col-lg-4">THÔNG TIN TÀI KHOẢN</h5>
                              <h5 className="col-lg-8">Tài liệu đính kèm</h5>
                            </div>
                            <div className="row">
                              <div className="col-lg-4">
                                <label>Mật khẩu:</label>
                                <span>&ensp;---</span>
                              </div>
                              <div className="col-lg-8">
                                <div>
                                  <label>
                                    <strong>Đính kèm thông tin liên quan</strong>&ensp;
                                    <i className="fas fa-upload"></i>&ensp;(Upload file định dạng .dox, pdf, imgae, dung lượng &#60;=5MB)
                                  </label>
                                  <div className="uploadFile">
                                  <div className="">
                                        {/*<a href="# ">1.Ảnh chụp siêu âm &ensp;</a>*/}
                                        {/*<a href="# ">2.Bệnh án.dox</a>*/}
                                      </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                        </form>
                    </div>
                 </div>
                 <div id="history" className="tab-pane fade"> <br />
                    <div className="contentHis">
                      <div className="row">
                        <div className="col-md-3">
                          <span>Danh sách khám</span>
                        </div>
                        <div className="col-md-9">
                          <div className="row __infor1">
                            <div className="col-lg-2">{this.state.patient.name}</div>
                            <div className="col-lg-2">{this.state.patient.birthDate}</div>
                            <div className="col-lg-8">Đơn vị doanh nghiệp : ---</div>
                          </div>
                          <div className="row __infor2">
                            <div className="col-lg-2">{this.state.patient.sex === 'F' ? 'Nữ' : 'Nam'}</div>
                            <div className="col-lg-2">{this.state.patient.phone}</div>
                            <div className="col-lg-8">Bộ phận: ---</div>
                          </div>
                        </div>
                      </div>
                      <div className="detailPatient">
                        <div className="row">
                          <div className="col-md-3">
                            <div className="listMedical" style={{ minWidth: '100%' }}>
                              <ul className="nav nav-tabs1">
                                {this.state.schedules.map((scheduleMenu, idx) =>
                                <li className="nav-item" key={idx}>
                                  <a key={idx}  data-toggle="tab" href="#tab1"
                                     className={this.state.activeMenuID == idx ? 'nav-link active' : 'nav-link'}
                                     onClick={(ev) => {
                                       this.setActiveSchedule(idx, scheduleMenu);
                                     }}
                                  >
                                      <strong>{scheduleMenu.serviceName ? scheduleMenu.serviceName : ''}</strong>
                                      <br />{this.formatDate(scheduleMenu.scheduledDate ? scheduleMenu.scheduledDate : '')}
                                      <br />Bác sỹ khám: {scheduleMenu.doctorName ? scheduleMenu.doctorName : '' }<br />
                                  </a>
                                </li>
                                )}
{/*                                 <li className="nav-item"> */}
{/*                                   <a className="nav-link" data-toggle="tab" href="#tab1"  style={{"width": "100px"}}> */}
{/*                                   </a> */}
{/*                                 </li> */}
                              </ul>
                            </div>
                          </div>
                          <div className="col-md-9 tab-content">
                            {this.state.activeSchedule.doctorName &&<div>
                              <div id="tab1" className="tab-pane active">
                                <div id="profile" role="tabpanel" className="tab-pane active">
                                  <div className="titleMedical">
                                    Thông tin khám bệnh đính kèm HTT
                                  </div>
                                  <div className="row _titleMedical">
                                    <div className="col-lg-3">Mô tả bệnh</div>
                                    <div className="col-lg-9">Tài liệu đính kèm</div>
                                  </div>
                                  <div className="row _contentMedical">
                                    <div className="col-lg-3">
                                      ---
                                    </div>
                                    <div className="col-lg-9">
                                      <a href="# ">
                                        ---
                                      </a>
                                      <a href="# ">
                                        ---
                                      </a>
                                    </div>
                                  </div>
                                  <div className="titleMedicals">
                                    Thông tin khám
                                  </div>
                                  <div className="row _titleMedical">
                                    <div className="col-lg-3">Chuyên khoa khám</div>
                                    {/*<div className="col-lg-9">Dịch vụ khám</div>*/}
                                  </div>
                                  <div className="row _contentMedical">
                                    <div className="col-lg-3">
                                      {this.state.activeSchedule.serviceName}
                                    </div>
                                    {/*<div className="col-lg-9">*/}
                                    {/*  Dịch vụ thuộc khoa khám bệnh nghề &ensp;*/}
                                    {/*</div>*/}
                                  </div>
                                  <div className="row _titleMedical">
                                    {/*<div className="col-lg-3">Bác sỹ khám</div>*/}
                                    <div className="col-lg-3">Bác sỹ khám</div>
                                    <div className="col-lg-6">Thời gian khám</div>
                                  </div>
                                  <div className="row _contentMedical" style={{paddingBottom:20}}>
                                    {/*<div className="col-lg-3">*/}
                                    {/*  Nguyễn Minh Chiến*/}
                                    {/*</div>*/}
                                    <div className="col-lg-3">
                                      {this.state.activeSchedule.doctorName}
                                    </div>
                                    <div className="col-lg-6">{this.formatDate(this.state.activeSchedule.scheduledDate)}</div>
                                  </div>
                                  <div className="titleMedicals">
                                    Kết quả khám
                                  </div>
                                  <div className="row">
                                    <div className="col-lg-6">
                                      <div className="_titleMedical">Mô tả bệnh</div>
                                      <div className="__contentMedical"  dangerouslySetInnerHTML={{ __html: this.state.activeSchedule.diagnosis.diagDesc}} >

                                      </div>
                                    </div>
                                    <div className="col-lg-6">
                                      <div className="_titleMedical">Phân loại bệnh</div>
                                      <div className="__contentMedical">
                                        ---
                                      </div>
                                      <div className="_titleMedical">Loại sức khỏe</div>
                                      <div className="__contentMedical">
                                        ---
                                      </div>
                                    </div>
                                  </div>
                                  <div className="_titleMedical">
                                    Kết luận
                                  </div>
                                  <div className="__contentMedical">
                                    {this.state.activeSchedule.diagnosis.diagConclusion}
                                  </div>
                                  <div className="_titleMedical">
                                    Khuyến nghị
                                  </div>
                                  <div className="__contentMedical">
                                    {this.state.activeSchedule.diagnosis.diagRecommendation}
                                  </div>
                                </div>
                              </div>
                            </div>}
                            {!this.state.activeSchedule.doctorName &&<div>
                              <div className="row _titleMedical">
                                <div className="col-lg-3">Chưa có thông tin bác sỹ khám chẩn đoán</div>
                              </div>
                            </div>}

                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
{/*                    <div id="images" className=" tab-pane fade"> */}
{/*                      <br /> */}
{/*                      <h3>Images</h3> */}
{/*                      <p> */}
{/*                        Sed ut perspiciatis unde omnis iste natus error sit voluptatem */}
{/*                        accusantium doloremque laudantium, totam rem aperiam. */}
{/*                      </p> */}
{/*                    </div> */}
              </div>
         </div>
        )
    }
    render() {
            return this.pageContent();
    }
}