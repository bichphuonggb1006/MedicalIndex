const GROUP_REQUEST_DATE = 2;
const GROUP_SERVICE_NAME = 1;

const SORT_REQUEST_DATE = "reqDate";

class Unscheduled extends PureComponent {
    constructor(props) {
        super(props);

        this.state = {
            'modalOptions': this.handleModalOptions(props.modal),
            'isModal': (props && 'modal' in props) ? true : false,
            lang: {},
            filter: {
                status: "unscheduled",
                // reqDate: new Date().toISOString().substring(0, 10),
                reqDate: ""
            },
            services: {},
            requestsList: {},
            groupBy: GROUP_SERVICE_NAME,
            groupByList: {},
            sort: SORT_REQUEST_DATE,
            sortList: {},
            servicesList: [],
            servicesDir: {}
        }
        this.scheduleModel = new ScheduleModel();
        this.serviceModel = new TeleclinicServiceModel();

        Lang.load('teleclinic', 'teleclinic').then(() => {
            this.setState({
                lang: Lang.messages,
                groupByList: {
                    [GROUP_SERVICE_NAME]: Lang.t("schedule.serviceName"),
                    [GROUP_REQUEST_DATE]: Lang.t("schedule.reqDate")
                },
                sortList: {
                    [SORT_REQUEST_DATE]: Lang.t("schedule.reqDate")
                }

            });
        });
    }

    getRequest() {
        return new Promise(done => {
            this.scheduleModel.getSchedule(this.state.filter, this.state.sort)
                .then(resp => {
                    delete resp["version"];

                    let res = {};

                    resp.forEach(item => {
                        let key;
                        if (this.state.groupBy == GROUP_REQUEST_DATE)
                            key = item["reqDate"];
                        else if (this.state.groupBy == GROUP_SERVICE_NAME)
                            key = this.getServicePath(item);

                        if (key in res)
                            res[key].push(item);
                        else
                            res[key] = [item];
                    });

                    console.log("requestsList", res);
                    this.setState({requestsList: res}, () => {
                        done(res)
                    });
                })
        });
    }

    getServices() {
        return new Promise((done) => {
            Promise.all([this.serviceModel.getServicesList(), this.serviceModel.getServicesDir()]).then((resp) => {
                console.log("resp", resp);
                let servicesList = resp[0];
                delete servicesList["version"];

                let servicesDirTmp = resp[1];
                delete servicesDirTmp["version"];

                let servicesDir = {};
                servicesDirTmp.forEach(item => {
                    servicesDir[item.id] = item;
                });

                let services = {};
                // group by dirID
                servicesList.forEach(item => {
                    let parentInfo = servicesDir[item.dirID];
                    let rootName = "";
                    let name = parentInfo.name;
                    if (parentInfo.parentID != "0")
                        rootName = servicesDir[parentInfo.parentID].name;

                    if (item.dirID in services)
                        services[item.dirID]["services"].push(item);
                    else
                        services[item.dirID] = {
                            rootName: rootName,
                            name: name,
                            services: [item]
                        };
                })

                this.setState({'services': services, 'servicesList': servicesList, 'servicesDir': servicesDir}, () => {
                    done()
                });
            });
        });
    }

    isModal() {
        return this.state && this.state.isModal;
    }

    handleModalOptions(opts) {
        //default
        return $.extend({
            'multiple': false,
            'type': 'department'
        }, opts);
    }

    componentWillReceiveProps(nexProps) {
        if (!nexProps)
            return;
        if ('modal' in nexProps) {
            this.setState({'modalOptions': this.handleModalOptions(nexProps.modal)});
        } else {
            this.setState({'modalOptions': {}});
        }
    }

    componentDidMount() {
        App.requireLogin();
        App.Component.trigger('leftNav.show', true);
        this.getServices().then(() => {
            this.getRequest();
        });

        //60s tu dong load danh sach
        setInterval(() => {
                this.getRequest();
            }
            , 60 * 1000);

    }

    componentWillMount() {
        if (!this.isModal()) {
            App.Component.trigger('leftNav.active', 'Unscheduled');
        }
    }

    translate(key) {
        if (!(key in this.state.lang))
            return key;

        return this.state.lang[key];
    }

    changeActiveElement(ev, status) {
        $(".dep-title").each(function (index) {
            $(this).removeClass("dep-title-active");
        });

        $(ev.target).addClass("dep-title-active");

        this.updateFilter("status", status, true);
    }

    updateFilter(key, value, reload = false) {
        if (!value)
            delete this.state.filter[key];
        else
            this.state.filter[key] = value;
        this.setState({filter: this.state.filter}, () => {
            if (reload)
                this.getRequest();
        });
    }

    changeGroupByField(value) {
        this.setState({groupBy: value}, () => {
            this.getRequest();
        });
    }

    changeOrderByField(field) {
        this.setState({sort: field}, () => {

        });
    }

    convertReqTime(reqTime) {
        if (!reqTime)
            return "";

        let times = reqTime.split(",");
        console.log(times)

        let result = "";
        times.forEach(item => {
            let val = parseInt(item);
            result += `${val}h-${val + 1}h, `;
        })

        return result.substring(0, result.length - 2);
    }

    getServiceGroup(request) {
        // console.log('request',request);
        let service = request["reqService"];

        if (service.hasOwnProperty('reqHealthInsurance') && service.reqHealthInsurance.length)
            return "Khám bảo hiểm y tế";

        let parentInfo = this.state.services[service["dirID"].toString()];
        if (!parentInfo)
            return '';

        return parentInfo["rootName"];

    }

    getServicePath(request) {
        // return request["reqService"]["name"];
        let service = request["reqService"];

        let parentInfo = this.state.services[service["dirID"].toString()];
        if (!parentInfo)
            return ''
        let res = "";
        // if (parentInfo["rootName"])
        //     res += parentInfo["rootName"] + " > ";

        /* Kiểm tra dịch vụ, thư mục dv có bị xoá k*/
        var posDir = this.getPosDir({'id': parseInt(service["dirID"])});
        var posService = this.getPosService(service);
        var dirName = parentInfo["name"];
        var srvName = service["name"];

        if (posDir != -1) {
            var dir = this.state.servicesDir[posDir];
            if (parseInt(dir.deleted)) {
                dirName = "<span class='deleted-dir'>" + dirName + "</span>";
            }
        }

        if (posService != -1) {
            var srv = this.state.servicesList[posService];
            if (parseInt(srv.deleted)) {
                srvName = "<span class='deleted-service'>" + srvName + "</span>";
            }
        }

        res += "<div>" + dirName + " <i class=\"fa fa-angle-right\"><\/i> " + srvName + "</div>";

        return res;
    }

    getPosDir(dir) {
        if (!this.state.servicesDir || !Object.keys(this.state.servicesDir).length || !dir || !dir.hasOwnProperty('id'))
            return -1;

        let pos = -1;
        for (var i in this.state.servicesDir) {
            if (!this.state.servicesDir[i] || this.state.servicesDir[i].id != dir.id)
                continue;

            pos = i;
            break;
        }

        return pos;
    }

    getPosService(srv) {
        if (!this.state.servicesList || !this.state.servicesList.length || !srv || !srv.hasOwnProperty('id'))
            return -1;

        var pos = -1;
        for (var i in this.state.servicesList) {
            if (!this.state.servicesList[i] || this.state.servicesList[i].id != srv.id)
                continue;
            pos = i;
            break;
        }

        return pos;
    }

    openScheduleEdit(item) {
        this.scheduleEdit.open(item).then(resp => {
            this.getRequest();
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

    pageContent() {
        return (
            <div className="main page-unschedule">
                <div className="row" style={{height: '100%'}}>
                    <div
                        className="col-sm-2 border  h-100 overflow-x-auto pl-0 pr-0 break-line theme-light sub-menu-left">
                        <ul className="schedule-nav">
                            <li onClick={(ev) => this.changeActiveElement(ev, "unscheduled")}>
                                <a className="dep-title dep-title-active">
                                    <i className={"fa fa-calendar"} style={{"marginRight": '14px'}}></i>
                                    {this.translate('schedule.status.unscheduled')}
                                </a>
                            </li>
                            <li onClick={(ev) => this.changeActiveElement(ev, "cancelled")}>
                                <a className="dep-title">
                                    <i className={"fa fa-calendar-times-o"} style={{"marginRight": '14px'}}></i>
                                    {this.translate('schedule.status.canceled')}</a>
                            </li>
                        </ul>
                    </div>

                    <div className="col-sm-10 border  h-100 pl-0 pr-0 break-line theme-light content-schedule">
                        <div className="page-bar">
                            <div className="page-title-breadcrumb">
                                <div className=" pull-left">
                                    <div className="page-title">
                                        {this.state.filter.status == 'unscheduled' && "Chưa xếp lịch"}
                                        {this.state.filter.status == 'cancelled' && "Đã hủy"}
                                    </div>
                                </div>
                                <ol className="breadcrumb page-breadcrumb pull-right">
                                    <li>
                                        <i className="fa fa-home"></i>
                                        <a className="parent-item" href={App.url('/:siteID/teleclinic/unscheduled', {siteID: App.siteID})}>Chưa xếp lịch</a>
                                        <i className="fa fa-angle-right"></i>
                                    </li>
                                    <li>
                                        {this.state.filter.status == 'unscheduled' && "Chưa xếp lịch"}
                                        {this.state.filter.status == 'cancelled' && "Đã hủy"}
                                    </li>
                                </ol>
                            </div>
                        </div>
                        <div className={"card"}>
                            <div className={"card-head"}></div>
                            <div className={"card-body"}
                                 style={{"display": "flex", "flexDirection": "column", "overflow": "hidden"}}>
                                <div className="filter-list d-flex flex-row p-3">
                                    <div className="" style={{minWidth: "150px"}}>
                                        <label
                                            htmlFor="searchPatientName">{this.translate("schedule.patientName")}</label>
                                        <input id="searchPatientName" type="text" className=""
                                               onChange={(ev) => {
                                                   this.updateFilter("patientName", ev.target.value)
                                               }}/>
                                    </div>
                                    <div className="ml-3" style={{minWidth: "100px"}}>
                                        <label htmlFor="searchReqDate">{this.translate("schedule.reqDate")}</label>
                                        <input id="searchReqDate" type="date" className=""
                                               style={{height: "30px"}}
                                            // defaultValue={this.state.filter.reqDate}
                                               onChange={(ev) => {
                                                   this.updateFilter("reqDate", ev.target.value)
                                               }}/>
                                    </div>
                                    <div className="ml-3" style={{minWidth: "250px"}}>
                                        <label
                                            htmlFor="searchReqName">{this.translate("schedule.serviceName")}</label>
                                        <select className="input-group" id="searchReqName"
                                                onChange={(ev) => {
                                                    this.updateFilter("reqServiceID", ev.target.value)
                                                }}
                                                style={{minWidth: "250px"}}>
                                            {
                                                <React.Fragment>
                                                    <option
                                                        value={""}>{this.translate("schedule.unscheduled.allService")}</option>
                                                    {
                                                        Object.keys(this.state.services).map((key) =>
                                                            <optgroup key={key}
                                                                      label={(this.state.services[key].rootName ? this.state.services[key].rootName + " + " : "") + this.state.services[key].name}>
                                                                {this.state.services[key]["services"].map(service =>
                                                                    <option key={service.id}
                                                                            value={service.id}>{service.name}</option>
                                                                )}
                                                            </optgroup>
                                                        )
                                                    }
                                                </React.Fragment>

                                            }

                                        </select>
                                    </div>
                                    <div className="ml-3" style={{minWidth: "100px"}}>
                                        <label
                                            htmlFor="searchGroupBy">{this.translate("schedule.unscheduled.filter.groupBy")}</label>
                                        <select className="input-group" id="searchReqName"
                                                onChange={(ev) => {
                                                    this.changeGroupByField(ev.target.value)
                                                }}
                                                value={this.state.groupBy}
                                                style={{minWidth: "100px"}}>
                                            {
                                                Object.keys(this.state.groupByList).map((key) =>
                                                    <option key={key}
                                                            value={key}>{this.state.groupByList[key]}</option>
                                                )
                                            }

                                        </select>
                                    </div>
                                    <div className="ml-3" style={{minWidth: "100px"}}>
                                        <label
                                            htmlFor="searchSortBy">{this.translate("schedule.unscheduled.filter.sortBy")}</label>
                                        <select className="input-group" id="searchSortBy"
                                                onChange={(ev) => {
                                                    this.changeOrderByField(ev.target.value)
                                                }}
                                                value={this.state.sort}
                                                style={{minWidth: "100px"}}>
                                            {
                                                Object.keys(this.state.sortList).map((key) =>
                                                    <option key={key}
                                                            value={key}>{this.state.sortList[key]}</option>
                                                )
                                            }

                                        </select>
                                    </div>
                                </div>
                                <div className="pl-3 filter-actions">
                                    <button onClick={() => {
                                        this.getRequest()
                                    }} className="btn btn-primary">Tìm kiếm <i className={"fa fa-search"}></i></button>

                                </div>
                                <div className={"table-scrollable"} style={{"flex": 1}}>
                                    <div className={"request"}>
                                        <div className={"tbl-fixed-header"}>
                                            <table className="table table-bordered table-groups">
                                                <thead>
                                                <tr className="text-center">
                                                    {/*<th style={{width: "10px"}}/>*/}
                                                    <th style={{width: "250px"}}>{this.translate("schedule.patientName")}</th>
                                                    <th style={{width: "120px"}}>{this.translate("schedule.patientPhone")}</th>
                                                    <th style={{width: "100px"}}>{this.translate("schedule.patientBirthDate")}</th>
                                                    <th style={{width: "150px"}}>{this.translate("schedule.reqDate")}</th>
                                                    <th style={{width: "180px"}}>{this.translate("schedule.serviceGroup")}</th>
                                                    <th style={{width: "200px"}}>{this.translate("schedule.serviceName")}</th>
                                                    <th style={{width: "180px"}}>{this.translate("schedule.healthInsurance")}</th>
                                                    <th style={{width: "180px"}}>{this.translate("schedule.encounterID")}</th>
                                                    <th style={{width: "180px"}}>{this.translate("schedule.paymentStatus")}</th>
                                                </tr>
                                                </thead>
                                            </table>
                                        </div>
                                        <div className={"tbl-fixed-body"}>
                                            <table className="table table-bordered table-groups">
                                                {
                                                    Object.keys(this.state.requestsList).map(key =>
                                                        <React.Fragment key={key}>
                                                            <tbody>
                                                            <tr className={"tbl-group"}>
                                                                <td className="" colSpan={9}
                                                                    dangerouslySetInnerHTML={{__html: key}}></td>
                                                            </tr>
                                                            {
                                                                this.state.requestsList[key].map((item, index) =>
                                                                    <tr key={index} onClick={() => {
                                                                        this.openScheduleEdit(item)
                                                                    }} className={"vc-rows tbl-row"}>
                                                                        {/*<td/>*/}
                                                                        <td style={{width: "250px"}}>{item["patientName"]}</td>
                                                                        <td style={{width: "120px"}}>{item["patient"]["phone"]}</td>
                                                                        <td style={{width: "10px"}}>{this.renderPatientBirthDate(item["patient"])}</td>
                                                                        <td style={{width: "150px"}}>{moment(item["reqDate"]).format("DD/MM/YYYY")} {this.convertReqTime(item["reqTimes"])}</td>
                                                                        <td style={{width: "180px"}}>{this.getServiceGroup(item)}</td>
                                                                        <td style={{width: "200px"}} dangerouslySetInnerHTML={{__html: this.getServicePath(item)}}></td>
                                                                        <td style={{width: "180px"}}>{item["patient"]["healthInsurance"]}</td>
                                                                        <td style={{width: "180px"}}>{item["patient"]["encounterID"]}</td>
                                                                        <td style={{width: "180px"}}>{this.translate("schedule.paymentStatus." + item["paymentStatus"])}</td>

                                                                    </tr>
                                                                )
                                                            }
                                                            </tbody>
                                                        </React.Fragment>
                                                    )
                                                }
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <ScheduleEdit ref={(elm) => {
                    this.scheduleEdit = elm;
                }} events={{
                    "loadRequest": () => {
                        this.getRequest()
                    }
                }}
                />

            </div>
        );
    }

    render() {
        if (this.isModal())
            return this.pageContent();
        else
            return (
                <AdminLayout>
                    {this.pageContent()}
                </AdminLayout>
            );
    }
}