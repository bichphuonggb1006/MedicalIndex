class TeleclinicServiceDir extends PureComponent {
    constructor(props) {
        super(props);

        this.state = {
            'modalOptions': this.handleModalOptions(props.modal),
            'isModal': (props && 'modal' in props) ? true : false,
            'servicesDir': [],
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

    componentWillMount() {
        if (!this.isModal()) {
            App.Component.trigger('leftNav.active', 'TeleclinicServiceDir');
        }
    }

    componentDidUpdate() {
        this.trigger('update');
    }

    componentDidMount() {
        App.requireLogin();
        this.getServiceDir();
    }

    getServiceDir() {
        return new Promise((done) => {
            this.model.getServicesDir({deleted: 0}).then((resp) => {
                delete resp["version"];
                this.parent = resp.filter(el => parseInt(el.parentID) == 0);
                console.log("service Dirs ", resp);
                this.setState({'parent': this.parent, 'servicesDir': resp}, () => {
                    // this.state.parent.map((p, idx) => {
                    //     this.state.servicesDir.filter((obj) => ((obj.parentID && obj.parentID == p.id) || obj.id == p.id))
                    //         .sort((a, b) => parseInt(a.parentID) > parseInt(b.parentID) ? 1 : -1)
                    //         .map((el, jdx) => {
                    //             console.log("el", el);
                    //         });
                    // });
                    done()
                });
            });
        });
    }

    deleteServiceDir(id) {
        Confirm.open("Xoá thư mục được chọn?").then((resp) => {
            if (resp)
                this.model.deleteServiceDir(id).then(() => {
                    this.getServiceDir();
                }, function (resp) {
                    var errmsg = Lang.t('update.error');
                    if (resp.hasOwnProperty('data') && resp.data.hasOwnProperty('error') && resp.data.error.length) {
                        errmsg = Lang.t(resp.data.error);
                    }
                    Alert.open(errmsg);
                });
        });

    }

    editServiceDir(serviceDir) {
        TeleclinicServiceDirEdit.open(serviceDir, this.parent).then((resp) => {
            if (resp.status) {
                this.getServiceDir();
            } else {
                Alert.open(Lang.t('update.error'));
            }
        });
    }

    renderDirName(dir) {
        if (!this.state.servicesDir || !this.state.servicesDir.length || !dir || !dir.hasOwnProperty('id'))
            return "";

        if (!parseInt(dir.id))
            return Lang.t("root");

        var ret = "";
        for (var i in this.state.servicesDir) {
            if (this.state.servicesDir[i].id != dir.id)
                continue;

            ret = this.state.servicesDir[i].name;
        }

        return ret;
    }

    pageContent() {
        return (
            <div>
                {!this.isModal() && <PageHeader>{Lang.t('serviceDir.header')}</PageHeader>}
                {!this.isModal() &&
                <div className="row">
                    <div className={"col-md-12"}>
                        <div className="page-title-breadcrumb">
                            <div className=" pull-left">
                                <div className="page-title">Nhóm dịch vụ</div>
                            </div>
                        </div>
                    </div>
                </div>
                }
                <div className="card card-service-dirs">
                    <div className="card-head"></div>
                    <div className="card-body">
                        <div>
                            {!this.isModal() &&
                            <div className="left">
                                <button type="button" className="btn btn-info" onClick={(ev) => {
                                    this.editServiceDir();
                                    ev.currentTarget.blur();
                                }}>{Lang.t('teleclinic.btnNew')} <i className={"fa fa-plus"}></i></button>
                            </div>
                            }
                        </div>

                        <h4/>
                        <div className={"table-scrollable"}>
                            <table className="table table-striped table-bordered table-groups" ref={(elm) => {
                                this.table = elm;
                            }}>
                                <thead>
                                <tr>
                                    {/*<th>{Lang.t("serviceDir.id")}</th>*/}
                                    <th>{Lang.t("serviceDir.name")}</th>
                                    {/*<th>{Lang.t("serviceDir.parentID")}</th>*/}
                                    <th style={{"textAlign" : "center"}}>{Lang.t("serviceDir.sort")}</th>
                                    <th style={{"textAlign" : "center"}}>{Lang.t("serviceDir.actions")}</th>
                                </tr>
                                </thead>
                                <tbody>
                                {
                                    this.state.parent.map((p, idx) => {
                                        return this.state.servicesDir.filter((obj) => ((obj.parentID && obj.parentID == p.id) || obj.id == p.id))
                                            .sort((a, b) => parseInt(a.parentID) > parseInt(b.parentID) ? 1 : -1)
                                            .map((el, jdx) => {
                                                    return <tr key={jdx} className="tbl-row">
                                                        {!this.isModal() &&
                                                        <td >
                                                            <a href="javascript:;"
                                                               style={el.parentID != 0 ? {"marginLeft": "40px"} : null}
                                                               onClick={() => {
                                                                   if (!this.isModal()) this.editServiceDir(el);
                                                               }}>{el.name}</a>
                                                        </td>}
                                                        {/*<td>{this.renderDirName({'id': el.parentID})}</td>*/}
                                                        <td align={"center"}>{el.sort}</td>
                                                        <td align={"center"}>
                                                            <a href="javascript:;" className={"btn btn-primary btn-xs"}
                                                               onClick={() => {
                                                                   if (!this.isModal()) this.editServiceDir(el);
                                                               }}><i className="fa fa-pencil" aria-hidden="true"></i></a>

                                                            <a href="javascript:;" className={"btn btn-danger btn-xs"}
                                                               onClick={() => {
                                                                   this.deleteServiceDir(el.id);
                                                               }}><i className="fa fa-trash-o" aria-hidden="true"></i></a>
                                                        </td>

                                                    </tr>
                                                }
                                            )
                                    })
                                }
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>)
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