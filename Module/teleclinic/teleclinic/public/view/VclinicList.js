class VclinicList extends Component {
    constructor(props) {
        super(props);
        this.clinicModel = new VclinicModel()
        this.state = {
            'modalOptions': this.handleModalOptions(props.modal),
            'isModal': (props && 'modal' in props) ? true : false,
            clinics: [],
            filter: {
                siteID: App.siteID,
                depID: 0,
                dep: {
                    'name': ''
                }
            }
        }

        this.bindThis(['deleteClinic']);
        Lang.load('teleclinic', 'teleclinic');
    }

    getClinics() {
        return new Promise((done) => {
            this.clinicModel.getClinics(this.state.filter).then((clinics) => {
                this.setState({'clinics': clinics}, () => {
                    if (done)
                        done(clinics)
                })
            })
        })
    }

    deleteClinic(id) {
        Confirm.open("Xác nhận xóa phòng khám đang chọn?").then((resp) => {
            if (resp)
                this.clinicModel.deleteClinic(id).then(() => {
                    this.getClinics()
                })
        });
    }

    editClinic(clinic) {
        VclinicEdit.open(clinic).then(() => {
            this.getClinics()
        })
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
        this.getClinics()
    }

    componentWillMount() {
        if (!this.isModal()) {
            App.Component.trigger('leftNav.active', 'Vclinic');
        }
    }

    componentDidUpdate() {
        this.trigger('update');
    }

    openDepFilter() {
        DepPicker.open().then((deps) => {
            if (!deps) {
                this.state.filter.dep = {}
                this.state.filter.depID = 0
                return
            }
            this.state.filter.dep = deps[0]
            this.state.filter.depID = deps[0].id
            this.getClinics()
        })
    }

    pageContent() {
        return (<div className="vclinic-page">
            {!this.isModal() && <PageHeader>Quản lý phòng khám</PageHeader>}
            {!this.isModal() &&
            <div className="row">
                <div className={"col-md-12"}>
                    <div className="page-title-breadcrumb">
                        <div className=" pull-left">
                            <div className="page-title">Quản lý phòng khám</div>
                        </div>
                    </div>
                </div>
            </div>
            }
            <div className="card card-vc-clinics">
                <div className="card-head"></div>
                <div className="card-body">
                    <div className="btn-and-search">
                        {!this.isModal() &&
                        <div className="left">
                            <button className="btn btn-info" onClick={() => {
                                this.editClinic();
                            }}>Thêm mới<i className={"fa fa-plus"}></i></button>
                        </div>}
                        <div className="right searchBox">
                            <div className="input-group" style={{maxWidth: '250px'}}>
                                <div className="input-group-prepend">
                                    <span className="input-group-text"><i className="ti-search"></i></span>
                                </div>
                                <input className={"form-control"} type="text" readOnly={true} onClick={() => {
                                    this.openDepFilter()
                                }}
                                       value={this.state.filter.dep.name} placeholder="Tìm theo khoa"/>
                            </div>
                        </div>
                        <div className="clear"></div>
                    </div>
                    <div className={"table-scrollable"}>
                        <table className="table table-striped table-hover table-bordered" ref={(elm) => {
                            this.table = elm;
                        }}>
                            <thead>
                            <tr>
                                {/*<th style={{'minWidth': '30px'}}>*/}

                                {/*</th>*/}
                                <th style={{'width': '100%'}}>Tên phòng khám</th>
                                <th style={{'minWidth': '400px'}}>Khoa</th>
                                <th style={{'minWidth': '80px', 'textAlign': 'center'}}>Sắp xếp</th>
                                <th style={{
                                    'minWidth': '140px',
                                    'textAlign': 'center'
                                }}>Thao tác</th>
                            </tr>
                            </thead>
                            <tbody>
                            {this.state.clinics.map((clinic) => <tr key={clinic.id}>
                                {/*<td></td>*/}
                                <td><a href="javascript:;" onClick={() => {
                                    this.editClinic(clinic)
                                }}>{clinic.name}</a></td>
                                <td>{clinic.department.name}</td>
                                <td align={"center"}>{clinic.sort}</td>
                                <td align={"center"}>
                                    <a href="javascript:;" onClick={() => {
                                        this.editClinic(clinic)
                                    }} className={"btn btn-primary btn-xs"}><i className="fa fa-pencil"></i></a>

                                    <a href="javascript:;" onClick={() => {
                                        this.deleteClinic(clinic.id)
                                    }} className={"btn btn-danger btn-xs"}><i className="fa fa-trash"></i></a>
                                </td>
                            </tr>)}
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