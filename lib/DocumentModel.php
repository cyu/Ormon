<?php

require_once 'Ormon.php';
require_once 'ormon/document.php';
require_once 'ormon/associations.php';

class DocumentModel extends ormon\DocumentObject {
    private static $collectionNames = array();

    public function getId() {
        return $this->_id;
    }

    public function save() {
        $doc = $this->asDoc();
        if (empty($doc)) return false;
        if (isset($doc['_id'])) {
            self::getCollection()->update($doc);
        } else {
            self::getCollection()->insert($doc);
            $this->data['_id'] = $doc['_id'];
        }
        return true;
    }

    public static function getCollection() {
        $db = Ormon::getDefaultDatabase();
        $collectionName = self::getCollectionName();
        return $db->$collectionName;
    }

    public static function getCollectionName() {
        $className = get_called_class();
        if (!isset(self::$collectionNames[$className])) {
            $collectionName = Ormon::collectionize($className);
            self::$collectionNames[$className] = $collectionName;
        } else {
            $collectionName = self::$collectionNames[$className];
        }
        return $collectionName;
    }

    public static function findOne($criteria = array()) {
        if ( is_a($criteria, 'MongoId') ) {
            $criteria = array('_id' => $criteria);
        }
        $doc = self::getCollection()->findOne($criteria);
        if (!empty($doc)) {
            $DocumentClass = get_called_class();
            $document = new $DocumentClass();
            $document->loadData($doc);
        }
        return $document;
    }
}