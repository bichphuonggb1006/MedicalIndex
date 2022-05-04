const SMS_SCHEDULE = 1;
const SMS_NOTICE_RESULT = 2;
const SMS_NOTICE_PAYMENT = 3;

class ScheduleEdit extends PureComponent {
    constructor(props) {
        super(props);

        let now = new Date().toISOString()
        this.state = {
            'form': null,
            'clinicDirs': {},
            'defaultScheduleDatetime': now.substring(0, 10) + "T" + now.substring(11, 16),
            clinics: [],
            "chosenClinicID": "",
            "showSendSMSPayment": 0
        }

        this.scheduleModel = new ScheduleModel();
        this.vclinicModel = new VclinicModel();
        Lang.load('teleclinic', 'teleclinic');
    }

    componentDidMount() {
        let clinicDirs = {}
        new TeleclinicServiceModel().getServicesDir().then((dirs) => {
            for (let i in dirs) {
                let dir = dirs[i]
                clinicDirs[dir.id] = dir
            }
            this.setState({clinicDirs: clinicDirs})
        })

        this.getCLinics();

        var field = 'SiteConfig';
        this.scheduleModel.getFieldsFormId(field).then((resp) => {
            delete resp["version"];
            if (!resp.length) return;
            var siteInfo = JSON.parse(resp[0].value);
            if(siteInfo.hasOwnProperty('smsPayment')){
                var sendSMSPayment = !siteInfo || !siteInfo.hasOwnProperty('smsPayment') ? 0 : siteInfo.smsPayment;
                this.setState({'showSendSMSPayment': sendSMSPayment}, () => {
                });
            }
        });
    }

    getCLinics() {

        return new Promise(done => {
            (new VclinicModel).getClinics({siteID: App.siteID, groupBy: "depID"})
                .then(resp => {
                    console.log("resp", resp);
                    delete resp["version"];
                    let allClinics = {};

                    // console.log(resp);
                    resp.forEach(department => {
                        department.clinics.forEach(clinic => {
                            allClinics[clinic.id] = clinic.name;
                        })
                    });

                    let defaultClinicID = "";
                    if (!jQuery.isEmptyObject(allClinics))
                        defaultClinicID = Object.keys(allClinics)[0];

                    this.setState({clinics: resp, chosenClinicID: defaultClinicID}, () => {
                        done(resp)
                    });
                })
        });
    }

    open(schedule) {
        //lấy lại dữ liệu mới
        new ScheduleModel().getScheduleById(schedule.id).then((schedule) => {
            let newSchedule = $.extend({}, schedule)
            if (newSchedule.reqService.dirID) {
                // console.log(newSchedule.reqService.dirID)
                //assign dir
                let level2 = this.state.clinicDirs[newSchedule.reqService.dirID]
                let level1 = this.state.clinicDirs[level2.parentID]
                newSchedule.reqService.dirs = []
                if (level1)
                    newSchedule.reqService.dirs.push(level1)
                if (level2)
                    newSchedule.reqService.dirs.push(level2)
            }

            let data = {
                'defaultScheduleDatetime': newSchedule.reqTimes >= 10 ? newSchedule.reqDate + "T" + newSchedule.reqTimes + ":00" : newSchedule.reqDate + "T" + "0" + newSchedule.reqTimes + ":00"
            };

            if (newSchedule.scheduledDate) {
                data["defaultScheduleDatetime"] = moment(newSchedule.scheduledDate).format("YYYY-MM-DDTHH:mm");
            }
            //default clinic : services[0] request
            this.getServicesClinic(newSchedule.reqServiceID);

            if (newSchedule.vclinicID)
                data["chosenClinicID"] = newSchedule.vclinicID;

            this.state.form = newSchedule;
            console.log("form", this.state.form);
            this.setPureState(Object.assign({'form': newSchedule}, data), () => {
                this.modal.showModal();
            })
        })
        return new Promise((done) => {
            this.done = done || new Function;
        });
    }


    onModalShown() {
        //reset validate
        $(this.form).removeClass('was-validated');
        if (['scheduled', 'completed'].indexOf(this.state.form.status) >= 0 && App.getUser().hasPrivilege('KhamChoBenhNhan'))
            this.TabControl.setActive('tab-2')
        else
            this.TabControl.setActive('tab-1')
    }

    onModalHidden() {
        console.log("vao day");
        //reset ui về trạng thái mặc định
        this.TabControl.setActive('tab-1');
    }

    componentWillUnmount() {
        let now = new Date().toISOString();
        this.setState({
            'form': null,
            'defaultScheduleDatetime': now.substring(0, 10) + "T" + now.substring(11, 16),
        });
    }

    chooseClinic(ev) {
        this.setState({chosenClinicID: ev.target.value})
    }

    initPrescription() {
        let bodyValue = "";
        Array(10).fill(0).forEach(() => {
            bodyValue += `
           <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
           `;
        });
        return (
            `<table class="table table-bordered table-hover prescription-table">
                <thead>
                <tr>
                    <th class="th-stt" scope="col">STT</th>
                    <th class="th-drugName" scope="col">${Lang.t("schedule.prescription.drugName")}</th>
                    <th class="th-dose" scope="col">${Lang.t("schedule.prescription.dose")}</th>
                    <th class="th-unit" scope="col">${Lang.t("schedule.prescription.unit")}</th>
                    <th class="th-quantity" scope="col">${Lang.t("schedule.prescription.quantity")}</th>
                </tr>
                </thead>
                <tbody>
                    ${bodyValue}
                     
                </tbody>
               

            </table>`
        );

    }

    schedule(ev) {
        ev.preventDefault();

        this.scheduleModel.confirmSchedule(
            this.state.form.id,
            {scheduledDate: this.state.defaultScheduleDatetime.replace("T", " "), vclinicID: this.state.chosenClinicID})
            .then(resp => {
                if (resp && resp["status"] === true) {
                    this.state.form.status = "scheduled";
                    this.setState({form: this.state.form});
                    $.toast({
                        text: Lang.t("schedule.success"),
                        position: 'top-right'
                    })

                    if (this.done)
                        this.done(resp);
                } else {
                    var error = "schedule.error";
                    if (resp.data.hasOwnProperty('error') && resp.data.error.length) {
                        error = resp.data.error;
                    }

                    $.toast({
                        text: Lang.t(error),
                        position: 'top-right'
                    })
                }
            });
    }

    cancelSchedule() {
        this.cancelScheduleModal.open(this.state.form.id).then(() => {
            this.modal.hideModal()
            if (this.done)
                this.done()
        });
    }

    onChangeDiagnosisField(ev, key) {
        this.state.form[key] = ev.target.value;
        this.setPureState({form: this.state.form});
    }

    handleChangePrescription = function (ev) {
        this.onChangeDiagnosisField(ev, "diagPrescription");
    }.bind(this);

    diagnosis(ev) {
        this.scheduleModel.diagnosis(
            this.state.form.id,
            {
                "diagDesc": this.state.form["diagDesc"],
                "diagConclusion": this.state.form["diagConclusion"],
                "diagRecommendation": this.state.form["diagRecommendation"],
                "diagPrescription": this.state.form["diagPrescription"],
                "reExamDate": this.state.form["reExamDate"],
                "createNextSchedule": this.state.form.createNextSchedule
            })
            .then(resp => {
                if (resp && resp["status"] === true) {
                    this.state.form.status = "completed";
                    this.setState({form: this.state.form});
                    $.toast({
                        text: Lang.t("schedule.success"),
                        position: 'top-right'
                    })
                    this.done()
                } else {
                    $.toast({
                        text: Lang.t("schedule.error"),
                        position: 'top-right'
                    })
                }
            }).catch(err => {
            console.log(err);
            $.toast({
                text: Lang.t("schedule.error"),
                position: 'top-right'
            })
        });

    }

    sendSMS(type, scheduleDatetime) {
        if (App.getUser().hasPrivilege('TiepNhanBenhNhan')) {
            if (this.state.form.status == "cancelled" || this.state.form.status == "unscheduled") {
                window.alert("Yêu cầu xếp lịch khám trước khi gửi SMS.");
                return false;
            }
        } else if (App.getUser().hasPrivilege('KhamChoBenhNhan')) {
            if (this.state.form.status != "completed") {
                window.alert("Yêu cầu chẩn đoán trước khi gửi SMS.");
                return false;
            }
        }

        this.scheduleModel.sendSMS({
            type: type,
            scheduleDatetime: scheduleDatetime,
            phoneNumber: this.state.form.patient.phone,
            uid: this.state.form.uid,
            password: this.state.form.patientPassword
        }).then(resp => {
            if (resp && resp["status"] === true) {
                $.toast({
                    text: Lang.t("schedule.success"),
                    position: 'top-right'
                })
            }
        })
    }

    sendSMSPayment(type, scheduleDatetime) {
        // kiểm tra dịch vụ có thu phí mới gửi SMS
        var reqService = this.state.form.reqService;
        if (!reqService || !reqService.id || !this.state.showSendSMSPayment)
            return false;

        // DV có thu phí + BHYT thì ko gửi SMS
        var price = !parseInt(reqService.price) || (reqService.hasOwnProperty('reqHealthInsurance') && reqService.reqHealthInsurance.length) ? 0 : parseInt(reqService.price);
        if (!price) {
            $.toast({
                text: Lang.t("schedule.sms.free"),
                position: 'top-right',
                hideAfter: 3000
            })
            return false;
        }

        this.scheduleModel.sendSMS({
            type: type,
            scheduleDatetime: scheduleDatetime,
            phoneNumber: this.state.form.patient.phone,
            uid: this.state.form.uid,
            password: this.state.form.patientPassword,
            createdTime: this.state.form.createdTime
        }).then(resp => {
            if (resp && resp.status) {
                $.toast({
                    text: Lang.t("schedule.success"),
                    position: 'top-right'
                })
            } else {
                Alert.open(Lang.t(resp.data));
            }
        })
    }

    updatePaymentStatus(paymentStatus) {
        var that = this;
        this.scheduleModel.updatePaymentStatus(this.state.form.id, paymentStatus).then(() => {
            this.state.form.paymentStatus = paymentStatus;
            this.setPureState({'form': this.state.form}, () => {
                this.trigger("loadRequest");
            })
        })
    }

    onCreateNextSchedule(checked) {
        //đã 1 lần tạo lịch khám tiếp theo rồi, nếu tạo nữa sẽ bị trùng
        if (this.state.form.nextSchedule && checked) {
            alert("Lịch tái khám đã được tạo từ trước, vui lòng hủy lịch tái khám đã tạo!")
            return
        }
        this.state.form.createNextSchedule = checked
        this.setPureState({form: this.state.form})
    }

    checkCanSchedule() {
        if (this.state.form.status == 'completed')
            return false
        if (App.getUser().hasPrivilege('TiepNhanBenhNhan') == false)
            return false
        return true
    }

    getServicesClinic(serviceID) {
        this.vclinicModel.getServiceClinics(serviceID).then((resp) => {
            delete resp["version"];
            if (resp.length) {
                // this.state.chosenClinicID = resp[0].id;
                this.setState({'chosenClinicID': resp[0].id});
            }
            console.log('this.setstate.chosenClinicID', this.state.chosenClinicID);
            console.log('getServiceClinics', resp);

        })

    }

    render() {
        let form = this.state.form
        if (!form || !this.state.clinicDirs)
            return (<div></div>)

        form.createNextSchedule = form.createNextSchedule ? true : false
        form.reExamDate = form.reExamDate ? form.reExamDate : ''
        return (
            <div>
                <Modal id="schedule-edit" className="schedule-edit" ref={(elm) => {
                    this.modal = elm
                }} events={{
                    'modal.shown': () => {
                        this.onModalShown();
                    },
                    'modal.hidden': () => {
                        this.onModalHidden();
                    }
                }}>
                    <Modal.Header>Chi tiết lịch khám</Modal.Header>
                    <Modal.Body>
                        <div className="row">
                            <div className="col-sm-3 patient-info">
                                <div className="card">
                                    <div className="card-body no-padding height-9">
                                        <div className="row">
                                            <div className="profile-userpic">
                                                <i className={"fa  fa-user-circle-o fa-3x"}></i>
                                            </div>
                                        </div>
                                        <div className="profile-usertitle">
                                            <div
                                                className="profile-usertitle-name">{form.patient.name} {form.patient.age}T
                                            </div>
                                            <div className="profile-usertitle-job">Bệnh nhân</div>
                                        </div>
                                        <ul className="list-group list-group-unbordered">
                                            <li className="list-group-item">
                                                <b>Điện thoại</b> <a
                                                className="pull-right desc-item">{form.patient.phone}</a>
                                            </li>
                                            <li className="list-group-item">
                                                <b>Địa chỉ</b> <a
                                                className="pull-right desc-item">{form.patient.addressText}</a>
                                            </li>
                                            <li className="list-group-item">
                                                <b>Số thẻ BHYT</b> <a
                                                className="pull-right desc-item">
                                                {(form.patient.healthInsurance.lenght > 0) ?
                                                    form.patient.healthInsurance
                                                    :
                                                    <span><i className={"fa fa-angle-left"}
                                                             style={{marginRight: '1px'}}></i>chưa điền<i
                                                        className={"fa fa-angle-right"} style={{marginLeft: '1px'}}></i></span>
                                                }
                                            </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div className="card">
                                    <div className={"card-head"}>
                                        <header>Thông tin đăng ký</header>
                                    </div>
                                    <div className="card-body no-padding height-9">
                                        <ul className="list-group list-group-unbordered">
                                            <li className="list-group-item">
                                                <b>Ngày yêu cầu</b> <a
                                                className="pull-right desc-item">{moment(form.reqDate).format("DD/MM/YYYY")},
                                                khoảng
                                                giờ {form.reqTimes}</a>
                                            </li>
                                            <li className="list-group-item">
                                                <b>Dịch vụ</b> <a className="pull-right desc-item">{
                                                form.reqService.dirs.map((dir, idx) => {
                                                    if (!idx) {
                                                        if (form.reqService.hasOwnProperty('reqHealthInsurance') && form.reqService.reqHealthInsurance.length) {
                                                            return <span
                                                                key={dir.id}>Khám bảo hiểm y tế &gt;</span>
                                                        } else
                                                            return <span
                                                                key={dir.id}>{dir.name} <i
                                                                className={"fa fa-angle-right"}></i> </span>
                                                    } else
                                                        return <span
                                                            key={dir.id}>{dir.name} <i
                                                            className={"fa fa-angle-right"}></i> </span>
                                                })
                                            }
                                                <span
                                                    className={"detail-service-name"}>{form.reqService.name}</span></a>
                                            </li>
                                            <li className="list-group-item">
                                                <b>Ghi chú</b> <a className="pull-right desc-item">{form.reqNote}</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div className="card">
                                    <div className={"card-head"}>
                                        <header>Thông tin thanh toán</header>
                                    </div>
                                    <div className="card-body no-padding height-9">
                                        <ul className="list-group list-group-unbordered">
                                            <li className="list-group-item">
                                                <b>Phí khám</b> <a className="pull-right desc-item">
                                                {App.getUser().hasPrivilege('TiepNhanBenhNhan') &&
                                                <div>
                                                    {form.paymentStatus != "free" &&
                                                    <label style={{
                                                        "display": "flex",
                                                        "alignItems": "center",
                                                        "marginBottom": "10px"
                                                    }}>
                                                        <input type="radio" checked={form.paymentStatus == 'unpaid'}
                                                               onChange={() => {
                                                               }}
                                                               onClick={() => {
                                                                   this.updatePaymentStatus('unpaid')
                                                               }}
                                                        />&nbsp;
                                                        Chưa thu
                                                    </label>
                                                    }
                                                    {form.paymentStatus != "free" &&
                                                    <label style={{
                                                        "display": "flex",
                                                        "alignItems": "center",
                                                        "marginBottom": "10px"
                                                    }}>
                                                        <input type="radio" checked={form.paymentStatus == 'paid'}
                                                               onChange={() => {
                                                               }}
                                                               onClick={() => {
                                                                   this.updatePaymentStatus('paid')
                                                               }}
                                                        />&nbsp;
                                                        Đã thu
                                                    </label>
                                                    }

                                                    {form.paymentStatus == "free" && <label>Không thu phí</label>}
                                                </div>}
                                                {App.getUser().hasPrivilege('TiepNhanBenhNhan') == false && <div>
                                                    {form.paymentStatus == "free" ? <label>Không thu phí</label> :
                                                        form.paymentStatus == 'unpaid' ? 'Chưa thu phí' : 'Đã thu phí'}
                                                </div>}
                                            </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div className="card">
                                    <div className={"card-head"}>
                                        <header>Tài liệu đính kèm</header>
                                    </div>
                                    <div className="card-body no-padding height-9">
                                        <div className={"patient-info-state"}>
                                            {form.files && form.files.map((file) =>
                                                <a className="btn-info btn btn-sm btn-circle" key={file.id}
                                                   style={{marginBottom: '3px', marginRight: '3px'}}
                                                   target="_blank"
                                                   href={App.url('/rest/teleclinic/schedule/file/:fileid', {'fileid': file.id})}>
                                                    {file.name}
                                                </a>)
                                            }
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="col-sm-9 patient-schedule">
                                <Tabs ref={(elm) => {
                                    this.TabControl = elm
                                }} className="tab-info center-tabs">
                                    <Tab id="tab-1" key="tab-user-basic" label="Xếp lịch">
                                        <div className="filter-list d-flex flex-row mt-3">
                                            <div className="" style={{minWidth: "200px", maxWidth: "220px"}}>
                                                <label htmlFor="searchReqDate1">Thời gian khám</label>
                                                <input id="searchReqDate1" type="datetime-local" className=""
                                                       disabled={!this.checkCanSchedule()}
                                                       style={{height: "30px"}}
                                                       value={this.state.defaultScheduleDatetime}
                                                       onChange={(ev) => {
                                                           this.setState({defaultScheduleDatetime: ev.target.value})
                                                       }}
                                                />
                                            </div>
                                            <div className="ml-3" style={{minWidth: "240px"}}>
                                                <label htmlFor="sel-chooseClinic">Xếp bệnh nhân vào phòng khám</label>
                                                <select className="form-control input-group" id="sel-chooseClinic"
                                                        disabled={!this.checkCanSchedule()}
                                                        value={this.state.chosenClinicID}
                                                        onChange={(ev) => this.chooseClinic(ev)}
                                                        style={{width: "270px"}}
                                                >
                                                    {
                                                        this.state.clinics.map(department =>
                                                            <optgroup key={department.id} label={department.name}>
                                                                {department.clinics.map(clinic =>
                                                                    <option key={clinic.id}
                                                                            value={clinic.id}>{clinic.name}</option>
                                                                )}
                                                            </optgroup>
                                                        )
                                                    }
                                                </select>
                                            </div>
                                            <div className="ml-3" style={{paddingTop: "21px"}}>
                                                <button className="btn btn-primary" disabled={!this.checkCanSchedule()}
                                                        onClick={(ev) => {
                                                            this.schedule(ev);
                                                            ev.currentTarget.blur();
                                                        }} style={{width: '150px'}}>Xếp lịch <i
                                                    className={"fa fa-file-text-o"}></i>
                                                </button>
                                                <button className="btn btn-danger" onClick={(ev) => {
                                                    this.cancelSchedule()
                                                }} style={{width: '150px'}}>Hủy yêu cầu <i
                                                    className={"fa fa-times"}></i>
                                                </button>
                                                <h4 className={"clear-margin"}></h4>
                                                <button className="btn btn-info" onClick={(ev) => {
                                                    this.sendSMS(SMS_SCHEDULE, moment(this.state.defaultScheduleDatetime).format("DD/MM/YYYY HH:mm"))
                                                }} style={{width: '150px'}}>Gửi SMS lịch khám <i
                                                    className={"fa fa-paper-plane-o"}></i>
                                                </button>
                                                {this.state.showSendSMSPayment > 0 &&
                                                <button className="btn btn-info" onClick={(ev) => {
                                                    this.sendSMSPayment(SMS_NOTICE_PAYMENT, moment(this.state.defaultScheduleDatetime).format("DD/MM/YYYY HH:mm"))
                                                }} style={{width: '150px'}}>Gửi SMS phí khám <i
                                                    className={"fa fa-paper-plane-o"}></i>
                                                </button>
                                                }
                                            </div>

                                        </div>
                                        <div className="clinic-500">
                                            <ClinicDayView date={this.state.defaultScheduleDatetime.substring(0, 10)}
                                                           clinic={this.state.chosenClinicID}
                                                           enableEdit={false}
                                                           ref={(elm) => {
                                                               this.ClinicDayView = elm
                                                           }}
                                            />
                                        </div>
                                    </Tab>
                                    {form && form["status"] != "unscheduled" && form["status"] != ["cancelled"] &&
                                    <Tab id="tab-2" key="tab-user-basic2" label="Chẩn đoán">

                                        <button className="btn btn-primary" onClick={(ev) => {
                                            this.diagnosis(ev)
                                        }}>{Lang.t("teleclinic.btnConfirmDiagnosis")} <i className={"fa fa-save"}></i>
                                        </button>
                                        <button className="btn btn-info" onClick={(ev) => {
                                            this.sendSMS(SMS_NOTICE_RESULT, form.scheduledDate)
                                        }}>Gửi SMS thông báo KQ <i className={"fa fa-paper-plane-o"}></i>
                                        </button>
                                        <form>
                                            <div className="form-group">
                                            <textarea className="form-control" id="diagDesc"
                                                      placeholder={Lang.t("schedule.diagDesc")}
                                                      rows="5"
                                                      value={!this.state.form.diagDesc ? "" : this.state.form.diagDesc}
                                                      onChange={(ev) => {
                                                          this.onChangeDiagnosisField(ev, "diagDesc");
                                                      }}
                                            />
                                            </div>
                                            <div className="form-group">
                                            <textarea className="form-control" id="diagConclusion"
                                                      placeholder={Lang.t("schedule.diagConclusion")}
                                                      rows="3"
                                                      value={!this.state.form.diagConclusion ? "" : this.state.form.diagConclusion}
                                                      onChange={(ev) => {
                                                          this.onChangeDiagnosisField(ev, "diagConclusion");
                                                      }}/>
                                            </div>
                                            <div className="form-group">
                                            <textarea className="form-control" id="diagRecommendation"
                                                      placeholder={Lang.t("schedule.diagRecommendation")}
                                                      rows="3"
                                                      value={!this.state.form.diagRecommendation ? "" : this.state.form.diagRecommendation}
                                                      onChange={(ev) => {
                                                          this.onChangeDiagnosisField(ev, "diagRecommendation");
                                                      }}/>
                                            </div>
                                            <div className="form-group">
                                                <input id="searchReExamDate" type="number" className="form-control"
                                                       style={{'width': 'calc(100% - 150px)', 'float': 'left'}}
                                                       placeholder={Lang.t("schedule.reExamDate")}
                                                       value={form.reExamDate}
                                                       onChange={(ev) => {
                                                           this.onChangeDiagnosisField(ev, "reExamDate");
                                                       }}/>
                                                <label style={{
                                                    'width': '150px',
                                                    'float': 'left',
                                                    'textAlign': 'right',
                                                    'lineHeight': '35px'
                                                }}>
                                                    <input type="checkbox" checked={form.createNextSchedule}
                                                           disabled={form.reExamDate == ''}
                                                           onChange={(ev) => {
                                                               this.onCreateNextSchedule(ev.target.checked)
                                                           }}/>&nbsp;
                                                    Tạo lịch tái khám
                                                </label>
                                                <div className="clearfix"></div>
                                            </div>
                                            <div className="form-group">
                                                <ContentEditable
                                                    html={this.state.form.diagPrescription ? this.state.form.diagPrescription : this.initPrescription()}
                                                    onChange={this.handleChangePrescription}
                                                />
                                            </div>
                                        </form>
                                    </Tab>}
                                </Tabs>
                            </div>
                        </div>
                    </Modal.Body>
                </Modal>
                <CancelScheduleModal ref={(elm) => {
                    this.cancelScheduleModal = elm
                }}/>
            </div>
        )
    }

}

class ContentEditable extends PureComponent {

    constructor(props) {
        super(props);
        this.ref = React.createRef();
    }

    render() {
        return <div
            style={{backgroundColor: "white"}}
            ref={this.ref}
            onInput={this.emitChange.bind(this)}
            onBlur={this.emitChange.bind(this)}
            contentEditable
            spellCheck={"false"}
            dangerouslySetInnerHTML={{__html: this.props.html}}/>;
    }

    shouldComponentUpdate(nextProps) {
        return nextProps.html !== this.ref.current.innerHTML;
    }

    emitChange() {
        let html = this.ref.current.innerHTML;
        if (this.props.onChange && html !== this.lastHtml) {

            this.props.onChange({
                target: {
                    value: html
                }
            });
        }
        this.lastHtml = html;
    }
}