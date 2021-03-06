class BaoCaoTongHopDichVu extends Component{
    constructor(props) {
        super(props);

        this.state = {
            filter: {
                from: new Date().toISOString().substr(0, 10),
                to: new Date().toISOString().substr(0, 10)
            },
            showReport: false
        }

        new VclinicModel().getClinics({siteID: App.siteID, groupBy: 'depID'}).then((clinics) => {
            this.state.clinics = clinics
        })
    }

    showReport() {
        this.state.countSchedules = {
            'scheduled': 0,
            'completed': 0,
            'cancelled': 0,
            'total': 0
        }
        this.setState({showReport: false})
        $.each(this.state.clinics, (index, department) => {
            department.countSchedules = {
                'scheduled': 0,
                'completed': 0,
                'cancelled': 0,
                'total': 0
            }
            department.index = index + 1
            $.each(department.clinics, (index, clinic) => {
                clinic.countSchedules = {
                    'scheduled': 0,
                    'completed': 0,
                    'cancelled': 0,
                    'total': 0
                }
                clinic.index = department.index + '.' + (index + 1)
                new ScheduleModel().getSchedule({
                    siteID: App.siteID,
                    clinicID: clinic.id,
                    status: ['completed', 'scheduled', 'cancelled'],
                    scheduledDate: [
                        this.state.filter.from,
                        this.state.filter.to
                    ]
                }).then((schedules) => {
                    schedules.map((schedule) => {
                        this.state.countSchedules[schedule.status]++;
                        this.state.countSchedules.total++;
                        department.countSchedules[schedule.status]++;
                        department.countSchedules.total++;
                        clinic.countSchedules[schedule.status]++;
                        clinic.countSchedules.total++;
                    })

                    //tinh toan
                    this.setState({
                        clinics: this.state.clinics,
                        showReport: true,
                        countSchedules: this.state.countSchedules
                    })
                })
            })
        })

    }

    render() {
        return (<div className={"card"}>
            <div className="filter">
                T??? <input type="date"
                          value={this.state.filter.from}
                          onChange={(ev)=>{this.state.filter.from = ev.target.value; this.setState({filter: this.state.filter})}}/>&nbsp;
                ?????n <input type="date" value={this.state.filter.to}
                           onChange={(ev)=>{this.state.filter.to = ev.target.value; this.setState({filter: this.state.filter})}}/>
                <button className="btn btn-default" onClick={(ev) => {this.showReport();ev.currentTarget.blur();}}>Xu???t b??o c??o <i className={"fa fa-file-text-o"}></i></button>
            </div>
            <div className={"results"}>
                {this.state.showReport && <table className="table table-bordered table-groups">
                    <thead>
                    <tr>
                        <th style={{minWidth: '10%'}}>STT</th>
                        <th style={{minWidth: '35%'}}>T??n</th>
                        <th style={{minWidth: '15%'}}>???? x???p l???ch</th>
                        <th style={{minWidth: '15%'}}>???? kh??m</th>
                        <th style={{minWidth: '15%'}}>H???y kh??m</th>
                        <th style={{minWidth: '15%'}}>T???ng s??? d???ch v???</th>
                    </tr>
                    </thead>
                    {this.state.clinics.map((dept) => <tbody key={dept.id}>
                    <tr className={"tbl-group"}>
                        <td>{dept.index}</td>
                        <td>{dept.name}</td>
                        <td>{dept.countSchedules.scheduled}</td>
                        <td>{dept.countSchedules.completed}</td>
                        <td>{dept.countSchedules.cancelled}</td>
                        <td>{dept.countSchedules.total}</td>
                    </tr>
                    {dept.clinics.map((clinic)=> <tr key={clinic.id} className={"tbl-row"}>
                        <td>{clinic.index}</td>
                        <td>{clinic.name}</td>
                        <td>{clinic.countSchedules.scheduled}</td>
                        <td>{clinic.countSchedules.completed}</td>
                        <td>{clinic.countSchedules.cancelled}</td>
                        <td>{clinic.countSchedules.total}</td>
                    </tr>)}
                    </tbody>)}
                    <tbody>
                        <tr>
                            <td></td>
                            <td><b>T???NG</b></td>
                            <td><b>{this.state.countSchedules.scheduled}</b></td>
                            <td><b>{this.state.countSchedules.completed}</b></td>
                            <td><b>{this.state.countSchedules.cancelled}</b></td>
                            <td><b>{this.state.countSchedules.total}</b></td>
                        </tr>
                    </tbody>
                </table>}
            </div>
        </div>)
    }
}