class LoginPage extends Component {

    constructor(props) {
        super(props);
        this.state = {
            'account': '',
            'password': '',
            'loadLang': false
        };
        Lang.load('companyui', 'user').then((resp) => {
            this.setState({
                'loadLang': resp
            });
        });
    }

    componentDidMount() {
    }

    generateCaptcha() {
        $("#captcha").attr("src", App.url('/rest/generateCaptcha?rand=' + Math.random()));
    }

    handleLogin(event) {

        var form = $('#frm-login');
        form.addClass('was-validated');

        event.preventDefault();
        if (form[0].checkValidity() === false) {
            return;
        }

        (new UserModel()).handleLogin('localdb', this.state.account, this.state.password, $("#entered-captcha").val())
            .then((resp) => {
                if (resp.status) {
                    // set site khi login
                    // localStorage.setItem('site', resp.data.user.siteFK);
                    window.location = App.siteUrl + '/home';
                } else {
                    if (resp.code == 0) {
                        onLoginFailed(Lang.t('loginPage.accountOrPassIncorrect'));
                    }
                    // code == 1: nhap sai tk/mk qua 3 lan => yeu cau nhap captcha
                    else if (resp.code == 1) {
                        onLoginFailed(Lang.t('loginPage.accountOrPassIncorrect'));
                        $("#captcha-block").removeClass("d-none");
                        this.generateCaptcha();


                    } else if (resp.code == 100) { // captcha sai
                        $("#captcha-block").removeClass("d-none");
                        this.generateCaptcha();
                        onLoginFailed(Lang.t('loginPage.captchaError'));
                    } else if (resp.code == 2) { //nhap sai 10 lan, bi khoa tk 1 phut
                        onLoginFailed(`${Lang.t('loginPage.accountLock')} ${resp.data.lockTime - moment().unix()} giây`);
                        $("#captcha-block").addClass("d-none");
                    }
                }
            }).catch((xhr) => {
                if (xhr.status == 404)
                    onLoginFailed(Lang.t('loginPage.accountOrPassIncorrect'));
                else
                    onLoginFailed();
            });

        function onLoginFailed(reason) {
            reason = reason || Lang.t('loginPage.loginFail');
            Alert.open(reason).then(() => {
                $('#txt-password').focus();
            });
        }
    }

    render() {
        return (
            <NoLayout>
                <meta name="viewport" content="width=device-width, initial-scale=1.0"></meta>
                {this.state.loadLang &&
                    <div className="layout bg-gradient-info" id="login-page">
                        <div className="container">
                            <div className="row full-height align-items-center view-port">
                                <div className="wrapper">
                                    <div className="login-a">
                                        <div className="logo">
                                            {/*<img className="logo-icon" src={App.themeUrl + '/images/logo-login-icon.png'} />*/}
                                            {/*<img className="logo-name" src={App.themeUrl + '/images/logo-pacs-name-icon.png'}/>*/}
                                            <img className="logo-name" src={App.themeUrl + '/images/logo_bvhm.png'}/>
                                        </div>
                                        {/* <div style="clear: both"></div> */}
                                    </div>
                                    <div className="card card-shadow card-login">
                                        <div className="card-body">
                                            <div className="p-h-5 p-v-5">
                                                <form id="frm-login" noValidate onSubmit={(event) => { this.handleLogin(event); }}>
                                                    <div className="form-group left-addon inner-addon">
                                                        <input type="text" className="form-control form-control-lg" placeholder={Lang.t('loginPage.placeholder.acc')} autoFocus required
                                                            onClick={(event) => { event.target.setSelectionRange(0, event.target.value.length) }}
                                                            onChange={(event) => { this.state.account = event.target.value; }} />
                                                        <i className="fa fa-user"></i>
                                                        <div className="invalid-tooltip">
                                                            {Lang.t('LoginPage.validate.account')}
                                                        </div>
                                                    </div>
                                                    <div className="form-group left-addon inner-addon">
                                                        <input type="password" id="txt-password" className="form-control form-control-lg" placeholder={Lang.t('loginPage.placeholder.pw')} required
                                                            onClick={(event) => { event.target.setSelectionRange(0, event.target.value.length) }}
                                                            onChange={(event) => { this.state.password = event.target.value; }} />
                                                        <i className="fa fa-lock"></i>
                                                        <div className="invalid-tooltip">
                                                            {Lang.t('LoginPage.validate.password')}
                                                        </div>
                                                        {/*<a href="#" className="link-forget-pass">{Lang.t('loginPage.forget.pass')}?</a>*/}
                                                    </div>
                                                    <div id="captcha-block" className="mt-3 text-center d-none">
                                                        <img id="captcha"/>
                                                        <div className="mt-4">
                                                            <div className="input-group mb-2">
                                                                <div className="input-group-prepend">
                                                                    <div className="btn btn-primary" onClick={(ev) => this.generateCaptcha()}><i className="fa fa-refresh" aria-hidden="true" style={{'lineHeight': '24px'}}/></div>
                                                                </div>
                                                                <input id="entered-captcha" type="text" className="form-control"
                                                                       placeholder="Nhập captcha"  style={{height: '40px', 'lineHeight': '40px'}}/>

                                                            </div>
                                                            {this.state.error &&
                                                            <p style={{color: "red"}}>{this.state.error}</p>
                                                            }
                                                        </div>
                                                    </div>

                                                    <button type="submit" className="btn-login-success btn btn-block btn-lg">
                                                        {Lang.t('LoginPage.button.login')}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="forget">
                                        <div className="support">
                                            <b> <i className="fa fa-phone-square"></i> {Lang.t('loginPage.support')}: <span className="phone">0967 645 444</span></b><br />
                                        </div>
                                    </div>
                                    {/*<div className="change-language">*/}
                                    {/*    <span className="fr-login-language"><a href="#">{Lang.t('loginPage.changeLanguage.en')}</a></span>*/}
                                    {/*    &nbsp;|&nbsp;*/}
                                    {/*        <span className="fr-login-language"><a href="#" className="active">{Lang.t('loginPage.changeLanguage.vi')}</a></span>*/}
                                    {/*</div>*/}
                                    {/*<div className="copy-right">*/}
                                    {/*        <span>© Copyright 2020, All Rights Reserved</span>*/}
                                    {/*</div>*/}
                                </div>
                            </div>
                        </div>
                    </div>}
            </NoLayout>
        );
    }
}

class PageContent extends LoginPage {

}

$(() => {
    ReactDOM.render(
        <NoLayout />,
        $('#root')[0]
    );
});