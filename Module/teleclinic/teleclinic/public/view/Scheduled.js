class Scheduled extends Component {
    constructor(props) {
        super(props);

        this.state = {
            'clinics': [],
            'filterClinic': '',
            'clinicCount': 0,
            'selectedClinic': {},
            'filterSchedule': {
                'siteID': App.siteID,
                'scheduledDate': new Date().toISOString().substring(0, 10),
                'clinicID': 0
            },
            'filterStatus': {
                'scheduled': true,
                'completed': true
            },
            'hours': [],
            'clinicDashboard': {},
            //khung giờ pk có thể khám
            'clinicOnDuty': {}
        }

        this.clinicModel = new VclinicModel()
        this.scheduleModel = new ScheduleModel()

        for (let i = 7; i < 17; i++) {
            if (i == 12)
                continue
            this.state.hours.push(i)
        }
    }

    componentDidMount() {
        App.requireLogin();
        App.Component.trigger('leftNav.show', true)

        //load clinics
        this.clinicModel.getClinics({
            'siteID': App.siteID,
            'groupBy': 'depID'
        }).then((resp) => {
            let newState = {'clinics': resp}
            this.setState(newState, () => {
                this.getReport()
            })
        })


    }

    componentWillMount() {
        App.Component.trigger('leftNav.active', 'Scheduled');
    }

    getReport() {
        this.state.clinicDashboard = {}; //reset
        this.state.clinicOnDuty = {};
        var numAjax = 0
        $.each(this.state.clinics, (index, dept) => {
            if (!dept.id)
                return
            $.each(dept.clinics, (index, clinic) => {
                if (!clinic.id)
                    return
                numAjax++
                this.scheduleModel.getClinicScheduleSummaries(clinic.id, this.state.filterSchedule.scheduledDate).then((resp) => {
                    numAjax--
                    this.state.clinicDashboard[clinic.id] = resp
                    if (numAjax == 0) {
                        this.setState({'clinicDashboard': this.state.clinicDashboard})
                    }
                })
            })
        })

    }

    setSelectedClinic(clinic) {
        this.state.filterSchedule.clinicID = clinic.id
        this.setState({
            'selectedClinic': clinic,
            'filterSchedule': this.state.filterSchedule
        })
        if (clinic.id == 0) {
            //man hinh tong hop
        } else {
            //xem danh sach cua phong kham
        }
    }

    openVideoCall() {
        if (!this.state.selectedClinic.id)
            return
        window.open(this.state.selectedClinic.videoCall.hostURL)
    }

    changeFilterDate(numDateChange) {
        let d = new Date(this.state.filterSchedule.scheduledDate)
        d.setDate(d.getDate() + numDateChange)
        this.state.filterSchedule.scheduledDate = d.toISOString().substr(0, 10)
        this.setState({filterSchedule: this.state.filterSchedule})

        if (this.state.filterSchedule.clinicID == 0)
            this.getReport()
    }

    renderClinicDetails() {
        return (<div className={"card"}>
            <div className={"card-head"}></div>
            <div className={"card-body"}>
                <div className="filter">
                    <input type="date" value={this.state.filterSchedule.scheduledDate} onChange={(ev) => {
                        this.state.filterSchedule.scheduledDate = ev.target.value
                        this.setState({'filterSchedule': this.state.filterSchedule})
                    }}/>
                    <button className="btn btn-primary" onClick={() => {
                        this.changeFilterDate(-1)
                    }}><i className="fa fa-chevron-left"/></button>
                    <button className="btn btn-primary" onClick={() => {
                        this.changeFilterDate(1)
                    }}><i className="fa fa-chevron-right"/></button>
                    <button className="btn btn-info" onClick={() => {
                        this.ClinicDayView.getSchedules()
                    }}><i className="fa fa-refresh"/></button>
                    <div style={{"flex": "1"}}>
                        <button className="btn btn-primary clear-margin pull-right" onClick={() => {
                            this.openVideoCall()
                        }}>Bắt đầu gọi Video <i className={"fa fa-video-camera"}></i>
                        </button>
                    </div>

                </div>
                <div className={"table-scrollable"}>
                    <div className="calendar">
                        <ClinicDayView date={this.state.filterSchedule.scheduledDate}
                                       clinic={this.state.filterSchedule.clinicID}
                                       ref={(elm) => {
                                           this.ClinicDayView = elm
                                       }}
                        />
                    </div>
                </div>
            </div>
        </div>)
    }

    isEnabledDutySchedule(clinicID, hour) {
        if (typeof this.state.clinicOnDuty[clinicID] == 'undefined' || typeof this.state.clinicOnDuty[clinicID].schedule == 'undefined')
            return true
        if (typeof this.state.clinicOnDuty[clinicID].schedule[hour] == 'undefined')
            return true
        return this.state.clinicOnDuty[clinicID].schedule[hour] ? true : false
    }

    renderDashboard() {
        return (<div className={"card"}>
            <div className={"card-head"}></div>
            <div className={"card-body"}>
                <div className="filter">
                    <input type="date" value={this.state.filterSchedule.scheduledDate} onChange={(ev) => {
                        this.state.filterSchedule.scheduledDate = ev.target.value
                        this.setState({'filterSchedule': this.state.filterSchedule})
                        this.getReport()
                    }}/>
                    <button className="btn btn-primary" onClick={() => {
                        this.changeFilterDate(-1)
                    }}><i className="fa fa-chevron-left"/></button>
                    <button className="btn btn-primary" onClick={() => {
                        this.changeFilterDate(1)
                    }}><i className="fa fa-chevron-right"/></button>
                    <button className="btn btn-info" onClick={() => {
                        this.getReport()
                    }}><i className="fa fa-refresh"/></button>
                    &nbsp;&nbsp;&nbsp;
                    <label style={{"color": "brown", "fontWeight": "bold"}}>
                        <input type="checkbox" checked={this.state.filterStatus.scheduled} onChange={(ev) => {
                            this.state.filterStatus.scheduled = ev.target.checked
                            this.setState({filterStatus: this.state.filterStatus})
                        }}/>
                        Chưa khám
                    </label>
                    &nbsp;&nbsp;&nbsp;
                    <label style={{"color": "green", "fontWeight": "bold"}}>
                        <input type="checkbox" checked={this.state.filterStatus.completed} onChange={(ev) => {
                            this.state.filterStatus.completed = ev.target.checked
                            this.setState({filterStatus: this.state.filterStatus})
                        }}/>
                        Đã khám
                    </label>
                </div>
                <div className={"table-scrollable"}>
                    <div className="dashboard container">
                        <div className={"tbl-fixed-header"}>
                            <table className="table table-bordered table-groups">
                                <thead>
                                <tr>
                                    <th style={{'width': '100%', 'textAlign': 'left'}}>Phòng khám</th>
                                    <th style={{minWidth: '100px'}}>SL tối đa/giờ</th>
                                    {this.state.hours.map((hour) => <th style={{'minWidth': '80px'}}
                                                                        key={hour}>{hour}h</th>)}
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <div className={"tbl-fixed-body"}>
                            <table className="table table-bordered table-groups">
                                {this.state.clinics.map((department) =>
                                    <tbody key={department.id}>
                                    <tr className={"tbl-group"}>
                                        <td className="" colSpan={11}
                                            dangerouslySetInnerHTML={{__html: department.name}}></td>
                                    </tr>
                                    {department.clinics.map((clinic) => <tr key={clinic.id} className={"tbl-row"}>
                                        <td style={{'width': '100%', 'textAlign': 'left'}}>{clinic.name}</td>
                                        <td style={{textAlign: 'center',minWidth: '100px'}}>{clinic.patientPerHour}</td>

                                        {this.state.hours.map((hour) => <td
                                            style={{'minWidth': '80px', 'textAlign': 'center'}}
                                            key={hour}>
                                            {this.state.clinicDashboard[clinic.id] && this.state.clinicDashboard[clinic.id][hour] &&
                                            <div>
                                                {this.state.filterStatus.scheduled &&
                                                <label style={{'fontWeight': 'bold', 'color': 'brown'}}>
                                                    {this.state.clinicDashboard[clinic.id][hour].scheduled || 0}
                                                </label>}
                                                {this.state.filterStatus.scheduled && this.state.filterStatus.completed &&
                                                <span>,&nbsp;</span>}
                                                {this.state.filterStatus.completed &&
                                                <label style={{'fontWeight': 'bold', 'color': 'green'}}>
                                                    {this.state.clinicDashboard[clinic.id][hour].completed || 0}
                                                </label>}
                                            </div>}
                                        </td>)}
                                    </tr>)}
                                    </tbody>)}
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>)
    }

    render() {
        return (
            <AdminLayout>
                <div className="main vclinic scheduled">
                    <div className="row" style={{height: '100%'}}>
                        <div className="col-sm-2 border overflow-x-auto h-100 pl-0 pr-0 break-line theme-light left-col"
                             style={{position: 'relative'}}>
                            <ul className="schedule-nav" style={{'minHeight': 'calc(100% - 34px)'}}>
                                <li>
                                    <a className={"dep-title " + (this.state.filterSchedule.clinicID == 0 ? 'dep-title-active' : '')}
                                       onClick={() => {
                                           this.setSelectedClinic({id: 0})
                                       }}
                                    ><i className={"fa fa-bar-chart"}
                                        style={{"marginRight": '6px', fontSize: '16px'}}></i> Màn hình
                                        tổng hợp
                                    </a>
                                </li>
                                {this.state.clinics.filter((department) => {
                                    for (var i in department.clinics) {
                                        if (department.clinics[i].name.toLowerCase().match(this.state.filterClinic))
                                            return true
                                    }
                                    return false
                                }).map((department) => <li key={department.id}>
                                        <a className="disabled"><i className={"fa fa-building-o"}
                                                                   style={{"marginRight": '14px'}}></i>{department.name}
                                        </a>
                                        <ul className="child-nav">
                                            {department.clinics.filter((clinic) => clinic.name.toLowerCase().match(this.state.filterClinic))
                                                .map((clinic) => <li className="d-block" key={clinic.id}>
                                                    <a className={"dep-title pl-5 " + (this.state.filterSchedule.clinicID == clinic.id ? 'dep-title-active' : '')}
                                                       onClick={() => {
                                                           this.setSelectedClinic(clinic)
                                                       }}>
                                                        <i className={"fa fa-user-md"}
                                                           style={{"marginRight": '14px'}}></i> {clinic.name}
                                                    </a>
                                                </li>)}
                                        </ul>
                                    </li>
                                )}
                                <li>&nbsp;</li>
                            </ul>
                            <div style={{
                                'position': 'sticky', 'lineHeight': '30px',
                                'bottom': '2px', 'left': 0
                            }}>
                                <div className="input-group">
                                    <div className="input-group-prepend">
                                        <span className="input-group-text"
                                              style={{
                                                  borderTop: 0,
                                                  borderLeft: 0,
                                                  borderRight: 0,
                                                  borderBottom: '2px solid #5FA3ED',
                                                  color: '#007bff'
                                              }}><i className="ti-search"></i></span>
                                    </div>
                                    <input type="text" placeholder="Tìm tên phòng khám.." className="search"
                                           style={{
                                               flex: "1", borderTop: 0,
                                               borderLeft: 0,
                                               borderRight: 0,
                                               borderBottom: '2px solid #5FA3ED'
                                           }}
                                           value={this.state.filterClinic}
                                           onChange={(ev) => {
                                               this.setState({filterClinic: ev.target.value.toLowerCase()})
                                           }}
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="col-sm-10  h-100 pl-0 pr-0 break-line theme-light right-col">
                            <div className="page-bar" style={{padding: '0 21px'}}>
                                <div className="page-title-breadcrumb">
                                    <div className=" pull-left">
                                        <div className="page-title">
                                            {this.state.filterSchedule.clinicID == 0 && 'Màn hình tổng hợp'}
                                            {this.state.filterSchedule.clinicID != 0 && this.state.selectedClinic.name}
                                        </div>
                                    </div>
                                    <ol className="breadcrumb page-breadcrumb pull-right">
                                        <li>
                                            <i className="fa fa-home"></i>
                                            <a className="parent-item" href={App.url('/:siteID/teleclinic/scheduled', {siteID: App.siteID})}>Đã xếp lịch</a>
                                            <i className="fa fa-angle-right"></i>
                                        </li>
                                        <li>
                                            {this.state.filterSchedule.clinicID == 0 && 'Màn hình tổng hợp'}
                                            {this.state.filterSchedule.clinicID != 0 && this.state.selectedClinic.name}
                                        </li>
                                    </ol>
                                </div>
                            </div>

                            {this.state.filterSchedule.clinicID == 0 && this.renderDashboard()}
                            {this.state.filterSchedule.clinicID != 0 && this.renderClinicDetails()}
                        </div>

                    </div>

                </div>
            </AdminLayout>
        );
    }
}