<?php

require_once 'DocumentModel.php';

Ormon::setMongo(new Mongo());
Ormon::setDefaultDatabaseName('ormon');

class Post extends DocumentModel {

    public function __construct() {
        $this->embedsList('comments', Comment);
    }
}

class Comment extends ormon\EmbeddedDocument {}

class CommentAuthor extends ormon\EmbeddedDocument {}