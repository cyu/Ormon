<?php

namespace ormon;

interface DocumentNode {
    public function asDoc();
}

class DocumentObject implements DocumentNode {
    protected $data = array();
    protected $dirty;

    public function __construct(array $data = null) {
        if (isset($data)) $this->loadData($data);
    }

    public function __get($name) {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    public function __isset($name) {
        return isset($this->data[$name]);
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
        $this->dirty[] = $name;
    }

    public function __toString() {
        return json_encode($this->asDoc());
    }

    public function loadData(array $data) {
        foreach ($data as $k => $v) {
            if (isset($this->data[$k])) {
                $cur = $this->data[$k];

                if (is_object($cur) && ($cur instanceof Association)) {
                    $cur->loadData($v);

                } else if (is_object($cur) && ($cur instanceof ObjectPlaceholder)) {
                    if (is_object($v)) {
                        $modelClass = $cur->getModelClass();
                        if ($v instanceof $modelClass) {
                            $this->data[$k] = $v;
                        }
                    } else {
                        $obj = $cur->create();
                        $obj->loadData($v);
                        $this->data[$k] = $obj;
                    }

                } else {
                    $this->data[$k] = $v;
                }

            } else {
                $this->data[$k] = $v;
            }
        }
    }

    public function asDoc() {
        foreach ($this->data as $k => $v) {
            $v = self::asDocValue($v);
            if (is_object($v)) throw new \Exception("cannot serialize $k value: ".get_class($v));
            if (!empty($v)) $doc[$k] = $v;
        }
        return $doc;
    }

    public function applyUpdates(DocumentUpdates $updates) {
        if (isset($this->dirty)) {
            foreach ($this->dirty as $name) {
                if (isset($this->data[$name])) {
                    $value = $this->data[$name];
                    if ($value instanceof Association) {
                        $value->applyUpdates($updates);
                    } else {
                        $updates->set($name, $value);
                    }
                } else {
                    $updates->unset_value($name);
                }
            }
        }
    }

    public static function asDocValue($v) {
        if (is_object($v)) {
            if ($v instanceof DocumentNode) return $v->asDoc();
            if ($v instanceof ObjectPlaceholder) return null;
        }
        return $v;
    }

    protected function embedsList($name, $modelClass = null) {
        $this->data[$name] = new EmbeddedList($modelClass);
    }

    protected function embedsObject($name, $modelClass) {
        $this->data[$name] = new ObjectPlaceholder($modelClass);
    }

}

class ObjectPlaceholder {
    private $modelClass;
    public function __construct($modelClass) {
        $this->modelClass = $modelClass;
    }

    public function create() {
        return new $this->modelClass();
    }

    public function getModelClass() {
        return $this->modelClass;
    }
}

class DocumentModel extends DocumentObject {
    private static $collectionNames = array();

    private $fieldProjection = false;

    public function getId() {
        return $this->_id;
    }

    public function save() {
        $doc = $this->asDoc();

        if (empty($doc)) {
            return false;
        }

        if (isset($doc['_id'])) {
            $docId = $doc['_id'];

            if ($this->fieldProjection) {
                // project used, don't do a full doc update.
                $updates = new DocumentUpdates();
                $this->applyUpdates($updates);
                $data = $updates->data;

            } else {
                unset($doc['_id']);
                $data = $doc;
            }

            if (!empty($data)) {
                self::getCollection()->update(array('_id' => $docId), $data);
            }
        } else {
            self::getCollection()->insert($doc);
            $this->data['_id'] = $doc['_id'];
        }
        return true;
    }

    public static function getCollection() {
        $db = \Ormon::getDefaultDatabase();
        $collectionName = self::getCollectionName();
        return $db->$collectionName;
    }

    public static function getCollectionName() {
        $className = get_called_class();
        if (!isset(self::$collectionNames[$className])) {
            $collectionName = \Ormon::collectionize($className);
            self::$collectionNames[$className] = $collectionName;
        } else {
            $collectionName = self::$collectionNames[$className];
        }
        return $collectionName;
    }

    public static function findOne($criteria = array(), $fields = array()) {
        if ( !is_array($criteria) ) {
            $criteria = array('_id' => $criteria);
        }
        $doc = self::getCollection()->findOne($criteria, $fields);
        if (!empty($doc)) {
            $DocumentClass = get_called_class();
            $document = new $DocumentClass();
            $document->fieldProjection = !empty($fields);
            $document->loadData($doc);
        }
        return $document;
    }
}

class DocumentUpdates {
    public $data = array();

    public function set($name, $value) {
        $this->op('set', $name, $value);
    }
    public function unset_value($name) {
        $this->op('unset', $name, 1);
    }

    private function op($op, $name, $value) {
        $op = '$'.$op;
        if (isset($this->data[$op])) {
            $this->data[$op][$name] = $value;
        } else {
            $this->data[$op] = array($name => $value);
        }
    }
}