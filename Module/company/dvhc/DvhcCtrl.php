<?php

namespace Company\Dvhc;

use Company\MVC\Controller;

class DvhcCtrl extends Controller {
    function getAll() {
        $rows = DvhcModel::makeInstance()
            ->filterLevel($this->req->get('level'))
            ->filterParentID($this->req->get('parentID'))
            ->getEntities();
        $this->outputJSON($rows);
    }
}