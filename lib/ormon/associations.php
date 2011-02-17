<?php

namespace ormon;

interface Association extends DocumentNode  {
    public function loadData($data);
}

class EmbeddedList implements Association {
    private $modelClass;
    private $data = array();

    public function __construct($modelClass = null) {
        $this->modelClass = $modelClass;
    }

    public function push($value) {
        $this->data[] = $value;
    }

    public function first() {
        return $this->data[0];
    }

    public function asDoc() {
        $doc = array();
        foreach ($this->data as $v) {
            $v = DocumentObject::asDocValue($v);
            if (!empty($v)) $doc[] = $v;
        }
        return empty($doc) ? null : $doc;
    }

    public function loadData($data) {
        foreach ($data as $v) {
            if (isset($this->modelClass)) {
                $this->data[] = new $this->modelClass($v);
            } else {
                $this->data[] = $v;
            }
        }
    }
}

class EmbeddedDocument extends DocumentObject implements Association {
}
