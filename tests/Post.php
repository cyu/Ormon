<?php

require_once 'Ormon.php';

Ormon::setMongo(new Mongo());
Ormon::setDefaultDatabaseName('ormon');

class Post extends ormon\DocumentModel {
    public function __construct(array $data = null) {
        $this->embedsObject('author', Author);
        $this->embedsList('comments', Comment);
        parent::__construct($data);
    }
}

class Comment extends ormon\EmbeddedDocument {

    public function __construct(array $data = null) {
        $this->embedsObject('author', Author);
        parent::__construct($data);
    }
}

class Author extends ormon\EmbeddedDocument {}