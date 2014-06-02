<?php

class UserbinModelTest extends \PHPUnit_Framework_TestCase
{

  public function snakeCases() {
    return array(
      ['simpleTest', 'simple_test'],
      ['easy', 'easy'],
      ['HTML', 'html'],
      ['simpleXML', 'simple_xml'],
      ['PDFLoad', 'pdf_load'],
      ['startMIDDLELast', 'start_middle_last'],
      ['AString', 'a_string'],
      ['Some4Numbers234', 'some4_numbers234'],
      ['TEST123String', 'test123_string']
    );
  }

  public function testSetAttributesInConstructor()
  {
    $attributes = array(
      'id' => 1,
      'email' => 'hello@example.com'
    );
    $model = new Userbin_Model($attributes);

    $this->assertEquals($model->email, $attributes['email']);
    $this->assertEquals($model->id, $attributes['id']);
  }

  /**
   * @dataProvider snakeCases
   */
  public function testSnakeCase($camel, $snake)
  {
    $this->assertEquals(Userbin_Model::snakeCase($camel), $snake);
  }
}

?>