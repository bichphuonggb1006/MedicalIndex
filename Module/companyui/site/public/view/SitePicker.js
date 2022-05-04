class SitePicker extends Component {
    constructor(props) {
        super(props);
        this.state = {
            'modalOptions': {},
            'selected': [],
            'notSites': []
        };
        // Lang.load('companyui', 'user');
    }

    static open(opts) {
        SitePicker.getInstance().then((instance) => {
            opts = opts || {};
            opts.notSites = opts.notSites || [];
            instance.setState({ 'modalOptions': opts });
            instance.modal.showModal();

            // load lại danh sách site
            if (instance.siteListComp) {
                // set lại giá trị mặc định cho state
                instance.siteListComp.setDefaultState();
                // lấy lại danh sách site
                instance.siteListComp.getSites();
            }
        });

        return new Promise((done, fail) => {
            SitePicker.done = done;
            SitePicker.catch = fail;
        });
    }

    handleSubmit(selectedOverride) {
        SitePicker.done(selectedOverride || this.state.selected);
        this.modal.hideModal();
    }

    render() {
        return (
            <Modal ref={(elm) => { this.modal = elm; }} size="modal-lg">
                <Modal.Header>Chọn site</Modal.Header>
                <Modal.Body>
                    <SiteList
                        modal={this.state.modalOptions}
                        ref={(elm) => { this.siteListComp = elm; }}
                        events={{
                            'update': () => {
                                if (this.siteListComp)
                                    this.setState({ 'selected': this.siteListComp.getSelectedSites(true) });
                            }
                        }}
                    />
                </Modal.Body>
                <Modal.Footer>
                    <button type="button" className="btn btn-primary" disabled={this.state.selected.length ? false : true} onClick={() => { this.handleSubmit(); }}>Chọn</button>
                    <button type="button" className="btn btn-secondary close-modal" onClick={() => { this.modal.hideModal(); }}>Hủy</button>
                </Modal.Footer>
            </Modal>
        );
    }
}