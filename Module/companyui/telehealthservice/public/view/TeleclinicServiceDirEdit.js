class TeleclinicServiceDirEdit extends PureComponent {
    constructor(props) {
        super(props);

        this.bindThis([
            'onModalShown', 'onModalHidden'
        ]);
        this.state = {
            'form': this.newForm(),
            'img': '',
            'type': 'serviceDir',
        };
        this.model = new TeleclinicServiceModel();
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
            'form': this.newForm(),
            'img': ''
        });
    }

    newForm() {
        return {
            name: "",
            parentID: "",
            sort: "",
            thumbnail: ""
        }
    }

    previewImg(ev) {
        console.log(ev.target.files[0]);
        this.setPureState({'img': ev.target.files[0]});
    }

    // mở modal
    static open(serviceDir, parents) {

        TeleclinicServiceDirEdit.getInstance().then((instance) => {
            instance.resetState();
            instance.modal.showModal();

            instance.setPureState({
                'form': $.extend(instance.newForm(), serviceDir),
            });
        });
        return new Promise((done) => {
            TeleclinicServiceDirEdit.instance.done = done || new Function;
        });
    }

    doInsertServiceDir() {

        var data = $.extend({}, this.state.form);
        this.model.updateServiceDir(this.state.form["id"], data).then((resp) => {
            console.log('resp----------', resp)
            if (resp.status) {
                if (TeleclinicServiceDirEdit.instance.done)
                    TeleclinicServiceDirEdit.instance.done(resp);
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

    updateField(ev, key) {
        this.state.form[key] = ev.target.value;
        this.setPureState({form: this.state.form});
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

    handleSubmit(ev) {
        ev.preventDefault();
        App.showSprinner()
        let fileUpload = document.getElementById('file-upload')
        if (fileUpload.files[0] !== undefined) {
            this.model.uploadBanner(fileUpload.files[0],this.state.type).then((resp) => {
                if (resp.status == true) {
                    this.state.form.thumbnail = resp.data.path;
                    this.setPureState({form: this.state.form}, () => {
                        this.doInsertServiceDir();
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
            this.doInsertServiceDir();
        }
    }

    render() {
        return (
            <form onSubmit={(ev) => {
                this.handleSubmit(ev);
            }} ref={(elm) => {
                this.form = elm;
            }}>
                <Modal ref={(elm) => {
                    this.modal = elm;
                }} events={{
                    'modal.shown': () => {
                        document.getElementById('file-upload').value = '';
                        this.onModalShown()
                    },
                    'modal.hidden': this.onModalHidden
                }}>
                    <Modal.Header>{Lang.t('serviceDirEdit.header')}</Modal.Header>
                    <Modal.Body>
                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label"
                                   htmlFor="txt-name">{Lang.t('serviceDir.name')} <Require></Require></label>
                            <div className="col-sm-7">
                                <input required type="text" className="form-control" id="txt-name" ref={(elm) => {
                                    this.nameSite = elm;
                                }}
                                       value={this.state.form.name}
                                       onChange={(ev) => {
                                           this.updateField(ev, "name")
                                       }}
                                />
                            </div>
                        </div>

                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label"
                                   htmlFor="txt-sort">{Lang.t('serviceDir.sort')}</label>
                            <div className="col-sm-7">
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
                            <label className="col-sm-5 col-form-label control-label" htmlFor="file-sort">Upload
                                Banner</label>
                            <div className="col-sm-7">
                                <input type="file" id="file-upload" onChange={(ev)=>{this.previewImg(ev)}} />
                            </div>
                        </div>

                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label" htmlFor="file-sort"></label>
                            <div className="col-sm-7">
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