class MedicalFlow extends Component {
    constructor(props) {
        super(props);
        this.state = this.getInitialState()
        this.handleDisabled = this.handleDisabled.bind(this);
        this.handleUndisabled = this.handleUndisabled.bind(this);
        this.handleDisabledText = this.handleDisabledText.bind(this);
        this.medicalFlowModel = new MedicalFlowModel();
    }
    handleDisabled() {
        this.setState(prevState => ({
            isDisabled: true
        }));
    }
    handleUndisabled() {
        this.setState(prevState => ({
            isDisabled: false
        }));
    }
    handleDisabledText() {
        this.setState(prevState => ({
            isDisabledText: !prevState.isDisabledText
        }));
    }
    handleSubmit(ev) {
        console.log(this.state.processForm);
    }
    getInitialState = () => ({
        'isDisabledText': false,
        'processForm': {
            'patient': {
                'id': '',
                'name': ''
            },
            'process' : {
                'temperature' : '',
                'datetime' : '',
                'sign' : [],
                'disription':''
            }
        },
        'stopProcessForm': {
            'scheduleID' : '',
            'patient': {
                'id': '',
                'name': ''
            },
            //LÝ DO
            'reason' : {
                'value' : '',
                'label': '',
                'discripsion': ''
            },
            //THÔNG TIN SAU ĐIỀU TRỊ
            'afterCure':{
                //bệnh nhân có máy thở hay không
                'breathingMachine':{
                    'value':'',
                    'label':'',
                    'startDate':'',
                    'endDate':'',
                    'descripsion':''
                },
                //bệnh nhân có dùng thuốc kháng virus
                'antiviralDrugs':{
                    'value':'',
                    'label':'',
                    'startDate':'',
                    'endDate':'',
                    'descripsion':''
                },
                //các biến trứng trong quá trình điều trị
                'symptoms':{
                    'value':'',
                    'label':'',
                    'startDate':'',
                    'endDate':'',
                    'descripsion':''
                }
            },
            //ĐÁNH GIÁ TÌNH TRẠNG SỨC KHỎE HIỆN TẠI
            'evaluate':{
                //kết quả xét nghiệm Covid tại thời điểm hiện tại
                'testResults':{
                    'value':'',
                    'label':''
                },
                //Thể trạng hiện tại
                'condition':{
                    'value':'',
                    'label':''
                },
                //Triệu chứng
                'symptom':{
                    'value':'',
                    'label':'',
                    'discripsion':'',
                }
            }
        },
        'form': {
            'phone': '',
            'password': ''
        },
        'patient': {},
        'loadLang': false,
        'currentStep': 1,
        'validateError': '',
        'servicesDir': [],
        'servicesList': [],
        'showHistory': 0,
        'activeMenuID': 0,
        'schedules': [],
        'activeSchedule': {},
        'errorLog' :'',
        'isDisabled': true,
        'filterSchedule': {
            'siteID': App.siteID,
            'scheduledDate': new Date().toISOString().substring(0, 10),
            'clinicID': 0
        }
    })



    updateProcessFormSigns(ev,req){
        var index = this.state.processForm.process.sign.indexOf(req);
        if (ev.target.checked) {
            if (index == -1)
                this.state.processForm.process.sign.push(req);
        } else {
            if (index !== -1) {
                this.state.processForm.process.sign.splice(index, 1);
            }
        }
        this.setState({processForm: this.state.processForm});
        console.log("processForm sign---", this.state.processForm);
    }
    handleLogin(ev) {
        var form = $('#frm-login');
        form.addClass('was-validated');

        if (form[0].checkValidity() === false) {
            return;
        }

        let data = $.extend({}, this.state.form);


        ev.preventDefault();

        this.medicalFlowModel.getTreatmentProcess(data).then((resp) => {
            if(resp.status){
                console.log('data',data);
                var data = resp.data;
                this.state.processForm.scheduleID = data.schedules.id;
                this.state.processForm.patient.id = data.patientInformation.id;
                this.state.processForm.patient.name = data.patientInformation.name;
                this.setState({'processForm': this.state.processForm}, () => {  });

                this.state.stopProcessForm.scheduleID = data.schedules.id;
                this.state.stopProcessForm.patient.id = data.patientInformation.id;
                this.state.stopProcessForm.patient.name = data.patientInformation.name;
                this.setState({'stopProcessForm': this.state.stopProcessForm}, () => {  });

                this.setState({'showHistory': 1}, () => {
                    console.log('showHisstory',this.state.showHistory);
                });
            }else{
                //ko tim thay
                let messErr = resp.data;
                this.setState({'errorLog': messErr}, () => {   });
            }
        }).catch((xhr) => {
            $.toast({
                text: "Xác thực không hợp lệ",
                position: 'top-right'
            })
        });
    }

    updateTreatmentProcess(ev){
        let data = $.extend({}, this.state.processForm);
        ev.preventDefault();
        if( !this.state.processForm.process.temperature ||
            !this.state.processForm.process.datetime
        ){
            ev.preventDefault();
            window.alert("Yêu cầu nhập thông tin theo dõi !");
            return;
        }
        this.medicalFlowModel.updateTreatmentProcess(data).then((resp) => {
            if(resp.status){
                $.toast({
                    text: "Thêm mới chỉ số thành công",
                    position: 'top-right'
                })
            }else{
                //ko tim thay
                ev.preventDefault();
                window.alert("Thêm mới chỉ số không thành công !");
                return;
            }
        }).catch((xhr) => {
            $.toast({
                text: "Cập nhật không thành công",
                position: 'top-right'
            })
        });
    }

    updateStopProcess(ev){
        let data = $.extend({}, this.state.stopProcessForm);
        ev.preventDefault();
        console.log('updateStopProcess',data);
        if(
            !this.state.stopProcessForm.reason.value||
            !this.state.stopProcessForm.afterCure.breathingMachine.value||
//             !this.state.stopProcessForm.afterCure.breathingMachine.startDate||
//             !this.state.stopProcessForm.afterCure.breathingMachine.endDate ||

            !this.state.stopProcessForm.evaluate.testResults.value||
            !this.state.stopProcessForm.evaluate.condition.value||
            !this.state.stopProcessForm.evaluate.symptom.value

        ){
            ev.preventDefault();
            window.alert(" Yêu cầu nhập đủ thông tin !");
            return;
        }
            this.medicalFlowModel.updateStopProcess(data).then((resp) => {
                if(resp.status){
                    $.toast({
                        text: "Dừng điều trị thành công",
                        position: 'top-right'
                    })
                    $("#stopTreatmentProcess").modal('hide');
                    location.reload();
                }else{
                    //ko tim thay
                    ev.preventDefault();
                    window.alert("Cập nhật không thành công !");
                    return;
                }
            }).catch((xhr) => {
                $.toast({
                    text: "Cập nhật không thành công",
                    position: 'top-right'
                })
            });
    }

    pageContent() {
        return(
            <NoLayout>
                {this.state.showHistory == 1 && <div className="contentFlow">
                    <div className="titleFlow">
                        <h2>Theo dõi chỉ số trong quá trình hỗ trợ điều trị</h2>
                    </div>
                    <div className="fullName">
                        <h2 style={{"textTransform":"uppercase"}}>{this.state.processForm.patient.name}</h2>
                    </div>
                    <div className="detailIndex">
                        <ul className="nav nav-tabs">
                            <li className="nav-item">
                                <a className="nav-link active" data-toggle="tab" href="#infor"> Theo dõi chỉ số </a>
                            </li>
                            {/*       <li className="nav-item"> */}
                            {/*         <a className="nav-link" data-toggle="tab" href="#_history"> Lịch sử điều trị </a> */}
                            {/*       </li> */}
                        </ul>
                        {/*//theo doi dieu tri*/}
                        <div className="row __content">
                            <div className="tab-content col-md-6">
                                <div id="infor" className="tab-pane active">
                                    <br />
                                    <form onSubmit={(ev)=> { this.handleSubmit(ev); }} ref={(elm) => { this.processForm = elm; }}> <h6 style={{ textTransform: 'uppercase'}}>Diễn biến</h6>
                                        <div className="row">
                                            <div className="col-lg-6">
                                                <label style={{fontWeight: 400}}>Nhiệt độ</label>
                                                <input id="temperature" type="number" name="temperature" placeholder="" className="form-control " onChange={(ev)=>
                                                {this.state.processForm.process.temperature = ev.target.value;this.setState({processForm: this.state.processForm}); }}/>
                                            </div>
                                            <div className="col-lg-6">
                                                <label style={{ fontWeight: 400 }}>Thời gian đo</label>
                                                <div style={{ display: 'flex'}}>
                                                    <input id="searchReqDate" type="datetime-local" className="form-control" style={{height: "30px"}} onChange={(ev)=>
                                                    {this.state.processForm.process.datetime = ev.target.value;this.setState({processForm: this.state.processForm}); }}/>
                                                </div>
                                            </div>
                                        </div>
                                        <h6 style={{ marginTop:20,textTransform: 'uppercase'}}>Biểu hiện</h6>
                                        <div className="row">
                                            <div className="col-lg-4">
                                                <div className="form-check">
                                                    <label className="form-check-label">
                                                        <input id="cough" className="form-check-input" type="checkbox" value="Ho"
                                                               checked={this.state.processForm.process.sign.includes("Ho")}
                                                               onChange={(ev)=>
                                                                   // {this.state.processForm.process.sign.push(ev.target.value); this.setState({processForm: this.state.processForm}); }}/>
                                                               {this.updateProcessFormSigns(ev,"Ho") ;}}/>
                                                        Ho</label>
                                                </div>
                                            </div>
                                            <div className="col-lg-4">
                                                <div className="form-check">
                                                    <label className="form-check-label">
                                                        <input id="lootSense" className="form-check-input" type="checkbox" value="Mất khứu giác"
                                                               checked={this.state.processForm.process.sign.includes("Mất khứu giác")}
                                                               onChange={(ev)=>
                                                                   // {this.state.processForm.process.sign.push(ev.target.value);this.setState({processForm: this.state.processForm}); }}/>
                                                               {this.updateProcessFormSigns(ev,"Mất khứu giác") }}/>
                                                        Mất khứu giác</label>
                                                </div>
                                            </div>
                                            <div className="col-lg-4">
                                                <div className="form-check">
                                                    <label className="form-check-label">
                                                        <input id="chills" className="form-check-input" type="checkbox" value="Ớn lạnh-gai rét"
                                                               checked={this.state.processForm.process.sign.includes("Ớn lạnh-gai rét")}
                                                               onChange={(ev)=>
                                                                   // {this.state.processForm.process.sign.push(ev.target.value); this.setState({processForm: this.state.processForm}); }}/>
                                                               {this.updateProcessFormSigns(ev,"Ớn lạnh-gai rét") }}/>
                                                        Ớn lạnh-gai rét</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="row" style={{ marginTop:20 }}>
                                            <div className="col-lg-4">
                                                <div className="form-check">
                                                    <label className="form-check-label">
                                                        <input id="breath" className="form-check-input" type="checkbox" value="Khó thở"
                                                               checked={this.state.processForm.process.sign.includes("Khó thở")}
                                                               onChange={(ev)=>
                                                                   // {this.state.processForm.process.sign.push(ev.target.value); this.setState({processForm: this.state.processForm}); }}/>
                                                               {this.updateProcessFormSigns(ev,"Khó thở") }}/>
                                                        Khó thở</label>
                                                </div>
                                            </div>
                                            <div className="col-lg-4">
                                                <div className="form-check">
                                                    <label className="form-check-label">
                                                        <input id="taste" className="form-check-input" type="checkbox" value="Mất vị giác"
                                                               checked={this.state.processForm.process.sign.includes("Mất vị giác")}
                                                               onChange={(ev)=>
                                                                   // {this.state.processForm.process.sign.push(ev.target.value); this.setState({processForm: this.state.processForm}); }}/>
                                                               {this.updateProcessFormSigns(ev,"Mất vị giác") }}/>
                                                        Mất vị giác</label>
                                                </div>
                                            </div>
                                            <div className="col-lg-4">
                                                <div className="form-check">
                                                    <label className="form-check-label">
                                                        <input id="chestPain" className="form-check-input" type="checkbox" value="Đau tức ngực kéo dài"
                                                               checked={this.state.processForm.process.sign.includes("Đau tức ngực kéo dài")}
                                                               onChange={(ev)=>
                                                                   // {this.state.processForm.process.sign.push(ev.target.value); this.setState({processForm: this.state.processForm}); }}/>
                                                               {this.updateProcessFormSigns(ev,"Đau tức ngực kéo dài") }}/>
                                                        Đau tức ngực kéo dài</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="row" style={{ marginTop:20 }}>
                                            <div className="col-lg-4">
                                                <div className="form-check">
                                                    <label className="form-check-label">
                                                        <input id="soreThroat" className="form-check-input" type="checkbox" value="Đau họng"
                                                               checked={this.state.processForm.process.sign.includes("Đau họng")}
                                                               onChange={(ev)=>
                                                               {this.updateProcessFormSigns(ev,"Đau họng") }}/>
                                                        {/*// {this.state.processForm.process.sign.push(ev.target.value); this.setState({processForm: this.state.processForm}); }}/>*/}
                                                        Đau họng</label>
                                                </div>
                                            </div>
                                            <div className="col-lg-4">
                                                <div className="form-check">
                                                    <label className="form-check-label">
                                                        <input id="diarrhea" className="form-check-input" type="checkbox" value="Tiêu chảy"
                                                               checked={this.state.processForm.process.sign.includes("Tiêu chảy")}
                                                               onChange={(ev)=>
                                                               {this.updateProcessFormSigns(ev,"Tiêu chảy") }}/>
                                                        {/*// {this.state.processForm.process.sign.push(ev.target.value); this.setState({processForm: this.state.processForm}); }}/>*/}
                                                        Tiêu chảy</label>
                                                </div>
                                            </div>
                                            <div className="col-lg-4">
                                                <div className="form-check">
                                                    <label className="form-check-label">
                                                        <input id="other" className="form-check-input" type="checkbox" value="Khác" onClick={this.handleDisabledText}
                                                               checked={this.state.processForm.process.sign.includes("Khác")}
                                                               onChange={(ev)=>
                                                               {this.updateProcessFormSigns(ev,"Khác") }}/>
                                                        Khác</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="row" style={{ marginTop:20 }}>
                                            <div className="col-lg-12">
                                               <textarea id="other-detail" type="text" name="discripsion" placeholder="Nhập biểu hiện khác" className="form-control" style={{height: 60}} disabled={this.state.isDisabledText?"":"1"}
                                                         onChange={(ev) => {
                                                             this.state.processForm.process.disription = ev.target.value;
                                                             this.setState({processForm: this.state.processForm});
                                                         }}
                                               ></textarea>
                                            </div>
                                        </div>
                                        <div className="row buttonHistory" style={{ marginTop:40,justifyContent:'flex-end',padding:10 }}>
                                            <button type="button" className="btn btn-danger" data-toggle="modal" data-target="#stopTreatmentProcess">Dừng hỗ trợ điều trị</button>
                                            <button className="btn btn-primary" type="button"
                                                    onClick={(ev) => {
                                                        this.updateTreatmentProcess(ev)
                                                    }}
                                            >Cập nhật</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="modal" id="stopTreatmentProcess">
                        <div className="modal-dialog">
                            <div className="modal-content">
                                <div className="modal-header">
                                    <h4 className="modal-title">Dừng sử dụng sản phẩm</h4>
                                    <button type="button" className="close" data-dismiss="modal"></button>
                                </div>
                                <div className="modal-body">
                                    <form onSubmit={(ev)=> { this.handleSubmitmodal(ev); }} ref={(elm) => { this.stopProcessForm = elm; }}>
                                        <h6 style={{textTransform: 'uppercase',fontSize: 15,fontWeight: 600}}>lý do (<small>*</small>)</h6>
                                        <div className="row">
                                            <div className="form-group" style={{ marginBottom: 0 }}>
                                                <div className="d-inline" style={{ marginLeft:25}}>
                                                    <input type="radio" id="radioReason1" name="r1" className="Âm tính" value="1" onClick={this.handleUndisabled}
                                                           onChange={(ev) => {
                                                               this.state.stopProcessForm.reason.value = ev.target.value;
                                                               this.state.stopProcessForm.reason.label = ev.target.className;
                                                               this.setState({
                                                                   stopProcessform: this.state.stopProcessform
                                                               });
                                                           }}
                                                    />
                                                    <label htmlFor="radioReason1">&ensp;Âm tính (Khỏi bệnh) </label>
                                                </div>
                                                <div className="d-inline" style={{ marginLeft:50}}>
                                                    <input type="radio" id="radioReason2" name="r1" className="Khác" value="Không" onClick={this.handleDisabled}
                                                           onChange={(ev) => {
                                                               this.state.stopProcessForm.reason.value = ev.target.value;
                                                               this.state.stopProcessForm.reason.label = ev.target.className;
                                                               this.setState({
                                                                   stopProcessform: this.state.stopProcessform
                                                               });
                                                           }}/>
                                                    <label htmlFor="radioReason2">&ensp;Khác </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="row">
                                            <div className="col-lg-12">
                                                <textarea id="discripsion" type="text" name="discripsion" placeholder="Nhập lý do khác" className="form-control" style={{height: 60}} disabled={this.state.isDisabled?"":"1"}
                                                          onChange={(ev) => {
                                                              this.state.stopProcessForm.reason.discripsion = ev.target.value;
                                                              this.setState({
                                                                  stopProcessform: this.state.stopProcessform
                                                              });
                                                          }}
                                                ></textarea>
                                            </div>
                                        </div>
                                        <h6 style={{marginTop:20,textTransform: 'uppercase',fontSize: 15,fontWeight: 600}}>thông tin sau điều trị</h6>
                                        <div className="row">
                                            <div className="col-lg-6" style={{ marginTop: 10 }}>
                                                <label style={{fontWeight: 500}}>Bệnh nhân có thở máy không? (<small>*</small>)</label>
                                                <div className="form-group" style={{ marginBottom: 0 }}>
                                                    <div className="d-inline" style={{ marginLeft:10}}>
                                                        <input type="radio" id="radioVentilator1" name="r2" value="1" className="Có"
                                                               onChange={(ev) => {
                                                                   this.state.stopProcessForm.afterCure.breathingMachine.value = ev.target.value;
                                                                   this.state.stopProcessForm.afterCure.breathingMachine.label = ev.target.className;
                                                                   this.setState({
                                                                       stopProcessform: this.state.stopProcessform
                                                                   });
                                                               }}
                                                        />
                                                        <label htmlFor="radioVentilator1">&ensp;Có </label>
                                                    </div>
                                                    <div className="d-inline" style={{ marginLeft:100}}>
                                                        <input type="radio" id="radioVentilator2" name="r2" value="Không" className="Không"
                                                               onChange={(ev) => {
                                                                   this.state.stopProcessForm.afterCure.breathingMachine.value = ev.target.value;
                                                                   this.state.stopProcessForm.afterCure.breathingMachine.label = ev.target.className;
                                                                   this.setState({
                                                                       stopProcessform: this.state.stopProcessform
                                                                   });
                                                               }}/>
                                                        <label htmlFor="radioVentilator2">&ensp;Không </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="row">
                                            <div className="col-lg-6">
                                                <label style={{fontWeight: 500}}>Ngày bắt đầu</label>
                                                <div style={{ display: 'flex'}}>
                                                    <input id="searchReqDate" type="date" className="form-control" style={{height: "30px"}}
                                                           onChange={(ev) => {
                                                               this.state.stopProcessForm.afterCure.breathingMachine.startDate = ev.target.value;
                                                               this.setState({
                                                                   stopProcessform: this.state.stopProcessform
                                                               });
                                                           }}/>
                                                </div>
                                            </div>
                                            <div className="col-lg-6">
                                                <label style={{fontWeight: 500}}>Ngày kết thúc</label>
                                                <div style={{ display: 'flex'}}>
                                                    <input id="searchReqDate" type="date" className="form-control" style={{height: "30px"}}
                                                           onChange={(ev) => {
                                                               this.state.stopProcessForm.afterCure.breathingMachine.endDate = ev.target.value;
                                                               this.setState({
                                                                   stopProcessform: this.state.stopProcessform
                                                               });
                                                           }}/>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="row">
                                            <div className="col-lg-12">
                                                <input id="discripsion" type="text" name="discripsion" placeholder="Mô tả" className="form-control" style={{marginTop: 10}}
                                                       onChange={(ev) => {
                                                           this.state.stopProcessForm.afterCure.breathingMachine.descripsion = ev.target.value;
                                                           this.setState({
                                                               stopProcessform: this.state.stopProcessform
                                                           });
                                                       }}
                                                />
                                            </div>
                                        </div>
                                        <div className="row">
                                        <label className="col-lg-12" style={{fontWeight: 500,marginTop: 10}}>Bệnh nhân có phải dùng thuốc kháng virus không? (<small>*</small>)</label>
                                            <div className="col-lg-6">
                                                <div className="form-group" style={{ marginBottom: 0 }}>
                                                    <div className="d-inline" style={{ marginLeft:10}}>
                                                        <input type="radio" id="radioVirus1" name="r3" value="1" className="Có" onChange={(ev) => {
                                                            this.state.stopProcessForm.afterCure.antiviralDrugs.value = ev.target.value;
                                                            this.state.stopProcessForm.afterCure.antiviralDrugs.label = ev.target.className;
                                                            this.setState({
                                                                stopProcessform: this.state.stopProcessform
                                                            });
                                                        }}/>
                                                        <label htmlFor="radioVirus1">&ensp;Có </label>
                                                    </div>
                                                    <div className="d-inline" style={{ marginLeft:100}}>
                                                        <input type="radio" id="radioVirus2" name="r3" value="Không" className="Không"
                                                               onChange={(ev) => {
                                                                   this.state.stopProcessForm.afterCure.antiviralDrugs.value = ev.target.value;
                                                                   this.state.stopProcessForm.afterCure.antiviralDrugs.label = ev.target.className;
                                                                   this.setState({
                                                                       stopProcessform: this.state.stopProcessform
                                                                   });
                                                               }}/>
                                                        <label htmlFor="radioVirus2">&ensp;Không </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="row">
                                            <div className="col-lg-6">
                                                <label style={{fontWeight: 500}}>Ngày bắt đầu</label>
                                                <div style={{ display: 'flex'}}>
                                                    <input id="searchReqDate" type="date" className="form-control" style={{height: "30px"}}
                                                           onChange={(ev) => {
                                                               this.state.stopProcessForm.afterCure.antiviralDrugs.startDate = ev.target.value;
                                                               this.setState({
                                                                   stopProcessform: this.state.stopProcessform
                                                               });
                                                           }}/>
                                                </div>
                                            </div>
                                            <div className="col-lg-6">
                                                <label style={{fontWeight: 500}}>Ngày kết thúc</label>
                                                <div style={{ display: 'flex'}}>
                                                    <input id="searchReqDate" type="date" className="form-control" style={{height: "30px"}}
                                                           onChange={(ev) => {
                                                               this.state.stopProcessForm.afterCure.antiviralDrugs.endDate = ev.target.value;
                                                               this.setState({
                                                                   stopProcessform: this.state.stopProcessform
                                                               });
                                                           }}/>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="row">
                                            <div className="col-lg-12">
                                                <input id="discripsionVirus" type="text" name="discripsionVirus" placeholder="Mô tả" className="form-control" style={{marginTop: 10}}
                                                       onChange={(ev) => {
                                                           this.state.stopProcessForm.afterCure.antiviralDrugs.descripsion = ev.target.value;
                                                           this.setState({
                                                               stopProcessform: this.state.stopProcessform
                                                           });
                                                       }}/>
                                            </div>
                                        </div>
                                        <div className="row">
                                            <div className="col-lg-6" style={{ marginTop: 10 }}>
                                                <label style={{fontWeight: 500}}>Các biến chứng trong quá trình điều trị (<small>*</small>)</label>
                                                <div className="form-group" style={{ marginBottom: 0 }}>
                                                    <div className="d-inline" style={{ marginLeft:10}}>
                                                        <input type="radio" id="radioSymptom1" name="r4" value="1" className="Có"
                                                               onChange={(ev) => {
                                                                   this.state.stopProcessForm.afterCure.symptoms.value = ev.target.value;
                                                                   this.state.stopProcessForm.afterCure.symptoms.label = ev.target.className;
                                                                   this.setState({
                                                                       stopProcessform: this.state.stopProcessform
                                                                   });
                                                               }}/>
                                                        <label htmlFor="radioSymptom1">&ensp;Có </label>
                                                    </div>
                                                    <div className="d-inline" style={{ marginLeft:100}}>
                                                        <input type="radio" id="radioSymptom2" name="r4" value="Không" className="Không"
                                                               onChange={(ev) => {
                                                                   this.state.stopProcessForm.afterCure.symptoms.value = ev.target.value;
                                                                   this.state.stopProcessForm.afterCure.symptoms.label = ev.target.className;
                                                                   this.setState({
                                                                       stopProcessform: this.state.stopProcessform
                                                                   });
                                                               }}/>
                                                        <label htmlFor="radioSymptom2">&ensp;Không </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="row">
                                            <div className="col-lg-6">
                                                <label style={{fontWeight: 500}}>Ngày bắt đầu</label>
                                                <div style={{ display: 'flex'}}>
                                                    <input id="searchReqDate" type="date" className="form-control" style={{height: "30px"}}
                                                           onChange={(ev) => {
                                                               this.state.stopProcessForm.afterCure.symptoms.startDate = ev.target.value;
                                                               this.setState({
                                                                   stopProcessform: this.state.stopProcessform
                                                               });
                                                           }}/>
                                                </div>
                                            </div>
                                            <div className="col-lg-6">
                                                <label style={{fontWeight: 500}}>Ngày kết thúc</label>
                                                <div style={{ display: 'flex'}}>
                                                    <input id="searchReqDate" type="date" className="form-control" style={{height: "30px"}}
                                                           onChange={(ev) => {
                                                               this.state.stopProcessForm.afterCure.symptoms.endDate = ev.target.value;
                                                               this.setState({
                                                                   stopProcessform: this.state.stopProcessform
                                                               });
                                                           }}/>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="row">
                                            <div className="col-lg-12">
                                                <input id="discripsionSymptom" type="text" name="discripsionSymptom" placeholder="Mô tả loại biến chứng - Ngày xuất hiện biến chứng - Ngày kết thúc biến chứng" className="form-control" style={{marginTop: 10}}
                                                       onChange={(ev) => {
                                                           this.state.stopProcessForm.afterCure.symptoms.descripsion = ev.target.value;
                                                           this.setState({
                                                               stopProcessform: this.state.stopProcessform
                                                           });
                                                       }}/>
                                            </div>
                                        </div>
                                        <h6 style={{marginTop:20,textTransform: 'uppercase',fontSize: 15,fontWeight: 600}}>Đánh giá tình trạng sức khỏe hiện tại</h6>
                                        <div className="row">
                                            <div className="col-lg-12">
                                                <label style={{fontWeight: 500}}>Kết quả xét nghiệm Covid tại thời điểm hiện tại (<small>*</small>)</label>
                                                <div className="form-group" style={{ marginBottom: 0 }}>
                                                    <div className="d-inline" style={{ marginLeft:10}}>
                                                        <input type="radio" id="radioTest1" name="r5" value="Không" className="Dương tính"
                                                               onChange={(ev) => {
                                                                   this.state.stopProcessForm.evaluate.testResults.value = ev.target.value;
                                                                   this.state.stopProcessForm.evaluate.testResults.label = ev.target.className;
                                                                   this.setState({
                                                                       stopProcessform: this.state.stopProcessform
                                                                   });
                                                               }}/>
                                                        <label htmlFor="radioTest1">&ensp;Dương tính </label>
                                                    </div>
                                                    <div className="d-inline" style={{ marginLeft:125}}>
                                                        <input type="radio" id="radioTest2" className="Âm tính" name="r5" value="1"
                                                               onChange={(ev) => {
                                                                   this.state.stopProcessForm.evaluate.testResults.value = ev.target.value;
                                                                   this.state.stopProcessForm.evaluate.testResults.label = ev.target.className;
                                                                   this.setState({
                                                                       stopProcessform: this.state.stopProcessform
                                                                   });
                                                               }}/>
                                                        <label htmlFor="radioTest2">&ensp;Âm tính </label>
                                                    </div>
                                                    <div className="d-inline" style={{ marginLeft:95}}>
                                                        <input type="radio" id="radioTest3" className="Chưa xét nghiệm" name="r5" value="2"
                                                               onChange={(ev) => {
                                                                   this.state.stopProcessForm.evaluate.testResults.value = ev.target.value;
                                                                   this.state.stopProcessForm.evaluate.testResults.label = ev.target.className;
                                                                   this.setState({
                                                                       stopProcessform: this.state.stopProcessform
                                                                   });
                                                               }}/>
                                                        <label htmlFor="radioTest3">&ensp;Chưa xét nghiệm </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="row">
                                            <div className="col-lg-12">
                                                <label style={{fontWeight: 500}}>Thể trạng hiện tại (<small>*</small>)</label>
                                                <div className="form-group" style={{ marginBottom: 0 }}>
                                                    <div className="d-inline" style={{ marginLeft:10}}>
                                                        <input type="radio" id="radioPhysical1" name="r6"
                                                               className="Đã hồi phục hoàn toàn" value="1"
                                                               onChange={(ev) => {
                                                                   this.state.stopProcessForm.evaluate.condition.value = ev.target.value;
                                                                   this.state.stopProcessForm.evaluate.condition.label = ev.target.className;
                                                                   this.setState({
                                                                       stopProcessform: this.state.stopProcessform
                                                                   });
                                                               }} />
                                                        <label htmlFor="radioPhysical1">&ensp;Đã phục hồi hoàn toàn </label>
                                                    </div>
                                                    <div className="d-inline" style={{ marginLeft:50}}>
                                                        <input type="radio" id="radioPhysical2" name="r6"
                                                               className="Đang hồi phục" value="2"
                                                               onChange={(ev) => {
                                                                   this.state.stopProcessForm.evaluate.condition.value = ev.target.value;
                                                                   this.state.stopProcessForm.evaluate.condition.label = ev.target.className;
                                                                   this.setState({
                                                                       stopProcessform: this.state.stopProcessform
                                                                   });
                                                               }} />
                                                        <label htmlFor="radioPhysical2">&ensp;Đang hồi phục </label>
                                                    </div>
                                                    <div className="d-inline" style={{ marginLeft:50}}>
                                                        <input type="radio" id="radioPhysical3" name="r6"
                                                               className="Chưa hồi phục" value="Không"
                                                               onChange={(ev) => {
                                                                   this.state.stopProcessForm.evaluate.condition.value = ev.target.value;
                                                                   this.state.stopProcessForm.evaluate.condition.label = ev.target.className;
                                                                   this.setState({
                                                                       stopProcessform: this.state.stopProcessform
                                                                   });
                                                               }} />
                                                        <label htmlFor="radioPhysical3">&ensp;Chưa hồi phục </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="row">
                                            <div className="col-lg-12">
                                                <label style={{fontWeight: 500}}>Triệu chứng (<small>*</small>)</label>
                                                <div className="form-group" style={{ marginBottom: 0 }}>
                                                    <div className="d-inline" style={{ marginLeft:10}}>
                                                        <input type="radio" id="radioTc1" name="r7" className="Đã hồi phục hoàn toàn" value="1"
                                                               onChange={(ev) => {
                                                                   this.state.stopProcessForm.evaluate.symptom.value = ev.target.value;
                                                                   this.state.stopProcessForm.evaluate.symptom.label = ev.target.className;
                                                                   this.setState({
                                                                       stopProcessform: this.state.stopProcessform
                                                                   });
                                                               }}/>
                                                        <label htmlFor="radioTc1">&ensp;Đã phục hồi hoàn toàn </label>
                                                    </div>
                                                    <div className="d-inline" style={{ marginLeft:50}}>
                                                        <input type="radio" id="radioTc2" name="r7" className="Đang hồi phục" value="2"
                                                               onChange={(ev) => {
                                                                   this.state.stopProcessForm.evaluate.symptom.value = ev.target.value;
                                                                   this.state.stopProcessForm.evaluate.symptom.label = ev.target.className;
                                                                   this.setState({
                                                                       stopProcessform: this.state.stopProcessform
                                                                   });
                                                               }}/>
                                                        <label htmlFor="radioTc2">&ensp;Đang hồi phục </label>
                                                    </div>
                                                    <div className="d-inline" style={{ marginLeft:50}}>
                                                        <input type="radio" id="radioTc3" name="r7" className="Chưa hồi phục" value="Không"
                                                               onChange={(ev) => {
                                                                   this.state.stopProcessForm.evaluate.symptom.value = ev.target.value;
                                                                   this.state.stopProcessForm.evaluate.symptom.label = ev.target.className;
                                                                   this.setState({
                                                                       stopProcessform: this.state.stopProcessform
                                                                   });
                                                               }}/>
                                                        <label htmlFor="radioTc3">&ensp;Chưa hồi phục </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="row">
                                            <div className="col-lg-12">
                                                <input id="symptom" type="text" name="symptom" placeholder="Nhập triệu chứng" className="form-control"
                                                       onChange={(ev) => {
                                                           this.state.stopProcessForm.evaluate.symptom.discripsion = ev.target.value;
                                                           this.setState({
                                                                   stopProcessform: this.state.stopProcessform
                                                               },()=>{
                                                                   console.log(this.state)
                                                               }
                                                           );
                                                       }}/>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-primary" data-dismiss="modal">Hủy bỏ</button>
                                    <button type="button" className="btn btn-danger"
                                            onClick={(ev) => {
                                                this.updateStopProcess(ev)
                                            }}
                                        // data-dismiss="modal"
                                    >Cập nhật</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>}
                {/*login*/}
                {this.state.showHistory == 0 && <div>
                    <div className="layout bg-gradient-info">
                        <div className="container">
                            <div className="row full-height align-items-center view-port">
                                <div className="wrapper">
                                    <div className="login-a">
                                        <div className="logo">
                                            <img className="logo-icon logo-name"
                                                 src=""/>
                                            {/*<img className="logo-icon" src={App.themeUrl + '/images/logo-login-icon.png'} />*/}
                                            {/*<img className="logo-name" src={App.themeUrl + '/images/logo-pacs-name-icon.png'}/>*/}
                                        </div>
                                        {/* <div style="clear: both"></div> */}
                                    </div>
                                    <div className="card card-shadow card-login">
                                        <div className="card-body">
                                            <div className="p-h-5 p-v-5">
                                                <form id="frm-login" noValidate>


                                                    <div className="form-group left-addon inner-addon">
                                                        <i className="fa fa-user"></i>
                                                        <input type="text" className="form-control form-control-lg"
                                                               placeholder="Nhập số điện thoại" autoFocus required

                                                               onChange={(ev) => {
                                                                   this.state.form.phone = ev.target.value;
                                                               }}

                                                        />
                                                        <div className="invalid-tooltip">
                                                            Nhập thông tin số điện thoại đăng ký
                                                        </div>
                                                    </div>
                                                    <div className="form-group left-addon inner-addon">
                                                        <i className="fa fa-lock"></i>
                                                        <input type="password" id="txt-password"
                                                               className="form-control form-control-lg"
                                                               placeholder="Nhập mật khẩu" required
                                                               onChange={(ev) => {
                                                                   this.state.form.password = ev.target.value;
                                                               }}
                                                        />
                                                        <div className="invalid-tooltip">
                                                            Nhập thông tin mật khẩu
                                                        </div>
                                                        {/*<a href="#" className="link-forget-pass">{Lang.t('loginPage.forget.pass')}?</a>*/}
                                                    </div>

                                                    {this.state.errorLog &&
                                                    <p style={{color: "red"}}>{this.state.errorLog}</p>
                                                    }
                                                    <button type="button"
                                                            onClick={(ev) => {
                                                                this.handleLogin(ev)
                                                            }}
                                                            className="btn-login-success btn btn-block btn-lg">
                                                        Đăng nhập
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="forget">
                                        {/*                                                             <div className="support"> */}
                                        {/*                                                                 <b> <i className="fa fa-phone-square"></i> Tổng đài hỗ trợ <span */}
                                        {/*                                                                     className="phone">0967 645 444</span></b><br/> */}
                                        {/*                                                             </div> */}
                                    </div>

                                    <div className="copy-right">
                                        <span>© Copyright 2021, All Rights Reserved</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>}
            </NoLayout>
        )
    }
    render() {
        return this.pageContent();
    }
}