<?php

namespace ormon;

interface DocumentNode {
    public function asDoc();
}

class DocumentObject implements DocumentNode {
    protected $data = array();

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
    }

    public function __toString() {
        return json_encode($this->asDoc());
    }

    public function loadData($data) {
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