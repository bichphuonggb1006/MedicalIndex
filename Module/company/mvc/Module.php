<?php

namespace Company\MVC;

class Module extends \Macroable {

    protected static $masterComposerFile;
    protected static $modules = [];
    protected $name;
    protected $meta;

    /** @var ModuleMapper */
    protected $moduleMapper;

    static function getMasterComposer() {
        if (!static::$masterComposerFile) {
            static::$masterComposerFile = json_decode(file_get_contents(BASE_DIR . '/modules.json'), true);
        }
        return static::$masterComposerFile;
    }

    /**
     * 
     * @return array
     */
    static function getModules() {

        $masterComposer = static::getMasterComposer();
        foreach ($masterComposer['require'] as $module => $ver) {
            self::$modules[$module] = new Module($module);
        }

        return self::$modules;
    }

    /**
     * 
     * @param string $name
     * @return Module
     */
    static function getInstance($name) {
        if (!isset(static::$modules[$name])) {
            static::$modules[$name] = new static($name);
        }
        return static::$modules[$name];
    }

    function __construct($name) {
        $this->name = $name;
        $composer = $this->getmoduleDir() . '/composer.json';
        if (file_exists($composer)) {
            $this->meta = json_decode(file_get_contents($composer));
        }
    }

    function getMeta() {
        return $this->meta;
    }

    function getName() {
        return $this->name;
    }

    function getmoduleDir() {
        return BASE_DIR . "/Module/" . strtolower($this->name);
    }

    function executeInstallFile() {
        $file = $this->getmoduleDir() . '/install.php';

        if (file_exists($file)) {
            echo "\nInstalling Module {$this->name}\n";
            require $file;
        }
    }

    function getConstructPath() {
        $composerJSON = $this->getmoduleDir() . '/modules.json';
        $construct = $this->getmoduleDir() . '/construct.php';
        if (!file_exists($construct)) {
            return false;
        }

        return $construct;
    }

    /**
     * h??m kh???i t???o CSDL l???n ?????u c??i module<br>
     * c??i c??c file sql
     */
    function initDatabase() {
        //scan c??c file *.table.sql
        $dir = $this->getmoduleDir() . '/sql';
        if (!file_exists($dir)) {
            return;
        }
        $items = scandir($dir);
        //sort by name
        sort($items);
        foreach ($items as $item) {
            if (strpos($item, '.table.sql') === false) {
                continue;
            }
            $tableName = preg_replace('/^[0-9]+\./', '', str_replace(".table.sql", "", $item));
            $path = $dir . '/' . $item;
            $db = $result = ModuleMapper::makeInstance()->db;
            
            //check b???ng t???n t???i, n???u ch??a th?? t???o
            if (!$db->tableExists($tableName) && !$db->importSQL($path)) {
                throw new \Exception("SQL import fail, file=$path, msg=" . $db->ErrorMsg());
            }
            
        }
    }

    /**
     * Kh???i t???o b???n ghi ??? b???ng system_module
     */
    function checkExistsOrCreateModuleRecord() {
        if(!$this->meta) {
            return; //not a application module
        }

        if (!ModuleMapper::makeInstance()->filterID($this->name)->isExists()) {
            ModuleMapper::makeInstance()->startTrans();
            ModuleMapper::makeInstance()->insert([
                'id' => $this->name,
                'version' => $this->meta->version,
                'desc' => $this->meta->description
            ]);
            ModuleMapper::makeInstance()->completeTransOrFail();
        }
    }

    /**
     * L???y phi??n b???n hi???n t???i trong module
     * @return string phi??n b???n hi???n t???i c???a module
     */
    function getDBVersion() {
        return ModuleMapper::makeInstance()
                        ->makeInstance()
                        ->filterID($this->name)
                        ->getEntity()->version;
    }

    /**
     * So s??nh 2 phi??n b???n v???i nhau
     * @param string $a
     * @param string $b
     * @return bool $a > $b == true
     */
    function compareVersion($a, $b) {
        //so s??nh t???ng c???p
        $a = explode('.', $a);
        $b = explode('.', $b);
        $loop = max([count($a), count($b)]);
        for ($i = 0; $i < $loop; $i++) {
            $aPart = (int) arrData($a, $i, 0);
            $bPart = (int) arrData($b, $i, 0);
            if ($aPart != $bPart) {
                return $aPart > $bPart;
            }
        }
    }

    function getPublicURL() {
        return url('/modules/' . $this->name . '/public');
    }

    function getBabelURL($path = '') {
        return url('/modules/' . $this->name . '/public/babelLoader?path=' . $path);
    }

}
