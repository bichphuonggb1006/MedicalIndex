class ProcessList extends PureComponent {
    constructor(props) {
        super(props);
        let url = window.location.pathname;
        this.state = {
            "processes": [],
            "serviceID": url.substring(
                url.lastIndexOf("services/") + "services/".length,
                url.lastIndexOf("/processes")
            ),
            "serviceName": "",
        }

        this.model = new ServiceModel();
        this.triggering = false;

        Lang.load('companyui', 'service');
    }

    componentDidMount() {
        App.requireLogin();
        this.getProcesses();
    }

    getProcesses() {
        return new Promise(done => {
            Promise.all([this.model.getService(this.state.serviceID), this.model.getProcesses(this.state.serviceID)]).then(resp => {
                console.log("getProcesses");
                let service = resp[0];
                let processes = resp[1];

                this.state.processes = [];
                delete processes["version"];

                Object.keys(processes).forEach((ip) => {
                    this.state.processes.push({ip: ip, pid: processes[ip], loading: false});
                });

                console.log(this.state.processes);
                this.setPureState({processes: this.state.processes, serviceName: service["name"]}, () => done());
            });
        });
    }

    handleProcess(ip, method) {
        for (let i=0; i< this.state.processes.length; i++) {
            if (method == "startAll") {
                if (this.state.processes[i].pid <= null)
                    this.state.processes[i].loading = true;
            } else if (method == "stopAll") {
                if (this.state.processes[i].pid > null)
                    this.state.processes[i].loading = true;
            } else {
                if (this.state.processes[i].ip == ip) {
                    this.state.processes[i].loading = true;
                    break;
                }
            }

        }

        this.setPureState({processes: this.state.processes});

        this.model.handleProcess(ip, this.state.serviceID, method).then(resp => {
            if (this.triggering === false) {
                this.triggering = true;
                console.log("setTimeOut...");
                setTimeout(() => {
                    console.log("Reloading...");
                    this.getProcesses();
                    this.triggering = false;
                }, 10000);
            }
        });
    }

    renderBtn(process) {
        if (process.loading === false) {
            if (process.pid > 0)
                return <button className="btn btn-danger" onClick={(ev) => this.handleProcess(process.ip, "stop")}>{Lang.t("process.btn.stop")}</button>;
            else
                return <button className="btn btn-primary" onClick={(ev) => this.handleProcess(process.ip,"start")}>{Lang.t("process.btn.start")}</button>;
        } else {
            return <button className="btn btn-secondary" type="button" disabled>
                <i className="fa fa-spinner fa-spin" aria-hidden="true"/>
            </button>;
        }
    }

    pageContent() {
        return (
            <div className="container-fluid">
                <div className="page-header text-center mt-4 mb-4">
                    <h1 className="header-title text-bold">Process {this.state.serviceName}</h1>
                </div>
                <div className="card">
                    <div className="card-body">
                        <div className="d-flex justify-content-between">
                            <div>
                                <button className="btn btn-primary" onClick={(ev) => this.handleProcess(null, "startAll")}>{Lang.t("process.btn.startAll")}</button>
                                <button className="btn btn-primary" onClick={(ev) => this.handleProcess(null, "stopAll")}>{Lang.t("process.btn.stopAll")}</button>
                            </div>
                        </div>

                        <div className="table-responsive">
                            <table className="table table-striped" ref={(elm) => {
                                this.table = elm;
                            }}>
                                <thead>
                                <tr>
                                    <th style={{width: "50px"}}>STT</th>
                                    <th>{Lang.t("process.ip")}</th>
                                    <th>{Lang.t("process.pid")}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                {this.state.processes.map((process, index) =>
                                    <tr key={process.ip}>
                                        <td >{index+1}</td>
                                        <td>{process.ip}</td>
                                        <td>{process.pid || ""}</td>
                                        <td style={{width: "50px"}}>
                                            {
                                                this.renderBtn(process)
                                            }
                                        </td>
                                    </tr>
                                )}

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    render() {
        return (
            this.pageContent()
        );
    }
}