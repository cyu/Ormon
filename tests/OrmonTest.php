<?php

require_once 'Ormon.php';

class OrmonTest extends PHPUnit_Framework_TestCase {
    public function testCollectionize() {
        $this->assertEquals('posts', Ormon::collectionize('Post'));
        $this->assertEquals('post_comments', Ormon::collectionize('PostComment'));
    }
}