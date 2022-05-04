window.clinicEditCompId = 0;

class VclinicEdit extends Component {
    constructor(props) {
        super(props);
        this.elmId = 'modal-clinic-edit-' + window.clinicEditCompId++;

        this.state = {
            'form': this.newClinic(),
            'weekDays': [
                {key: 1, text: 'Thứ 2'},
                {key: 2, text: 'Thứ 3'},
                {key: 3, text: 'Thứ 4'},
                {key: 4, text: 'Thứ 5'},
                {key: 5, text: 'Thứ 6'},
                {key: 6, text: 'Thứ 7'},
                {key: 0, text: 'Chủ nhật'}
            ]
        }

        this.clinicModel = new VclinicModel()
    }

    newClinic() {
        return {
            id: 0,
            name: '',
            siteID: App.siteID,
            depID: null,
            sort: 0,
            videoCall: {},
            department: {
                'name': ''
            },
            services: [],
            schedule: {},
            users: [],
            patientPerHour: 0
        }
    }

    static open(clinic) {
        VclinicEdit.getInstance().then((component) => {
            if (!clinic)
                clinic = component.newClinic()
            clinic = $.extend({}, clinic);
            clinic.videoCall = JSON.stringify(clinic.videoCall)
            if (!clinic.users)
                clinic.users = [];

            if (!clinic.patientPerHour || !clinic.hasOwnProperty('patientPerHour')) {
                clinic.patientPerHour = 0;
            }

            /* process schedule*/
            if (clinic.schedule) {
                var schedules = [];
                for (let key in clinic.schedule) {
                    var schedule = clinic.schedule[key];
                    if (!schedule || schedule == null || !schedule.length) {
                        schedules.push(["", ""]);
                        continue;
                    }

                    schedules.push(schedule);
                }

                clinic.schedule = schedules;
            }

            component.setState({'form': clinic});
            // get privilege
            component.modal.showModal();
        });

        return new Promise((done) => {
            VclinicEdit.instance.done = done || new Function;
        });
    }

    onModalShown() {
        this.tabs.setActive('info')
    }

    onModalHidden() {

    }

    handleSubmit(ev) {
        ev.preventDefault()
        //filter schedule
        if (this.state.form.schedule) {
            for (let weekDay in this.state.form.schedule) {
                let beginEnd = this.state.form.schedule[weekDay]
                //delete invalid day
                if (beginEnd.length != 2)
                    delete this.state.form.schedule[weekDay]
            }
        }

        this.clinicModel.save(this.state.form).then((resp) => {
            if (!resp.status) {
                alert(Lang.t('vclinic.updateError', {'message': JSON.stringify(resp.data)}))
                return
            }
            this.modal.hideModal();
            VclinicEdit.instance.done()
        }).catch((err) => {
            alert(err.toString())
            console.log(err)
        })
    }

    pickDept() {
        DepPicker.open().then((deps) => {
            if (!deps[0] || deps[0].id == 0)
                return

            this.state.form.department = deps[0]
            this.state.form.depID = deps[0].id
            this.setState({
                'form': this.state.form
            })
        })
    }

    openServicePicker() {
        this.servicePicker.open().then((services) => {
            //check trung
            $.each(services, (index, newService) => {
                for (let j in this.state.form.services) {
                    let oldService = this.state.form.services[j]
                    if (oldService.id == newService.id)
                        return
                }
                this.state.form.services.push(newService)
            })
            this.setState({form: this.state.form})
        })
    }

    removeService(serviceID) {
        for (let i in this.state.form.services) {
            let service = this.state.form.services[i]
            if (service.id == serviceID) {
                this.state.form.services.splice(i, 1)
                this.setState({form: this.state.form})
                break
            }
        }
    }

    onScheduleChange(weekDay, value, type) {
        if (!this.state.form.schedule)
            this.state.form.schedule = {}
        if (!this.state.form.schedule[weekDay])
            this.state.form.schedule[weekDay] = [value]
        if (type == 'end' && this.state.form.schedule[weekDay].length == 1)
            this.state.form.schedule[weekDay].push(value)
        if (type == 'begin')
            this.state.form.schedule[weekDay][0] = value
        else if (type == 'end')
            this.state.form.schedule[weekDay][1] = value
        this.setState({form: this.state.form})
    }

    getScheduleDay(weekDay, type) {
        if (!this.state.form.schedule || !this.state.form.schedule[weekDay])
            return 0;

        if (type == 'begin') {
            if (!this.state.form.schedule[weekDay][0])
                return 0;
            return this.state.form.schedule[weekDay][0];
        } else if (type == 'end') {
            if (!this.state.form.schedule[weekDay][1])
                return 0;
            return this.state.form.schedule[weekDay][1];
        }
    }

    pickUser() {
        UserPicker.open({type: ['user']}).then((users) => {
            console.log(users)
            if (!users.length)
                return
            $.each(users, (index, newUser) => {
                for (var j in this.state.form.users) {
                    var addedUser = this.state.form.users[j]
                    if (addedUser.id == newUser.id) //skipp added user
                        return
                }
                this.state.form.users.push(newUser)
            })
            this.setState({form: this.state.form})
        })
    }

    removeUser(id) {
        for (let i in this.state.form.users) {
            let user = this.state.form.users[i]
            if (user.id == id) {
                this.state.form.users.splice(i, 1)
                this.setState({form: this.state.form})
                return
            }
        }
    }


    render() {
        return (
            <form onSubmit={(ev) => {
                this.handleSubmit(ev);
            }}
                  ref={(elm) => {
                      this.form = elm;
                  }}
            >
                <Modal
                    id="modal-clinic-edit"
                    ref={(elm) => {
                        this.modal = elm;
                    }}
                    events={{
                        'modal.shown': () => {
                            this.onModalShown();
                        },
                        'modal.hidden': () => {
                            this.onModalHidden();
                        }
                    }}>
                    <Modal.Header>Phòng khám</Modal.Header>
                    <Modal.Body>
                        <div className="container">
                            <Tabs preRender={true} ref={(el) => {
                                this.tabs = el
                            }}>
                                <Tab label="Thông tin chung" id="info" key="info">
                                    <div className="form-group row">
                                        <label className="control-label">Tên</label>
                                        <input type="text" className="form-control" required
                                               value={this.state.form.name}
                                               onChange={(ev) => {
                                                   this.state.form.name = ev.target.value;
                                                   this.setState({form: this.state.form});
                                               }}/>
                                    </div>
                                    <div className="form-group row">
                                        <label className="control-label">Khoa/Phòng</label>
                                        <input type="text" readOnly="readOnly" className="form-control" required
                                               value={this.state.form.department.name}
                                               onClick={() => {
                                                   this.pickDept()
                                               }}/>
                                    </div>
                                    <div className="form-group row">
                                        <label className="control-label">Số bệnh nhân tối đa/giờ</label>
                                        <input type="number" className="form-control"
                                               value={this.state.form.patientPerHour}
                                               onChange={(ev) => {
                                                   this.state.form.patientPerHour = ev.target.value;
                                                   this.setState({form: this.state.form});
                                               }}/>
                                    </div>
                                    <div className="form-group row">
                                        <label className="control-label">Sắp xếp</label>
                                        <input type="number" className="form-control" value={this.state.form.sort}
                                               onChange={(ev) => {
                                                   this.state.form.sort = ev.target.value;
                                                   this.setState({form: this.state.form});
                                               }}/>
                                    </div>
                                    <div className="form-group row">
                                        <label className="control-label">Video call</label>
                                        <textarea className="form-control" value={this.state.form.videoCall} rows="5"
                                                  onChange={(ev) => {
                                                      this.state.form.videoCall = ev.target.value;
                                                      this.setState({form: this.state.form});
                                                  }}/>
                                    </div>
                                </Tab>
                                <Tab label="Dịch vụ liên kết" id="service" key="service">
                                    <h4>&nbsp;</h4>
                                    <div className="form-group row">
                                        <a href="javascript:;" onClick={() => {
                                            this.openServicePicker()
                                        }} className={"btn btn-info"}>Chọn dịch vụ <i className={"fa fa-plus"}></i></a>
                                        <div className={"service-link"} style={{'width' : '100%'}}>
                                            <span className="control-label" style={{color : "#3a405b", fontSize: "14px", fontWeight : "600"}}>Danh sách dịch vụ liên kết</span>
                                        </div>
                                        <table className="table table-bordered table-condensed">
                                            <tbody>
                                            {this.state.form.services.map((service) => <tr key={service.id}>
                                                <td style={{width: '100%', 'verticalAlign' : 'top'}}
                                                    className={parseInt(service.deleted) ? 'deleted-service' : ''}>{service.displayName}</td>

                                                <td style={{minWidth: '50px','verticalAlign' : 'top'}}>
                                                    <a href="javascript:;" onClick={() => {
                                                        this.removeService(service.id)
                                                    }} className={"btn btn-danger btn-xs"}><i className={"fa fa-trash-o"}></i></a>
                                                </td>
                                            </tr>)}
                                            </tbody>
                                        </table>
                                    </div>
                                </Tab>
                                <Tab label="Lịch trực" id="schedule" key="schedule">
                                    <table className="table table-bordered table-condensed">
                                        <tbody>
                                        <tr>
                                            <th style={{minWidth: '100px'}}></th>
                                            <th>Giờ bắt đầu</th>
                                            <th>Giờ kết thúc</th>
                                        </tr>
                                        {this.state.weekDays.map((weekDay) => <tr key={weekDay.key}>
                                            <td>{weekDay.text}</td>
                                            <td>
                                                <input type="number"
                                                       onChange={(ev) => {
                                                           this.onScheduleChange(weekDay.key, ev.target.value, 'begin')
                                                       }}
                                                       value={this.getScheduleDay(weekDay.key, 'begin')}
                                                />
                                            </td>
                                            <td>
                                                <input type="number"
                                                       disabled={this.getScheduleDay(weekDay.key, 'begin') == ''}
                                                       onChange={(ev) => {
                                                           this.onScheduleChange(weekDay.key, ev.target.value, 'end')
                                                       }}
                                                       value={this.getScheduleDay(weekDay.key, 'end')}
                                                />
                                            </td>
                                        </tr>)}
                                        </tbody>
                                    </table>

                                </Tab>
                                <Tab label="Quyền truy cập" id="acl" key="acl">
                                    <h4></h4>
                                    <button type="button" className="btn btn-default" onClick={() => {
                                        this.pickUser()
                                    }}>Chọn tài khoản để phân quyền
                                    </button>
                                    <table className="table table-bordered table-condensed">
                                        <tbody>
                                        {this.state.form.users.map((user) => <tr key={user.id}>
                                            <td style={{width: '100%','verticalAlign' : 'top'}}>{user.fullname}</td>
                                            <td style={{minWidth: '50px','verticalAlign' : 'top'}}>
                                                <a href="javascript:;" onClick={() => {
                                                    this.removeUser(user.id)
                                                }} className={"btn btn-danger btn-xs"}><i className={"fa fa-trash-o"}></i></a>
                                            </td>
                                        </tr>)}
                                        </tbody>
                                    </table>
                                </Tab>
                            </Tabs>
                        </div>
                    </Modal.Body>
                    <Modal.Footer>
                        <button type="button" className="btn btn-secondary" data-dismiss="modal">Hủy bỏ</button>
                        <button type="submit" className="btn btn-primary">Ghi lại</button>
                    </Modal.Footer>
                </Modal>
                <TeleclinicServiceList modal={{multiple: true}} ref={(el) => {
                    this.servicePicker = el;
                }}/>
            </form>
        );
    }
}