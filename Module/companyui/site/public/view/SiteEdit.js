class SiteEdit extends PureComponent {
    constructor(props) {
        super(props);
        this.bindThis([
            'onModalShown', 'onModalHidden'
        ]);
        this.siteModel = new SiteModel;
        this.state = {
            'form': this.newSite(),
            'img':'',
            'provinces': [],
            'districts': [],
            'wards': [],
            'type': 'Site',
        };
        this.siteModel.getProvinces().then((provinces) => {
            this.setState({'provinces': provinces})
        });

    }

    resetState() {
        this.setState({
            'form': this.newSite(),
            'img': ''
        });
    }

    previewImg(ev) {
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
    static open(site) {
        SiteEdit.getInstance().then((instance) => {
            instance.resetState();
            instance.modal.showModal();
            var newState = {
                'form': $.extend(instance.newSite(), site)
            }
            if(site != undefined) {
                let getDistricts = instance.siteModel.getDistricts(site.province)
                let getWards = instance.siteModel.getWards(site.district)
                Promise.all([getDistricts, getWards]).then((resp) => {
                    newState.districts = resp[0];
                    newState.wards = resp[1];
                    //setState
                    instance.setPureState(newState);
                })
            } else {
                //set state
                instance.setPureState(newState);
            }

        });
        return new Promise((done) => {
            SiteEdit.instance.done = done || new Function;
        });
    }

    newSite() {
        return {
            'id': 0,
            'name': '',
            'shortName': '',
            'active': 1,
            'province':'',
            'district':'',
            'ward':'',
            'address': '',
            'phone': '',
            'description': '',
            'location':'',
            'thumbnail':''
        };
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

    doInsertSite() {
        var data = $.extend({}, this.state.form);
        var form = $(this.form);
        if (form[0].checkValidity() === false) {
            $(form).addClass('was-validated');
            return;
        }
        this.siteModel.updateSite(this.state.form.id, data).then((resp) => {
            if (resp.status) {
                if (SiteEdit.instance.done)
                    SiteEdit.instance.done(resp);
                window.location.reload();
                //this.modal.hideModal();
            }
            App.hideSprinner()
        }).catch((xhr) => {
            if (this.editFail)
                this.editFail(xhr);
            App.hideSprinner()
        });
    }

    updateSite(ev, key) {
        this.state.form[key] = ev.target.value;
        if(key == 'province' && this.state.form[key] != ''){

            this.siteModel.getDistricts(this.state.form.province).then((districts) => {
                this.setState({'districts': districts})
            });
        }
        if(key == 'district' && this.state.form[key] != ''){
            this.siteModel.getWards(this.state.form.district).then((wards) => {
                this.setState({'wards': wards})
            });
        }
        if(key == 'province' && this.state.form.province == ''){
            this.setState({'districts': []})
            this.setState({'wards': []})
        }
        if(key == 'district' && this.state.form.district == ''){
            this.setState({'wards': []})
        }
        this.setPureState({form: this.state.form});
    }

    // ghi lại
    handleSubmit(ev) {
        ev.preventDefault();
        App.showSprinner()
        let fileUpload = document.getElementById('file-upload')
        if (fileUpload.files[0] !== undefined) {
            this.siteModel.uploadBanner(fileUpload.files[0],this.state.type).then((resp) => {
                if (resp.status == true) {
                    this.state.form.thumbnail = resp.data.path;
                    this.setPureState({form: this.state.form}, () => {
                        this.doInsertSite();
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
            this.doInsertSite();
        }
    }

    handleChangeCheckbox(checked) {
        this.state.form.active = checked;
        this.setPureState({ form: this.state.form });
    }

    render() {
        return (
            <form onSubmit={(ev) => { this.handleSubmit(ev); }} ref={(elm) => { this.form = elm; }} >
                <Modal id="modal-site-edit" ref={(elm) => { this.modal = elm; }} events={{
                    'modal.shown': this.onModalShown,
                    'modal.hidden': this.onModalHidden
                }}>
                    <Modal.Header>{Lang.t('site.header')}</Modal.Header>
                    <Modal.Body>
                        <div className="form-group row">
                            <label className="col-sm-3 col-form-label control-label" htmlFor="txt-login">{Lang.t('site.name')} site <Require /></label>
                            <div className="col-sm-9">
                                <input type="text" className="form-control" id="txt-nameSite" ref={(elm) => { this.nameSite = elm; }}
                                    required
                                    value={this.state.form.name}
                                    onChange={(ev) => { this.state.form.name = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                />
                                <div className="invalid-tooltip">
                                    {Lang.t('site.validateName')}
                                </div>
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-3 col-form-label control-label" htmlFor="txt-login">{Lang.t('site.phone')}</label>
                            <div className="col-sm-9">
                                <input type="text" className="form-control" id="txt-phone-site"
                                       value={this.state.form.phone}
                                       onChange={(ev) => { this.state.form.phone = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                />
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-3 col-form-label control-label" htmlFor="txt-login">{Lang.t('site.address')} <Require /></label>
                            <div className="col-sm-3">
                                <select className="form-control input-group right" id="sel-provinces" value={this.state.form.province || ''}
                                        onChange={(ev) =>{ this.updateSite(ev, "province") }} required >
                                    <option  value="">-- Tỉnh/thành phố --</option>
                                    {
                                        this.state.provinces.map((province) => {
                                            return <option key={province.id} value={province.id}>{province.name}</option>
                                        })
                                    }
                                </select>
                            </div>
                            <div className="col-sm-3">
                                <select className="form-control input-group right" id="sel-districts" value={this.state.form.district || ''}
                                        onChange={(ev) =>
                                        { this.updateSite(ev, "district") }} required >
                                    <option  value="">-- Quận/huyện --</option>
                                    {
                                        this.state.districts.map((district) => {
                                            return <option key={district.id} value={district.id}>{district.name}</option>
                                        })
                                    }
                                </select>
                            </div>
                            <div className="col-sm-3">
                                <select className="form-control input-group right" id="sel-wards" value={this.state.form.ward || ''}
                                        onChange={(ev) =>
                                        { this.updateSite(ev, "ward") }} required >
                                    <option  value="">-- Phường/xã --</option>
                                    {
                                        this.state.wards.map((ward) => {
                                            return <option key={ward.id} value={ward.id}>{ward.name}</option>
                                        })
                                    }
                                </select>
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-3 col-form-label control-label" htmlFor="txt-login">Địa chỉ chi tiết <Require /></label>
                            <div className="col-sm-9">
                                <input type="text" className="form-control" id="txt-address-site"
                                       value={this.state.form.address}
                                       onChange={(ev) => { this.state.form.address = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                       required
                                />
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-3 col-form-label control-label" htmlFor="txt-login">Địa chỉ google map <Require /></label>
                            <div className="col-sm-9">
                                <input type="text" className="form-control" id="txt-address-site"
                                       value={this.state.form.location}
                                       onChange={(ev) => { this.state.form.location = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                       required
                                />
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-3 col-form-label control-label" htmlFor="txt-login">Thông tin giới thiệu</label>
                            <div className="col-sm-9">
                                <textarea type="text" className="form-control" id="txt-phone-site" style={{minHeight: 100,resize:"none"}}
                                          value={this.state.form.description}
                                          onChange={(ev) => { this.state.form.description = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                />
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-3 col-form-label control-label" htmlFor="chk-site-status">{Lang.t('site.sttAction')}</label>
                            <div className="col-sm-9">
                                <CheckBox id="chk-site-status"
                                          checked={this.state.form.active}
                                          onChange={(checked) => { this.handleChangeCheckbox(checked); }}
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
                        <button type="submit" className="btn btn-primary">{Lang.t('site.btnSave')}</button>
                        <button type="button" className="btn btn-secondary" data-dismiss="modal">{Lang.t('site.btnClose')}</button>
                    </Modal.Footer>
                </Modal>
            </form>
        );
    }
}