class MedicalRecord extends Component {
    constructor(props) {
        super(props);
        this.clinicModel = new VclinicModel();
        this.medicalRecordModel = new MedicalRecordModel();
        this.state = {
            'modalOptions': this.handleModalOptions(props.modal),
            'isModal': (props && 'modal' in props) ? true : false,
            clinics: [],
            medicalRecods : [],
            filter: {
                siteID: App.siteID,
                depID: 0,
                dep: {
                    'name': ''
                },
                name: '',
                phone :''
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


    searchName(ev) {
        this.state.filter.name = ev.target.value;
        this.setPureState({filter: this.state.filter}, () => {
            this.getMedicalRecord(this.state.filter);
        });
    }
    searchPhone(ev) {
        this.state.filter.phone = ev.target.value;
        this.setPureState({filter: this.state.filter}, () => {
            this.getMedicalRecord(this.state.filter);
        });
    }
    getMedicalRecord(){
        return new Promise((done) => {
            this.medicalRecordModel.getMedicalRecord(this.state.filter).then((medicalRecods) => {
                // var patientAtrArr = medicalRecods.data;
                let patientAtrArr = medicalRecods.data.map(obj => (
                  JSON.parse(obj.patientAttr)
                ));

                this.setState({'medicalRecods': patientAtrArr}, () => {
                    console.log(this.state.medicalRecods);
                    if (done)
                        done(medicalRecods)
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
        this.getMedicalRecord()
    }

    componentWillMount() {
        if (!this.isModal()) {
            App.Component.trigger('leftNav.active', 'Record');
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
            {!this.isModal() && <PageHeader>Quản lý hồ sơ khám</PageHeader>}
            {!this.isModal() &&
            <div className="row">
                <div className={"col-md-12"}>
                    <div className="page-title-breadcrumb">
                        <div className=" pull-left">
                            <div className="page-title">Quản lý hồ sơ khám</div>
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
                        <div className="">
                            {/*<button className="btn btn-info" onClick={() => {*/}
                            {/*    this.editClinic();*/}
                            {/*}}>{Lang.t('teleclinic.btnNew')} <i className={"fa fa-plus"}></i></button>*/}
                            <div className="left div-search-site input-group" style={{"maxWidth": '250px',"marginRight":'15px'}}>
                                <div className="input-group-prepend">
                                    <span className="input-group-text"><i className="ti-search"></i></span>
                                </div>

                                <input className={"form-control"} type="text" onChange={(ev) => {
                                    this.state.filter.name = ev.target.value;
                                    this.getMedicalRecord();
                                    // this.setState({form: this.state.form});
                                }}
                                    placeholder="Tìm theo tên"/>
                            </div>
                            <div className="input-group left div-search-site" style={{maxWidth: '250px'}}>
                                <div className="input-group-prepend">
                                    <span className="input-group-text"><i className="ti-search"></i></span>
                                </div>

                                <input className={"form-control"} type="text" onChange={(ev) => {
                                    this.state.filter.phone = ev.target.value;
                                    this.getMedicalRecord();
                                    // this.setState({form: this.state.form});
                                }}
                                       placeholder="Tìm theo số điện thoại"/>
                            </div>
                        </div>}
                        <div className="clear"></div>
                    </div>
                    <div className={"table-scrollable"}>
                        <table className="table table-striped table-hover table-bordered" ref={(elm) => {
                            this.table = elm;
                        }}>
                            {/*<thead>*/}
                            {/*<tr>*/}
                            {/*    /!*<th style={{'minWidth': '30px'}}>*!/*/}

                            {/*    /!*</th>*!/*/}
                            {/*    <th style={{'width': '100%'}}>Tên phòng khám</th>*/}
                            {/*    <th style={{'minWidth': '400px'}}>Khoa</th>*/}
                            {/*    <th style={{'minWidth': '80px', 'textAlign': 'center'}}>Sắp xếp</th>*/}
                            {/*    <th style={{*/}
                            {/*        'minWidth': '140px',*/}
                            {/*        'textAlign': 'center'*/}
                            {/*    }}>{Lang.t("serviceDir.actions")}</th>*/}
                            {/*</tr>*/}
                            {/*</thead>*/}
                            {/*<tbody>*/}
                            {/*{this.state.clinics.map((clinic) => <tr key={clinic.id}>*/}
                            {/*    /!*<td></td>*!/*/}
                            {/*    <td><a href="javascript:;" onClick={() => {*/}
                            {/*        this.editClinic(clinic)*/}
                            {/*    }}>{clinic.name}</a></td>*/}
                            {/*    <td>{clinic.department.name}</td>*/}
                            {/*    <td align={"center"}>{clinic.sort}</td>*/}
                            {/*    <td align={"center"}>*/}
                            {/*        <a href="javascript:;" onClick={() => {*/}
                            {/*            this.editClinic(clinic)*/}
                            {/*        }} className={"btn btn-primary btn-xs"}><i className="fa fa-pencil"></i></a>*/}

                            {/*        <a href="javascript:;" onClick={() => {*/}
                            {/*            this.deleteClinic(clinic.id)*/}
                            {/*        }} className={"btn btn-danger btn-xs"}><i className="fa fa-trash"></i></a>*/}
                            {/*    </td>*/}
                            {/*</tr>)}*/}
                            {/*</tbody>*/}

                            <thead>
                            <tr>
                                {/*<th style={{'minWidth': '30px'}}>*/}

                                {/*</th>*/}
                                {/*<th style={{'minWidth': '100px'}}>Họ tên</th>*/}
                                {/*<th style={{'minWidth': '50px'}}>Giới tính</th>*/}
                                {/*<th style={{'minWidth': '100px'}}>Số điện thoại </th>*/}

                                {/*<th style={{'minWidth': '10%'}}>Địa chỉ </th>*/}


                                <th style={{'minWidth': '20%'}}>Họ tên</th>
                                <th style={{'minWidth': '5%'}}>Giới tính</th>
                                <th style={{'minWidth': '20%'}}>Số điện thoại </th>
                                <th style={{'minWidth': '30%'}}>Địa chỉ </th>

                            </tr>
                            </thead>
                            <tbody>
                            {this.state.medicalRecods.map((record,idx) => <tr key={idx}>
                                <td>
                                <a href={App.siteUrl + '/'+App.siteID+ '/teleclinic/record/'+record.phone} target="blank"  >
                                    {record.name}
                                </a>
                                </td>
                                {/*<td>*/}
                                {/*    <a href="{App.siteUrl + '/theodoidieutri'}" onClick={() => { }}>{record.name}</a>*/}
                                {/*</td>*/}
                                <td>{record.sex === 'F' ? 'Nữ' : 'Nam'}</td>
                                <td >{record.phone}</td>
                                <td >{record.addressText}</td>
                                {/*<td align={"center"}>*/}
                                {/*    <a href="javascript:;" onClick={() => {*/}
                                {/*        this.editClinic(clinic)*/}
                                {/*    }} className={"btn btn-primary btn-xs"}><i className="fa fa-pencil"></i></a>*/}

                                {/*    <a href="javascript:;" onClick={() => {*/}
                                {/*        this.deleteClinic(clinic.id)*/}
                                {/*    }} className={"btn btn-danger btn-xs"}><i className="fa fa-trash"></i></a>*/}
                                {/*</td>*/}

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