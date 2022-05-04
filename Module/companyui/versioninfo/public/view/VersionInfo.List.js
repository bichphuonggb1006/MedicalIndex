VersionInfo.List = class VersionInfoList extends PureComponent {
    constructor(props) {
        super(props);

        this.state = {
            'versionInfo': ''
        };
        this.versionInfoModel = new VersionInfo.Model;
        Lang.load('companyui', 'versionInfo').then(() => {
            this.setState({ 'readyToRender': true });
        });
    }

    componentWillMount() {
        App.Component.trigger('leftNav.active', 'versionInfo');
        this.getVersionInfo();
    }

    //lấy danh sách máy chụp
    getVersionInfo() {
        return new Promise((done) => {
            this.versionInfoModel.getVersionInfo().then((resp) => {
                this.setPureState({ 'versionInfo': resp.rows }, () => {
                    if (!resp.rows.length) {
                        resp.rows = [];
                    }
                    done();
                });
            });
        });
    }

    pageContent() {
        return (
            <div>
               <div dangerouslySetInnerHTML={{ __html: this.state.versionInfo }} />
            </div>
        );
    }

    render() {
        return (
            <AdminLayout>
                <PageHeader>{Lang.t('versionInfo.header')}</PageHeader>
                <div className="card card-version">
                    <div className="card-body">
                        {this.pageContent()}
                    </div>
                </div>
            </AdminLayout>
        );
    }
}