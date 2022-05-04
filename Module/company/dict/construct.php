<?php

namespace Company\Dict;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;

/**
 * Collection route
 */
$colCtrl = "\\Company\\Dict\\Controller\\CollectionCtrl";
$itemCtrl = "\\Company\\Dict\\Controller\\ItemCtrl";
if (R::getInstance()->requestUriHas('collection')) {
    R::getInstance()->addRoute(new MVC('/rest/collections(/:id)', 'POST,PUT', $colCtrl, "updateCollection"));
    R::getInstance()->addRoute(new MVC('/rest/collections/:id', 'GET', $colCtrl, "getCollection"));
    R::getInstance()->addRoute(new MVC('/rest/collections', 'GET', $colCtrl, "getCollections"));
    R::getInstance()->addRoute(new MVC('/rest/collections/:id', 'DELETE', $colCtrl, "deleteCollection"));

    /**
     * Item route
     */
    R::getInstance()->addRoute(new MVC('/rest/collections/:collectionID/items(/:itemID)', 'POST,PUT', $itemCtrl, "updateItem"));
    R::getInstance()->addRoute(new MVC('/rest/collections/:collectionID/items', 'GET', $itemCtrl, "getItems"));
    R::getInstance()->addRoute(new MVC('/rest/collections/:collectionID/items/:itemID', 'GET', $itemCtrl, "getItem"));
    R::getInstance()->addRoute(new MVC('/rest/collections/:collectionID/items/:itemID', 'DELETE', $itemCtrl, "deleteItem"));
}
