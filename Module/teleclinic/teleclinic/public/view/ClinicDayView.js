class ClinicDayView extends Component {
    constructor(props) {
        super(props)
        this.state = {
            'filter': {
                'siteID': App.siteID,
                'clinicID': this.props.clinic,
                'scheduledDate': this.props.date,
                'order': 'scheduledDate'
            },
            'schedules': {},
            'hours': [],
            'clinic': {}
        }
        for(let i=7; i<18;i++)
            this.state.hours.push(i)

        this.scheduleModel = new ScheduleModel()
    }



    getSchedules() {
        for(let i in this.state.filter)
            if(!this.state.filter[i])
                return
        this.scheduleModel.getSchedule(this.state.filter).then((schedules) => {
            //group schedule by hour
            this.state.schedules = {}
            for(let i in schedules) {
                let schedule = schedules[i]
                if(!schedule.id)
                    continue
                //chỉ quyền tiếp nhận mới được xem chưa xếp lịch
                if(schedule.status == 'unscheduled' && App.getUser().hasPrivilege('TiepNhanBenhNhan') == false)
                    continue
                let hour = new Date(schedule.scheduledDate).getHours()
                if(!this.state.schedules[hour])
                    this.state.schedules[hour] = []
                this.state.schedules[hour].push(schedule)
            }

            this.setState({'hours': this.state.hours})
        })

        new VclinicModel().getClinic(this.state.filter.clinicID).then((clinic)=> {
            this.setState({'clinic': clinic})
        })
    }

    isEmpty(obj) {
        for(let i in obj)
            return false
        return true
    }

    isOnDuty(hour) {
        let weekDay = new Date(this.state.filter.scheduledDate).getDay()
        if(this.isEmpty(this.state.clinic.schedule))
            return true
        if(!this.state.clinic.schedule[weekDay])
            return false
        let begin = this.state.clinic.schedule[weekDay][0]
        let end = this.state.clinic.schedule[weekDay][1]
        if(hour >= begin && hour <= end)
            return true
        else
            return false
    }

    componentDidMount() {
        this.getSchedules()
    }

    componentWillReceiveProps(nextProps) {
        this.state.filter.clinicID = nextProps.clinic
        this.state.filter.scheduledDate = nextProps.date
        this.getSchedules()

    }

    openSchedule(schedule) {
        if(typeof this.props.enableEdit != 'undefined' && !this.props.enableEdit)
            return
        this.scheduleEdit.open(schedule).then(() => {
            this.getSchedules()
        })
    }

    getHourClass(hour) {
        let className = this.isOnDuty(hour) ? 'enabled' : 'disabled'
        if(this.state.schedules[hour] && this.state.clinic.patientPerHour > 0 && this.state.schedules[hour].length >= this.state.clinic.patientPerHour)
            className += ' overload'
        return className
    }

    render() {
        return (<div className="ClinicDayView">
            <div style={{"overflow": "hidden", "margin": "10px 0", "display" :"flex", "alignItems" : "center"}}>
                    <div style={{'float': 'left'}}>Trạng thái:&nbsp;</div>
                    <div className="btn schedule-obj unscheduled" style={{width: 'auto', 'padding': '4px 8px'}}>Chưa xếp lịch</div>
                    <div className="btn schedule-obj scheduled" style={{width: 'auto', 'padding': '4px 8px'}}>Đã xếp lịch</div>
                    {(App.getUser().hasPrivilege('TiepNhanBenhNhan') || App.getUser().hasPrivilege('KhamChoBenhNhan') ) &&
                        <div className="btn schedule-obj completed" style={{width: 'auto', 'padding': '4px 8px'}}>Đã khám</div>}
            </div>

            <table className="table table-bordered">
                <tbody>
                {this.state.hours.map((hour)=><tr key={hour}  className={this.getHourClass(hour)}>
                    <th>{hour}h</th>
                    <td>
                        {this.state.schedules[hour] && this.state.schedules[hour]
                            .filter((schedule) => {
                                if(schedule.status == 'cancelled')
                                    return false
                                return true
                            })
                            .map((schedule) =>
                            <div className={"schedule-obj "  + schedule.status} key={schedule.id} onClick={() => {this.openSchedule(schedule)}}>
                                <div className="left">
                                    {schedule.patient.name} {schedule.patient.age}T
                                </div>
                                <div className="right">
                                    {new Date(schedule.scheduledDate).getHours()}:{new Date(schedule.scheduledDate).getMinutes()}
                                </div>
                            </div>)}
                    </td>
                </tr>)}
                </tbody>
            </table>
            <ScheduleEdit ref={(elm)=> { this.scheduleEdit = elm; }}/>
        </div>)
    }
}