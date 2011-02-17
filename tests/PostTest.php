<?php

require_once 'Post.php';

class PostTest extends PHPUnit_Framework_TestCase {

    public function testGetCollectionName() {
        $this->assertEquals('posts', Post::getCollectionName());
    }

    public function testBasics() {
        $post = new Post();
        $post->title = 'My Test Post';
        $post->save();

        $found = Post::findOne(array('title' => 'My Test Post'));
        $this->assertNotNull($found);
        $this->assertTrue(is_a($post->getId(), 'MongoId'));
        $this->assertTrue(isset($found->title));
        $this->assertEquals('My Test Post', $found->title);
    }

    public function testComments() {
        $post = new Post();
        $comments = $post->comments;
        $this->assertNotNull($comments);
        $this->assertTrue(is_a($comments, 'ormon\EmbeddedList'));

        $author = new CommentAuthor(array(
            'name' => 'Calvin Yu',
            'email' => 'csyu77@gmail.com'));
        $comments->push(new Comment(array(
            'author' => $author,
            'text' => 'This is a comment')));

        $this->assertTrue($post->save());

        $id = $post->getId();
        $this->assertTrue(is_a($post->getId(), 'MongoId'));
        $found = $post->findOne($id);
        $comment = $post->comments->first();
        $this->assertNotNull($comment);
        $this->assertEquals('This is a comment', $comment->text);

        $author = $comment->author;
        $this->assertNotNull($author);
        $this->assertEquals('Calvin Yu', $author->name);
    }

}