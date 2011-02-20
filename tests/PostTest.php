<?php

require_once 'Post.php';

class PostTest extends PHPUnit_Framework_TestCase {

    private static $collection;

    public static function setUpBeforeClass() {
        self::$collection = Ormon::getDefaultDatabase()->posts;
        self::$collection->remove(array());
        self::$collection->insert(array(
            '_id' => 1,
            'title' => 'The Ultimate Blog Post',
            'body' => 'This blog post object has everything in it!',
            'author' => array(
                'name' => 'William Shakespeare',
                'email' => 'billy.shakes@example.com'
            ),
            'comments' => array(
                array(
                    'author' => array(
                        'name' => 'John Doe',
                        'email' => 'john.doe@example.com'
                    ),
                    'text' => 'This is a comment'
                ),
                array(
                    'author' => array(
                        'name' => 'Jane Doe',
                        'email' => 'jane.doe@example.com'
                    ),
                    'text' => 'This is another comment'
                )
            )
        ));
    }

    public static function tearDownAfterClass() {
        self::$collection = null;
    }

    public function testGetCollectionName() {
        $this->assertEquals('posts', Post::getCollectionName());
    }

    public function testSimpleSave() {
        $post = new Post();
        $post->title = 'Simple Save Test';
        $this->assertTrue($post->save());
        $this->assertInstanceOf(MongoId, $post->getId());
        $this->assertDocument($post, array('title' => 'Simple Save Test'));
    }

    public function testEmbeddedLists() {
        $post = Post::findOne(1);
        $this->assertInstanceOf('ormon\EmbeddedList', $post->comments);
        $this->assertEquals(2, $post->comments->count);
        $this->assertInstanceOf(Comment, $post->comments->first);
        $this->assertNotNull($post->comments->get(1));
        $this->assertEquals('John Doe', $post->comments->first->author->name);
    }

    public function testEmbeddedObject() {
        $post = Post::findOne(1);
        $this->assertInstanceOf(Author, $post->author);
        $this->assertEquals('billy.shakes@example.com', $post->author->email);
    }

    public function testSettingEmbeddedObject() {
        $post = new Post();
        $post->author = new Author(array('name' => 'joe commenter'));
        $this->assertInstanceOf(Author, $post->author);
        $this->assertEquals('joe commenter', $post->author->name);

        $post->author = array('name' => 'joe commenter');
        $this->assertInstanceOf(Author, $post->author);
        $this->assertEquals('joe commenter', $post->author->name);
    }

    public function testSaveWithEmbeddedList() {
        $post = new Post(array(
            'comments' => array(
                new Comment(array(
                    'author' => new Author(array(
                        'name' => 'Calvin Yu',
                        'email' => 'csyu77@gmail.com'
                    )),
                    'text' => 'This is a comment'
                ))
            )
        ));

        $this->assertTrue($post->save());
        $this->assertDocument($post, array(
            'comments' => array(
                array(
                    'author' => array(
                        'name' => 'Calvin Yu',
                        'email' => 'csyu77@gmail.com'
                    ),
                    'text' => 'This is a comment'
                )
            )
        ));
    }

    public function testFindFieldProjection() {
        $post = Post::findOne(1, array('title'));
        $this->assertEquals('The Ultimate Blog Post', $post->title);
        $this->assertNull($post->body);
    }

    public function testSavingPostWithProjections() {
        $post = Post::findOne(1, array('title'));
        $post->foo = 'bar';
        $this->assertTrue($post->save());

        $this->assertDocumentPartial($post, array(
            'foo' => 'bar',
            'body' => 'This blog post object has everything in it!'
        ));
    }

    private function assertDocumentPartial($model, $expected) {
        $obj = Ormon::getDefaultDatabase()->posts->findOne(array('_id' => $model->getId()));
        foreach ($expected as $k => $v) {
            $this->assertEquals($v, $obj[$k]);
        }
    }

    private function assertDocument($model, $expected) {
        $obj = Ormon::getDefaultDatabase()->posts->findOne(array('_id' => $model->getId()));
        $this->assertDocsEquals($obj, $expected);
    }

    private function assertDocsEquals($actual, $expected) {
        unset($actual['_id']);
        $diffKeys = array_diff_key($actual, $expected);
        $this->assertTrue(empty($diffKeys), 'found extra keys: '.json_encode(array_keys($diffKeys)));
    }
}