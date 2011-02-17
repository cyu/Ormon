<?php

require_once 'DocumentModel.php';

Ormon::setMongo(new Mongo());
Ormon::setDefaultDatabaseName('ormon');

class Post extends DocumentModel {

    public function __construct(array $data = null) {
        $this->embedsList('comments', Comment);
        parent::__construct($data);
    }
}

class Comment extends ormon\EmbeddedDocument {

    public function __construct(array $data = null) {
        $this->embedsObject('author', CommentAuthor);
        parent::__construct($data);
    }
}

class CommentAuthor extends ormon\EmbeddedDocument {}