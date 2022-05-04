class BaoCaoChiTietPhongKham extends Component {
    constructor(props) {
        super(props);

        this.state = {
            filter: {
                from: new Date().toISOString().substr(0, 10),
                to: new Date().toISOString().substr(0, 10),
                clinicID: 0,
                status: ['completed']
            },
            'clinics': [],
            'schedules': {},
            'scheduleDates': [],
            'showReport': false,
            'totalSchedules': 0
        }
    }

    componentDidMount() {
        new VclinicModel().getClinics({
            siteID: App.siteID,
            groupBy: 'depID'
        }).then((clinics) => {
            this.setState({clinics: clinics})
        })
    }

    showReport() {
        if (this.state.filter.clinicID == 0) {
            alert("Vui lòng chọn phòng khám")
            return
        }

        new ScheduleModel().getSchedule(this.state.filter).then((schedules) => {
            this.state.schedules = {};
            this.state.totalSchedules = 0;
            this.state.scheduleDates = [];
            for (let i in schedules) {
                let schedule = schedules[i]
                if (!schedule.id)
                    continue
                let date = new Date(schedule.scheduledDate).toISOString().substr(0, 10)
                if (!this.state.schedules[date])
                    this.state.schedules[date] = []
                this.state.schedules[date].push(schedule)
                if (!this.state.scheduleDates.includes(date)) {
                    this.state.scheduleDates.push(date)
                }
            }

            this.setState({
                'scheduleDates': this.state.scheduleDates,
                'totalSchedules': schedules.length
            })
        })
    }

    renderPatientBirthDate(patient) {
        if (patient.hasOwnProperty('birthDate'))
            return patient.birthDate;

        if (!patient.hasOwnProperty('age'))
            return '';

        var now = new Date();
        return now.getFullYear() - parseInt(patient.age);
    }

    render() {
        return (<div className={"card"}>
            <div className="filter">
                <select onChange={(ev) => {
                    this.state.filter.clinicID = ev.target.value;
                    this.setState({filter: this.state.filter})
                }}>
                    <option value="0">-- Chọn phòng khám --</option>
                    {this.state.clinics.map((department) => <optgroup label={department.name} key={department.id}>
                        {department.clinics.map((clinic) => <option value={clinic.id}
                                                                    key={clinic.id}>{clinic.name}</option>)}
                    </optgroup>)}
                </select>&nbsp;
                Từ <input type="date"
                          value={this.state.filter.from}
                          onChange={(ev) => {
                              this.state.filter.from = ev.target.value;
                              this.setState({filter: this.state.filter});
                          }}/>&nbsp;
                Đến <input type="date" value={this.state.filter.to}
                           onChange={(ev) => {
                               this.state.filter.to = ev.target.value;
                               this.setState({filter: this.state.filter});
                           }}/>
                <button className="btn btn-default" onClick={(ev) => {
                    this.showReport();
                    ev.currentTarget.blur();
                }}>Xuất báo cáo <i className={"fa fa-file-text-o"}></i></button>
            </div>
            <div className={"results"}>
                <span style={{'display': 'none'}}>{window.i = 1}</span>
                <table className="table table-bordered table-groups">
                    <thead>
                    <tr>
                        <th style={{width: '10%'}}>TT</th>
                        <th style={{width: '20%'}}>Tên bệnh nhân</th>
                        <th style={{width: '10%'}}>Ngày sinh</th>
                        <th style={{width: '40%'}}>Dịch vụ đăng ký</th>
                        <th style={{width: '20%'}}>Ngày khám</th>
                    </tr>
                    </thead>
                    {this.state.scheduleDates.map((date) => <tbody key={date}>
                    <tr className={"tbl-group"}>
                        <td colSpan="5">Ngày {new Date(date).toLocaleDateString()}</td>
                    </tr>
                    {this.state.schedules[date].map((schedule) => <tr key={schedule.id} className={"tbl-row"}>
                        <td>{window.i++}</td>
                        <td>{schedule.patient.name}</td>
                        <td>{this.renderPatientBirthDate(schedule.patient)}</td>
                        <td>{schedule.reqService.name}</td>
                        <td>{new Date(schedule.scheduledDate).toLocaleDateString() + ' ' + new Date(schedule.scheduledDate).toLocaleTimeString()}</td>
                    </tr>)}
                    </tbody>)}
                </table>
            </div>
            {this.state.showReport &&
            <div className="container"><b>Tổng số lượt khám: {this.state.totalSchedules}</b></div>}
        </div>)
    }
}