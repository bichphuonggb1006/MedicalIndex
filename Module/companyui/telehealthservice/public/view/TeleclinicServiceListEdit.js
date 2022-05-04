class TeleclinicServiceListEdit extends PureComponent {
    constructor(props) {
        super(props);

        this.bindThis([
            'onModalShown', 'onModalHidden'
        ]);
        this.state = {
            'form': this.newInstance(),
            'dirs': [],
            'img': '',
            'parent': [],
            'sites': [],
            'type': 'ServiceList',
        };
        this.model = new TeleclinicServiceModel();
        this.model.getSite().then((resp) => {
            this.setPureState({sites: resp});
        });
        Lang.load('companyui', 'telehealthservice');
    }

    // khi hiện modal
    onModalShown() {
        //reset validate
        $(this.form).removeClass('was-validated');
        console.log('Hiện modal');
    }

    // khi ẩn modal
    onModalHidden() {
        console.log('Ẩn modal');
    }

    resetState() {
        this.setState({
            'form': this.newInstance(),
            'img': ''
        });
    }

    newInstance() {
        return {
            name: "",
            code: "",
            dirID: "",
            siteID:"",
            sort: "",
            description:"",
            thumbnail:"",
            price: "",
            isDoctor: 0
        }
    }

    previewImg(ev) {
        console.log(ev.target.files[0]);
        this.setPureState({'img': ev.target.files[0]});
    }

    deleteThumbnail($thumbnail){
        if($thumbnail == "preview" ){
            this.setState({'img': ''});
            document.getElementById('file-upload').value = '';
        }else{
            this.state.form.thumbnail = "";
            this.setPureState({form: this.state.form});
        }
    }

    // mở modal
    static open(serviceList) {
        return new Promise((done) => {
            new TeleclinicServiceModel().getServicesDir({deleted: 0}).then((dirs) => {
                TeleclinicServiceListEdit.getInstance().then((instance) => {
                    instance.resetState();
                    TeleclinicServiceList.instance = instance
                    TeleclinicServiceListEdit.instance.done = done || new Function;
                    instance.modal.showModal();
                    instance.parents = [];
                    let parent = dirs.filter(el => parseInt(el.parentID) == 0);
                    let newServiceList = instance.newInstance();
                    console.log("this.dirs", this.dirs);
                    instance.setPureState({
                        'form': $.extend(newServiceList, serviceList),
                        'parent': parent
                    });
                });
            })
        });
    }

    updateField(ev, key) {
        this.state.form[key] = ev.target.value;
        this.setPureState({form: this.state.form});
    }

    doInsertServiceList() {

        var data = $.extend({}, this.state.form);
        this.model.updateServiceList(this.state.form["id"], data).then((resp) => {
            console.log('resp----------', resp)
            if (resp.status) {
                if (TeleclinicServiceListEdit.instance.done)
                    TeleclinicServiceListEdit.instance.done(resp);
                this.modal.hideModal();
            } else {
                var errmsg = Lang.t('update.error');
                if (resp.hasOwnProperty('data') && resp.data.hasOwnProperty('error') && resp.data.error.length) {
                    errmsg = Lang.t(resp.data.error);
                }
                Alert.open(errmsg);
            }
            App.hideSprinner()
        }).catch((xhr) => {
            console.log(xhr.responseText);
            const err = (JSON.parse(xhr.responseText).data);
            if (this.editFail)
                this.editFail(xhr);
            App.hideSprinner()
        });
    }


    handleSubmit(ev) {
        ev.preventDefault();
        App.showSprinner()
        let fileUpload = document.getElementById('file-upload')
        if (fileUpload.files[0] !== undefined) {
            this.model.uploadBanner(fileUpload.files[0],this.state.type).then((resp) => {
                if (resp.status == true) {
                    this.state.form.thumbnail = resp.data.path;
                    this.setPureState({form: this.state.form}, () => {
                        this.doInsertServiceList();
                    });
                } else {
                    $.toast({
                        text: resp.data.message,
                        position: 'top-right',
                        icon: 'warning'
                    });
                }
            }).catch((xhr) => {
                $.toast({
                    text: xhr.message,
                    position: 'top-right',
                    icon: 'warning'
                });
                console.log('Lỗi', xhr)
            });
        } else {
            this.doInsertServiceList();
        }
    }

    render() {
        return (
            <form onSubmit={(ev) => {
                this.handleSubmit(ev);
            }} ref={(elm) => {
                this.form = elm;
            }} >
                <Modal ref={(elm) => {
                    this.modal = elm;
                }} events={{
                    'modal.shown': () => {
                        document.getElementById('file-upload').value = '';
                        this.onModalShown()
                    },
                    'modal.hidden': this.onModalHidden
                }}>
                    <Modal.Header>{Lang.t('serviceListEdit.header')}</Modal.Header>
                    <Modal.Body>
                            <div className="form-group row">
                                <label className="col-sm-3 col-form-label control-label"
                                       htmlFor="txt-name">{Lang.t('serviceList.name')} <Require/></label>
                                <div className="col-sm-9">
                                    <input type="text" className="form-control" id="txt-name" ref={(elm) => {
                                        this.nameSite = elm;
                                    }}
                                           value={this.state.form.name}
                                           onChange={(ev) => {
                                               this.updateField(ev, "name")
                                           }}
                                           required
                                    />
                                </div>
                            </div>

                            <div className="form-group row">
                                <label className="col-sm-3 col-form-label control-label"
                                       htmlFor="txt-code">{Lang.t('serviceList.code')} <Require/></label>
                                <div className="col-sm-9">
                                    <input type="text" className="form-control" id="txt-code" ref={(elm) => {
                                        this.nameSite = elm;
                                    }}
                                           value={this.state.form.code}
                                           onChange={(ev) => {
                                               this.updateField(ev, "code")
                                           }}
                                           required
                                    />
                                </div>
                            </div>

                            <div className="form-group row">
                                <label className="col-sm-3 col-form-label control-label"
                                       htmlFor="sel-dirID">{Lang.t('serviceList.dirID')} <Require/></label>
                                <div className="col-sm-9">
                                    <select className="form-control" id="sel-dirID" value={this.state.form.dirID || ''}
                                            onChange={(ev) => {
                                                this.updateField(ev, "dirID")
                                            }} required>
                                        <option value="">-- Nhóm dịch vụ --</option>
                                        {
                                            this.state.parent.map((p) => {
                                                return <option key={p.id}
                                                               value={p.id}>{p.name}</option>
                                            })
                                        }
                                    </select>
                                </div>
                            </div>

                            <div className="form-group row">
                                <label className="col-sm-3 col-form-label control-label"
                                       htmlFor="sel-siteID">Cơ sở y tế <Require/></label>
                                <div className="col-sm-9">
                                    <select className="form-control" id="sel-siteID" value={this.state.form.siteID || ''}
                                            onChange={(ev) => {
                                                this.updateField(ev, "siteID")
                                            }} required>
                                        <option value="">-- Cơ sở y tế --</option>
                                        {
                                            this.state.sites.map((site) => {
                                                return <option key={site.id}
                                                               value={site.id}>{site.name}</option>
                                            })
                                        }
                                    </select>
                                </div>
                            </div>

                            <div className="form-group row">
                                <label className="col-sm-3 col-form-label control-label"
                                       htmlFor="txt-sort">{Lang.t('serviceList.sort')}</label>
                                <div className="col-sm-9">
                                    <input type="text" className="form-control" id="txt-sort" ref={(elm) => {
                                        this.nameSite = elm;
                                    }}
                                           value={this.state.form.sort}
                                           onChange={(ev) => {
                                               this.updateField(ev, "sort")
                                           }}
                                    />
                                </div>
                            </div>

                            <div className="form-group row">
                                <label className="col-sm-3 col-form-label control-label"
                                       htmlFor="txt-price">{Lang.t('serviceList.price')}</label>
                                <div className="col-sm-9">
                                    <input type="text" className="form-control" id="txt-price" ref={(elm) => {
                                        this.nameSite = elm;
                                    }}
                                           value={this.state.form.price}
                                           onChange={(ev) => {
                                               this.updateField(ev, "price")
                                           }}
                                    />
                                </div>
                            </div>

                            <div className="form-group row">
                                <label className="col-sm-3 col-form-label control-label" htmlFor="chk-user-status">Bác sĩ</label>
                                <div className="col-sm-9">
                                    <CheckBox id="chk-user-status" ref="ckUserStatus" checked={this.state.form.isDoctor}
                                              onChange={(checked) => { this.state.form.isDoctor = checked; this.setPureState({form: this.state.form}); }}
                                    />
                                </div>
                            </div>

                            <div className="form-group row">
                                <label className="col-sm-3 col-form-label control-label"
                                       htmlFor="txt-descripsion">Mô tả</label>
                                <div className="col-sm-9">
                                    <textarea type="text" className="form-control" style={{height: 100}} id="txt-descripsion" ref={(elm) => {
                                        this.nameSite = elm;
                                    }}
                                           value={this.state.form.description}
                                           onChange={(ev) => {
                                               this.updateField(ev, "description")
                                           }}
                                    />
                                </div>
                            </div>

                            <div className="form-group row">
                                <label className="col-sm-3 col-form-label control-label" htmlFor="file-sort">Upload
                                    Banner</label>
                                <div className="col-sm-9">
                                    <input type="file" id="file-upload" onChange={(ev)=>{this.previewImg(ev)}} />
                                </div>
                            </div>

                            <div className="form-group row">
                                <label className="col-sm-3 col-form-label control-label" htmlFor="file-sort"></label>
                                <div className="col-sm-9">
                                    {
                                        this.state.img &&
                                        <div className="preview-upload">
                                            <img id='hinh-upload' style={{maxWidth: 210}}
                                                 src={URL.createObjectURL(this.state.img)}/>
                                            <span style={{margin: 15,fontSize: 20,color: "red"}} onClick={() => {this.deleteThumbnail("preview")}}><i className="fa fa-trash-o" aria-hidden="true"></i></span>
                                        </div>
                                    }
                                    {
                                        this.state.form.thumbnail && this.state.img == '' &&
                                        <div className="preview-upload">
                                            <img id='hinh-upload' style={{maxWidth: 210}}
                                                 src={App.url('/rest/upload/show?path=:path' ,{ path: this.state.form.thumbnail}) }/>
                                            <span style={{margin: 15,fontSize: 20,color: "red"}} onClick={() => {this.deleteThumbnail("upload")}}><i className="fa fa-trash-o" aria-hidden="true"></i></span>
                                        </div>
                                    }
                                </div>
                            </div>
                    </Modal.Body>
                    <Modal.Footer>
                        <button type="button" className="btn btn-secondary"
                                data-dismiss="modal">{Lang.t('teleclinic.btnClose')}</button>
                        <button type="submit" className="btn btn-primary">{Lang.t('teleclinic.btnSave')}</button>
                    </Modal.Footer>
                </Modal>
            </form>
        );
    }
}