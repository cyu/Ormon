<?php

namespace ormon;

interface DocumentNode {
    public function asDoc();
}

class DocumentObject implements DocumentNode {
    protected $data = array();

    public function __construct(array $data = array()) {
        $this->data = $data;
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

    public function loadData($data) {
        foreach ($data as $k => $v) {
            if (isset($this->data[$k])) {
                $cur = $this->data[$k];
                if (is_object($cur) && ($cur instanceof ormon\Association)) {
                    $cur->loadData($v);
                } else {
                    $this->data[$k] = $v;
                }
            } else {
                $this->data[$k] = $v;
            }
        }
    }

    public function asDoc() {
        $doc = array();
        foreach ($this->data as $k => $v) {
            $v = self::asDocValue($v);
            if (is_object($v)) throw new \Exception("cannot serialize $k value: ".get_class($v));
            if (!empty($v)) $doc[$k] = $v;
        }
        return $doc;
    }

    public static function asDocValue($v) {
        return is_object($v) && ($v instanceof DocumentNode) ? $v->asDoc() : $v;
    }

}