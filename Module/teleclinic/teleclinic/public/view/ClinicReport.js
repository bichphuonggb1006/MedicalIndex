class ClinicReport extends Component {
    constructor(props) {
        super(props)

        this.state = {
            'reports': [
                {'name': 'Báo cáo tổng hợp dịch vụ', 'component': BaoCaoTongHopDichVu},
                {'name': 'Báo cáo chi tiết phòng khám', 'component': BaoCaoChiTietPhongKham}
            ]
        }
        this.state.selectedReport = this.state.reports[0]
    }

    componentWillMount() {
        App.Component.trigger('leftNav.active', 'Report');
    }

    componentDidMount() {
        App.requireLogin();
        App.Component.trigger('leftNav.show', true)
    }

    render() {
        return (<AdminLayout>
            <div className="page-report">
                <div className="row" style={{height: '100%'}}>
                    <div className="col-sm-2 border  h-100 overflow-x-auto pl-0 pr-0 break-line theme-light sub-menu-left">
                        <ul className="schedule-nav">
                            {this.state.reports.map((report) => <li key={report.name}>
                                <a className={"dep-title " + (report.name == this.state.selectedReport.name ? 'dep-title-active' : '')}
                                   onClick={() => {
                                       this.setState({selectedReport: report})
                                   }}><i className={"fa fa-file"} style={{"marginRight": '14px'}}></i>{report.name}
                                </a>
                            </li>)}
                        </ul>
                    </div>
                    <div className="col-sm-10  h-100 pl-0 pr-0 break-line theme-light right-col report-content">
                        {React.createElement(this.state.selectedReport.component)}
                    </div>
                </div>
            </div>
        </AdminLayout>)
    }
}