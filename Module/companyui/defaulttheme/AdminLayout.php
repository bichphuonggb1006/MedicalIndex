<?php

namespace CompanyUI\DefaultTheme;

use Company\MVC\Layout;
use Company\MVC\Module;
use Company\MVC\Theme;

class AdminLayout extends \Company\MVC\Layout
{

    protected $title = "Cores";
    function init()
    {
        parent::init();

        $css = ['/vendor/bootstrap/dist/css/bootstrap.css', '/vendor/perfect-scrollbar/css/perfect-scrollbar.min.css', '/node_modules/font-awesome/css/font-awesome.min.css', '/css/themify-icons.css', '/css/materialdesignicons.min.css', '/vendor/selectize/dist/css/selectize.default.css', '/vendor/summernote/dist/summernote-bs4.css', '/vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css', '/css/jquery.toast.min.css', //'/css/animate.min.css',
            '/css/app.css', '/css/custom.css', '/css/chosen.min.css'];
        $module = new Module('companyui/defaulttheme');
        foreach ($css as $file) {
            $this->addCSS($module->getPublicURL() . $file);
        }

        $js = [//nếu chế độ opimize thì dùng production build
            app()->config['production'] ? '/js/react.production.min.js' : '/js/react.development.js', app()->config['production'] ? '/js/react-dom.production.min.js' : '/js/react-dom.development.js', // '/js/browser.min.js',
            '/js/vendor.js', //    '/js/jquery-2.2.4.min.js',
            //  '/js/bootstrap.min.js',
            '/vendor/moment/min/moment.min.js', '/js/jquery.toast.min.js', '/vendor/selectize/dist/js/standalone/selectize.min.js', '/vendor/summernote/dist/summernote-bs4.min.js', '/vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.js', '/js/app.min.js', '/js/chosen.jquery.min.js', '/js/jquery.ajax_upload.js', '/js/jquery.validate.min.js', '/js/babelLoader.js'];
        foreach ($js as $file) {
            $this->addJS($module->getPublicURL() . $file);
        }

        $this->loadUiModule(new \CompanyUI\BaseComponent\UiLoader());
        $babel = ['autoload.json'];
        foreach ($babel as $file) {
            $this->addJS($module->getBabelURL($file));
        }

        $this->loadUiModule(new \CompanyUI\User\UiLoader());
        $this->loadUiModule(new \CompanyUI\Module\UiLoader());
        $this->loadUiModule(new \CompanyUI\Site\UiLoader());
        $this->loadUiModule(new \CompanyUI\Setting\UiLoader());
        $this->loadUiModule(new \CompanyUI\Telehealthservice\UiLoader());
        $this->loadUiModule(new \Teleclinic\Teleclinic\UiLoader());
        $this->loadUiModule(new \Payment\BASE\UiLoader());
        $this->loadUiModule(new \Payment\VNPAY\UiLoader());
    }

    protected function renderLayout($content)
    {
        $module = Module::getInstance('companyui/defaulttheme');
        ?>
        <html>
        <head>
            <meta charset="utf-8">
            <title><?php echo $this->title ?></title>
            <?php $this->genStyleTags() ?>
            <?php
            /* Load OEM */
            $baseDir = BASE_DIR . "/Config/OEM";
            if (file_exists($baseDir)) {
                $folder1 = $baseDir . "/default1";
                $folder2 = $baseDir . "/default2";

                $headers = getallheaders();
                $host = arrData($headers, "Host");
                list($domain, $port) = explode(":", $host);
                $folder3 = $baseDir . "/" . $domain;

                $logoIco = "";
                if (strlen($domain) && file_exists($folder3 . "/images/logo.ico")) {
                    $logoIco = $folder3 . "/images/logo.ico";
                } elseif (file_exists($folder2 . "/images/logo.ico")) {
                    $logoIco = $folder2 . "/images/logo.ico";
                } elseif (file_exists($folder1 . "/images/logo.ico")) {
                    $logoIco = $folder1 . "/images/logo.ico";
                }

                $dataB64 = !file_get_contents($logoIco) ? "" : base64_encode(file_get_contents($logoIco));
            }
            ?>

            <?php if (strlen($dataB64)): ?>
                <link href="data:image/x-icon;base64,<?php echo $dataB64; ?>" rel="shortcut icon" type="image/x-icon">
            <?php else: ?>
                <link rel="icon" type="image/png"
                      href="<?php echo($module->getPublicURL() . '/images/logo_minerva.ico'); ?>"/>
            <?php endif; ?>

        </head>
        <body class="<?php echo str_replace("/", " ", $_SERVER['REQUEST_URI']) ?>">
        <div id="loading-sprinner-global" class="sprinner-global hidden overlay">
            <i class="fa fa-spinner fa-spin fa-3x fa-fw" style=" color: #fff; font-size: 50px"></i>
            <span class="sr-only">Loading...</span>
        </div>
        <script>
            App = window.App || {};
            App.showSprinner = function (){
                document.getElementById('loading-sprinner-global').style.display='flex';
            }
            App.hideSprinner = function (){
                document.getElementById('loading-sprinner-global').style.display='none';
            }

            App.siteUrl = <?php echo json_encode(url()) ?>;
            App.user = <?php echo json_encode(\Company\Auth\Auth::getInstance()->getUser()); ?>;
            App.themeUrl = <?php echo json_encode($module->getPublicURL()); ?>;
            App.enviroment = <?php echo json_encode(app()->config['enviroment']) ?>;
            App.production = <?php echo json_encode(app()->config['production']) ?>;
            App.siteID = <?php echo json_encode($this->siteID) ?>;
            App.isFullControl =  <?php echo json_encode(\Company\Auth\Auth::getInstance()->isFullControl()); ?>;
            //App.token =  <?php //\Company\Auth\Auth::generateCrsfToken(); echo json_encode($_SESSION['token']); ?>//;
        </script>
        <?php $this->genScriptTags() ?>
        <div id="root">
            <h1 style="text-align: center; padding-top: 20px;">Đang tải...</h1>
        </div>
        <?php echo $content ?>

        <?php
        /* Load OEM */
        $baseDir = BASE_DIR . "/Config/OEM";
        if (file_exists($baseDir)) {
            $folder1 = $baseDir . "/default1";
            $folder2 = $baseDir . "/default2";

            $headers = getallheaders();
            $host = arrData($headers, "Host");
            list($domain, $port) = explode(":", $host);
            $folder3 = $baseDir . "/" . $domain;

            $imagesFolder = "";
            if (strlen($domain) && file_exists($folder3 . "/css/customize.css")) {
                $imagesFolder = file_exists($folder3 . "/images") ? $folder3 . "/images" : "";
            } elseif (file_exists($folder2 . "/css/customize.css")) {
                $imagesFolder = file_exists($folder2 . "/images") ? $folder2 . "/images" : "";
            } elseif (file_exists($folder1 . "/css/customize.css")) {
                $imagesFolder = file_exists($folder1 . "/images") ? $folder1 . "/images" : "";
            }

            /* scan thu muc images de truyen bien php vao css OEM */
            if (strlen($imagesFolder) && file_exists($imagesFolder)) {
                $files = getFilesFromPath($imagesFolder);
                foreach ($files as $file) {
                    $paths = pathinfo($file);
                    $fname = tiengVietKhongDau(arrData($paths, 'filename'));
                    $ext = arrData($paths, 'extension');
                    $fname = str_replace(' ', '', $fname);
                    $fname = preg_replace('/[^A-Za-z0-9]/', '', $fname);
                    $fnameVar = $fname . $ext;
                    $$fnameVar = base64_encode(file_get_contents($file));
                }
            }
        }
        ?>

        <?php if (strlen($domain) && file_exists($folder3 . "/css/customize.css")): ?>
            <style><?php require $folder3 . "/css/customize.css"; ?></style>
        <?php elseif (file_exists($folder2 . "/css/customize.css")) : ?>
            <style><?php require $folder2 . "/css/customize.css"; ?></style>
        <?php elseif (file_exists($folder1 . "/css/customize.css")): ?>
            <style><?php require $folder1 . "/css/customize.css"; ?></style>
        <?php endif; ?>
        </body>
        </html>
        <?php
    }

    public function getTheme()
    {
        return Theme::getTheme('default');
    }

    public function getName()
    {
        return 'admin';
    }

    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }
}
