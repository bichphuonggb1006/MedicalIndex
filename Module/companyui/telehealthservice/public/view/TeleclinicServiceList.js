class TeleclinicServiceList extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            'modalOptions': this.handleModalOptions(props.modal),
            'isModal': (props && 'modal' in props) ? true : false,
            'servicesList': [],
            'checked': {},
            'dirs': [],
            'sites':[],
            'filter': {
                "dirID": "",
                "siteID": "",
                "name": "",
                "deleted": 0
            },
            'parent': []
        };
        this.model = new TeleclinicServiceModel();
        Lang.load('companyui', 'telehealthservice');
    }

    isModal() {
        return this.state && this.state.isModal;
    }

    handleModalOptions(opts) {
        //default
        return $.extend({
            'multiple': false
        }, opts);
    }

    open() {
        return new Promise((done) => {
            this.done = done
            this.setPureState({'checked': {}}, () => {
                this.modal.showModal()
            })
        })
    }

    toggleCheck(itemID) {
        this.state.checked[itemID] = this.state.checked[itemID] ? false : true;
        this.setPureState({'checked': this.state.checked})
    }

    toggleCheckAll(newVal) {
        for (let i in this.state.servicesList) {
            let item = this.state.servicesList[i]
            this.state.checked[item.id] = newVal
        }
        this.setPureState({'checked': this.state.checked})
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

    componentWillMount() {
        if (!this.isModal()) {
            App.Component.trigger('leftNav.active', 'TeleclinicServiceList');
        }
    }

    componentDidUpdate() {
        this.trigger('update');
    }

    componentDidMount() {
        App.requireLogin();
        this.getServiceList({deleted: 0});
    }

    getServiceList(filter = null) {
        return new Promise((done) => {
            Promise.all([this.model.getServicesList(filter), this.model.getServicesDir({deleted: 0}),this.model.getSite()]).then((resp) => {
                let servicesList = resp[0];
                delete servicesList["version"];

                let sortedDirs = resp[1];
                let siteList = resp[2];
                let parent = sortedDirs.filter(el => parseInt(el.parentID) == 0);
                delete sortedDirs["version"];
                delete siteList["version"];
                console.log(parent)
                this.setState({'servicesList': servicesList, 'dirs': sortedDirs, 'parent': parent, 'sites': siteList}, () => {
                    done()
                });
            });
        });
    }

    deleteServiceList(id) {
        Confirm.open("Xác nhận xóa dịch vụ đang chọn?").then((resp) => {
            if (resp)
                this.model.deleteServiceList(id).then(() => {
                    this.getServiceList({deleted: 0});
                }, function (resp) {
                    var errmsg = Lang.t('update.error');
                    if (resp.hasOwnProperty('data') && resp.data.hasOwnProperty('error') && resp.data.error.length) {
                        errmsg = Lang.t(resp.data.error);
                    }
                    Alert.open(errmsg);
                });
        });

    }

    editServiceList(serviceList) {
        TeleclinicServiceListEdit.open(serviceList).then((resp) => {
            if (resp.status) {
                this.getServiceList({deleted: 0});
            } else {
                Alert.open(Lang.t('update.error'));
            }
        });
    }

    selectDir(ev) {
        this.state.filter.dirID = ev.target.value;
        this.setPureState({filter: this.state.filter}, () => {
            this.getServiceList(this.state.filter);
        });
    }

    selectSite(ev) {
        this.state.filter.siteID = ev.target.value;
        this.setPureState({filter: this.state.filter}, () => {
            this.getServiceList(this.state.filter);
        });
    }

    searchName(ev) {
        this.state.filter.name = ev.target.value;
        this.setPureState({filter: this.state.filter}, () => {
            this.getServiceList(this.state.filter);
        });
    }

    findDirById(dirID) {
        for (let i in this.state.dirs) {
            let dir = this.state.dirs[i]
            if (dir.id == dirID)
                return dir
        }
    }

    findSiteById(siteID){
        for (let i in this.state.sites) {
            let site = this.state.sites[i]
            if (site.id == siteID)
                return site
        }
    }

    showSiteName(siteID){
        let site = this.findSiteById(siteID)
        return site.name
    }

    showDirName(dirID) {
        let directDir = this.findDirById(dirID)
        if (directDir.parentID == 0)
            return directDir.name
        let parentDir = this.findDirById(directDir.parentID)
        return parentDir.name + ' <i class="fa fa-angle-right"><\/i> ' + directDir.name
    }

    confirmSelect() {
        let ret = []
        for (let i in this.state.servicesList) {
            let service = this.state.servicesList[i]
            if (this.state.checked[service.id]) {
                ret.push(service)
            }
        }
        this.done(ret)
        this.modal.hideModal()
    }

    pageContent() {
        return (
            <div>
                {!this.isModal() && <PageHeader>{Lang.t('serviceList.header')}</PageHeader>}
                {!this.isModal() &&
                <div className="row">
                    <div className={"col-md-12"}>
                        <div className="page-title-breadcrumb">
                            <div className=" pull-left">
                                <div className="page-title">Dịch vụ</div>
                            </div>
                        </div>
                    </div>
                </div>
                }
                <div className="card card-services">
                    <div className="card-head"></div>
                    <div className="card-body">
                        <div>
                            {!this.isModal() &&
                            <div className="left">
                                <button type="button" className="btn btn-info" onClick={() => {
                                    this.editServiceList()
                                }}>{Lang.t('teleclinic.btnNew')} <i className={"fa fa-plus"}></i>
                                </button>
                            </div>
                            }
                        </div>

                        <select style={{maxWidth: '200px'}} className="form-control input-group right" id="sel-dir"
                                value={this.state.filter.dirID} onChange={(ev) => this.selectDir(ev)}>
                            <option value={0}>Nhóm thư mục</option>
                            {
                                this.state.parent.map((p, idx) => {
                                    return <option key={p.id}
                                                   value={p.id}>{p.name}</option>
                                })
                            }
                        </select>

                        <select style={{maxWidth: '200px'}} className="form-control input-group right" id="sel-siteID"
                                value={this.state.filter.siteID} onChange={(ev) => this.selectSite(ev)}>
                            <option value={0}>Cơ sở y tế</option>
                            {
                                this.state.sites.map((site) => {
                                    return <option key={site.id}
                                                   value={site.id}>{site.name}</option>
                                })
                            }
                        </select>

                        <div className="input-group right div-search-site" style={{maxWidth: '250px'}}>
                            <div className="input-group-prepend">
                                <span className="input-group-text"><i className="ti-search"></i></span>
                            </div>
                            <input type="text" className="form-control" placeholder={Lang.t('teleclinic.placeSearch')}
                                   onChange={(ev) => {
                                       this.searchName(ev)
                                   }}/>

                        </div>

                        <h4/>
                        <div className={"table-scrollable"}>
                            <table className="table table-striped table-bordered" ref={(elm) => {
                                this.table = elm;
                            }}>
                                <thead>
                                <tr>
                                    {this.isModal() && <th>
                                        <input type="checkbox" onChange={(ev) => {
                                            this.toggleCheckAll(ev.target.checked)
                                        }}/>
                                    </th>}
                                    <th>{Lang.t("serviceList.id")}</th>
                                    <th>{Lang.t("serviceList.name")}</th>
                                    <th>{Lang.t("serviceList.code")}</th>
                                    <th>{Lang.t("serviceList.dirID")}</th>
                                    <th>Cơ sở y tế</th>
                                    <th style={{"minWidth" : "80px", "textAlign" : "center"}}>{Lang.t("serviceList.sort")}</th>
                                    {!this.isModal() && <th>{Lang.t("serviceList.price")}</th>}
                                    {!this.isModal() && <th style={{"textAlign" : "center"}}>{Lang.t("serviceDir.actions")}</th>}
                                </tr>
                                </thead>
                                <tbody>
                                {this.state.servicesList.map((el) =>
                                    <tr key={el.id}>
                                        {this.isModal() && <td style={{'verticalAlign' : 'top'}}>
                                            <input type="checkbox"
                                                   checked={this.state.checked[el.id] ? true : false}
                                                   onChange={() => {
                                                       this.toggleCheck(el.id)
                                                   }}
                                                   style={{'marginTop' : '5px'}}/>
                                        </td>}
                                        <td style={{"verticalAlign" : "top"}}>{el.id}</td>
                                        {!this.isModal() && <td style={{"verticalAlign" : "top"}}><a href="javascript:;" onClick={() => {
                                            if (!this.isModal()) this.editServiceList(el);
                                        }}>{el.name}</a></td>}
                                        {this.isModal() && <td style={{"verticalAlign" : "top"}}>{el.name}</td>}
                                        <td style={{"verticalAlign" : "top"}}>{el.code}</td>
                                        <td style={{"verticalAlign" : "top"}} dangerouslySetInnerHTML={{__html: this.showDirName(el.dirID)}}></td>
                                        <td style={{"verticalAlign" : "top"}}>{this.showSiteName(el.siteID)}</td>
                                        <td style={{"verticalAlign" : "top","textAlign" : "center"}}>{el.sort}</td>
                                        {!this.isModal() && <td style={{"verticalAlign" : "top"}}>{el.price}</td>}
                                        {!this.isModal() && <td align={"center"}  style={{"verticalAlign" : "top"}}>
                                            <a href="javascript:;" onClick={() => {
                                                if (!this.isModal()) this.editServiceList(el);
                                            }} className={"btn btn-primary btn-xs"}>
                                                <i className="fa fa-pencil" aria-hidden="true"></i>
                                            </a>
                                            <a href="javascript:;" onClick={() => {
                                                this.deleteServiceList(el.id);
                                            }} className={"btn btn-danger btn-xs"}>
                                                <i className="fa fa-trash-o" aria-hidden="true"></i>
                                            </a>
                                        </td>}

                                    </tr>
                                )}

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>)
    }

    render() {
        if (this.isModal())
            return (<Modal ref={(el) => {
                this.modal = el
            }} id="teleclinic-service-picker" size="modal-lg">
                <Modal.Header>Chọn dịch vụ</Modal.Header>
                <Modal.Body>{this.pageContent()}</Modal.Body>
                <Modal.Footer>
                    <button className="btn btn-secondary" type="button" onClick={() => {
                        this.modal.hideModal()
                    }}>Hủy bỏ
                    </button>
                    <button className="btn btn-primary" type="button" onClick={() => {
                        this.confirmSelect()
                    }}>Chọn
                    </button>
                </Modal.Footer>
            </Modal>);
        else
            return (
                <AdminLayout>
                    {this.pageContent()}
                </AdminLayout>
            );
    }
}