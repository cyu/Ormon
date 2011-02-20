<?php

namespace ormon;

interface Association extends DocumentNode  {
    public function applyUpdates(DocumentUpdates $updates);
    public function loadData(array $data);
}

class EmbeddedList implements Association {
    private $modelClass;
    private $data = array();

    public function __construct($modelClass = null) {
        $this->modelClass = $modelClass;
    }

    public function __isset($name) {
        if ($name == 'count' ||
                ($name == 'first' && !empty($this->data))) {
            return true;
        }
    }

    public function __get($name) {
        if ($name == 'first') {
            return empty($this->data) ? null : $this->data[0];
        }
        if ($name == 'count') {
            return count($this->data);
        }
    }

    public function push($value) {
        $this->data[] = $value;
    }

    public function get($index) {
        return $this->data[$index];
    }

    public function applyUpdates(DocumentUpdates $updates) {
        foreach ($this->data as $val) {
            
        }
    }

    public function asDoc() {
        $doc = array();
        foreach ($this->data as $v) {
            $v = DocumentObject::asDocValue($v);
            if (!empty($v)) $doc[] = $v;
        }
        return empty($doc) ? null : $doc;
    }

    public function loadData(array $data) {
        foreach ($data as $v) {
            if (isset($this->modelClass)) {
                if ($v instanceof $this->modelClass) {
                    $this->data[] = $v;
                } else {
                    $this->data[] = new $this->modelClass($v);
                }
            } else {
                $this->data[] = $v;
            }
        }
    }
}

class EmbeddedDocument extends DocumentObject implements Association {
}
