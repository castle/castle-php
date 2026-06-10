<?php

class CastleTest extends Castle_TestCase
{
  protected $sessionToken;

  public static function setUpBeforeClass(): void {
    Castle::setApiKey('secretkey');
  }

  public function setUp(): void
  {
    $_SESSION = array();
    $_COOKIE = array();
    Castle_RequestTransport::reset();
    Castle::enableTracking();
    Castle::setFailoverStrategy('allow');
  }

  public function testSetApiKey()
  {
    $this->assertEquals('secretkey', Castle::getApiKey());
  }

  public function testFilter()
  {
    Castle_RequestTransport::setResponse(204, '');
    Castle::filter(Array(
      'request_token' => '7e51335b-f4bc-4bc7-875d-b713fb61eb23-bf021a3022a1a302',
      'name' => '$registration',
      'user' => Array('id' => 'abc', 'email' => 'user@foobar.io')
    ));
    $this->assertRequest('post', '/filter');
  }

  public function testLog()
  {
    Castle_RequestTransport::setResponse(204, '');
    Castle::log(Array(
      'request_token' => '7e51335b-f4bc-4bc7-875d-b713fb61eb23-bf021a3022a1a302',
      'name' => '$login',
      'status' => '$failed',
      'user' => Array('id' => 'abc', 'email' => 'user@foobar.io')
    ));
    $this->assertRequest('post', '/log');
  }

  public function testRisk()
  {
    Castle_RequestTransport::setResponse(204, '');
    Castle::risk(Array(
      'request_token' => '7e51335b-f4bc-4bc7-875d-b713fb61eb23-bf021a3022a1a302',
      'name' => '$login',
      'status' => '$succeeded',
      'user' => Array('id' => 'abc', 'email' => 'user@foobar.io')
    ));
    $this->assertRequest('post', '/risk');
  }

  public function testRiskIncludesContext()
  {
    Castle_RequestTransport::setResponse(200, '{}');
    Castle::risk(Array(
      'request_token' => 'token',
      'name' => '$login',
      'user' => Array('id' => 'abc')
    ));
    $request = $this->assertRequest('post', '/risk');
    $this->assertArrayHasKey('context', $request['params']);
  }

  public function testFilterIncludesContext()
  {
    Castle_RequestTransport::setResponse(200, '{}');
    Castle::filter(Array(
      'request_token' => 'token',
      'name' => '$registration',
      'user' => Array('id' => 'abc')
    ));
    $request = $this->assertRequest('post', '/filter');
    $this->assertArrayHasKey('context', $request['params']);
  }

  public function testLogIncludesContext()
  {
    Castle_RequestTransport::setResponse(204, '');
    Castle::log(Array(
      'request_token' => 'token',
      'name' => '$login',
      'status' => '$succeeded',
      'user' => Array('id' => 'abc')
    ));
    $request = $this->assertRequest('post', '/log');
    $this->assertArrayHasKey('context', $request['params']);
  }

  public function testRiskIncludesSentAt()
  {
    Castle_RequestTransport::setResponse(200, '{}');
    Castle::risk(Array(
      'request_token' => 'token',
      'name' => '$login',
      'user' => Array('id' => 'abc')
    ));
    $request = $this->assertRequest('post', '/risk');
    $this->assertArrayHasKey('sent_at', $request['params']);
    $this->assertSame(
      1,
      preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/', $request['params']['sent_at'])
    );
  }

  public function testLogIncludesSentAt()
  {
    Castle_RequestTransport::setResponse(204, '');
    Castle::log(Array(
      'request_token' => 'token',
      'name' => '$login',
      'status' => '$succeeded',
      'user' => Array('id' => 'abc')
    ));
    $request = $this->assertRequest('post', '/log');
    $this->assertArrayHasKey('sent_at', $request['params']);
  }

  public function testSentAtIsNotOverwritten()
  {
    Castle_RequestTransport::setResponse(200, '{}');
    Castle::filter(Array(
      'request_token' => 'token',
      'name' => '$registration',
      'user' => Array('id' => 'abc'),
      'sent_at' => '2020-01-01T00:00:00.000Z'
    ));
    $request = $this->assertRequest('post', '/filter');
    $this->assertEquals('2020-01-01T00:00:00.000Z', $request['params']['sent_at']);
  }

  public function testCreateList()
  {
    Castle_RequestTransport::setResponse(201, '{ "id": "list-id", "name": "blocklist" }');
    $list = Castle::createList(Array(
      'name' => 'blocklist',
      'color' => '$red',
      'primary_field' => 'user.email'
    ));
    $this->assertRequest('post', '/lists');
    $this->assertEquals('list-id', $list['id']);
  }

  public function testGetAllLists()
  {
    Castle_RequestTransport::setResponse(200, '[{ "id": "list-id" }]');
    $lists = Castle::getAllLists();
    $this->assertRequest('get', '/lists');
    $this->assertEquals('list-id', $lists[0]['id']);
  }

  public function testGetList()
  {
    Castle_RequestTransport::setResponse(200, '{ "id": "list-id" }');
    Castle::getList('list-id');
    $this->assertRequest('get', '/lists/list-id');
  }

  public function testUpdateList()
  {
    Castle_RequestTransport::setResponse(200, '{ "id": "list-id" }');
    Castle::updateList('list-id', array('name' => 'renamed'));
    $this->assertRequest('put', '/lists/list-id');
  }

  public function testDeleteList()
  {
    Castle_RequestTransport::setResponse(204, '');
    Castle::deleteList('list-id');
    $this->assertRequest('delete', '/lists/list-id');
  }

  public function testQueryList()
  {
    Castle_RequestTransport::setResponse(200, '{ "total_count": 0, "items": [] }');
    Castle::queryList(array('filters' => array()));
    $this->assertRequest('post', '/lists/query');
  }

  public function testCreateListItem()
  {
    Castle_RequestTransport::setResponse(201, '{ "id": "item-id" }');
    Castle::createListItem('list-id', array(
      'author' => 'user:123',
      'primary_value' => 'user@example.com'
    ));
    $this->assertRequest('post', '/lists/list-id/items');
  }

  public function testCreateListItems()
  {
    Castle_RequestTransport::setResponse(201, '{ "items": [] }');
    Castle::createListItems('list-id', array('items' => array()));
    $this->assertRequest('post', '/lists/list-id/items/batch');
  }

  public function testGetListItem()
  {
    Castle_RequestTransport::setResponse(200, '{ "id": "item-id" }');
    Castle::getListItem('list-id', 'item-id');
    $this->assertRequest('get', '/lists/list-id/items/item-id');
  }

  public function testUpdateListItem()
  {
    Castle_RequestTransport::setResponse(200, '{ "id": "item-id" }');
    Castle::updateListItem('list-id', 'item-id', array('comment' => 'note'));
    $this->assertRequest('put', '/lists/list-id/items/item-id');
  }

  public function testQueryListItems()
  {
    Castle_RequestTransport::setResponse(200, '{ "total_count": 0, "items": [] }');
    Castle::queryListItems('list-id', array('filters' => array()));
    $this->assertRequest('post', '/lists/list-id/items/query');
  }

  public function testCountListItems()
  {
    Castle_RequestTransport::setResponse(200, '{ "count": 0 }');
    Castle::countListItems('list-id', array('filters' => array()));
    $this->assertRequest('post', '/lists/list-id/items/count');
  }

  public function testArchiveListItem()
  {
    Castle_RequestTransport::setResponse(204, '');
    Castle::archiveListItem('list-id', 'item-id');
    $this->assertRequest('delete', '/lists/list-id/items/item-id/archive');
  }

  public function testUnarchiveListItem()
  {
    Castle_RequestTransport::setResponse(204, '');
    Castle::unarchiveListItem('list-id', 'item-id');
    $this->assertRequest('put', '/lists/list-id/items/item-id/unarchive');
  }

  public function testRequestUserData()
  {
    Castle_RequestTransport::setResponse(200, '{ "id": "req-id" }');
    Castle::requestUserData(array(
      'identifier' => 'user@example.com',
      'identifier_type' => '$email'
    ));
    $this->assertRequest('post', '/privacy/users');
  }

  public function testDeleteUserData()
  {
    Castle_RequestTransport::setResponse(200, '{ "id": "req-id" }');
    Castle::deleteUserData(array(
      'identifier' => 'user@example.com',
      'identifier_type' => '$email'
    ));
    $this->assertRequest('delete', '/privacy/users');
  }

  public function testEventsSchema()
  {
    Castle_RequestTransport::setResponse(200, '{ "events": [] }');
    Castle::eventsSchema();
    $this->assertRequest('get', '/events/schema');
  }

  public function testQueryEvents()
  {
    Castle_RequestTransport::setResponse(200, '{ "data": [] }');
    Castle::queryEvents(array('filters' => array()));
    $this->assertRequest('post', '/events/query');
  }

  public function testGroupEvents()
  {
    Castle_RequestTransport::setResponse(200, '{ "data": [] }');
    Castle::groupEvents(array('filters' => array()));
    $this->assertRequest('post', '/events/group');
  }

  public function testRiskSuccessIncludesFailoverFalse()
  {
    Castle_RequestTransport::setResponse(200, '{ "policy": { "action": "allow" } }');
    $risk = Castle::risk(array(
      'request_token' => 'token',
      'name' => '$login',
      'user' => array('id' => 'abc')
    ));
    $this->assertFalse($risk->failover);
    $this->assertNull($risk->failover_reason);
  }

  public function testRiskFailsOverOnRequestError()
  {
    Castle_RequestTransport::setError();
    $risk = Castle::risk(array(
      'request_token' => 'token',
      'name' => '$login',
      'user' => array('id' => 'abc')
    ));
    $this->assertTrue($risk->failover);
    $this->assertEquals('allow', $risk->action);
    $this->assertEquals('allow', $risk->policy['action']);
    $this->assertEquals('abc', $risk->user_id);
    $this->assertEquals('Castle\\RequestError', $risk->failover_reason);
  }

  public function testRiskFailsOverOnServerError()
  {
    Castle_RequestTransport::setResponse(500, '{ "type": "server_error" }');
    $risk = Castle::risk(array(
      'request_token' => 'token',
      'name' => '$login',
      'user' => array('id' => 'abc')
    ));
    $this->assertTrue($risk->failover);
    $this->assertEquals('allow', $risk->action);
    $this->assertEquals('Castle\\InternalServerError', $risk->failover_reason);
  }

  public function testFilterFailoverUsesMatchingUserId()
  {
    Castle::setFailoverStrategy('deny');
    Castle_RequestTransport::setError();
    $filter = Castle::filter(array(
      'request_token' => 'token',
      'name' => '$registration',
      'matching_user_id' => 'mu-1'
    ));
    $this->assertTrue($filter->failover);
    $this->assertEquals('deny', $filter->action);
    $this->assertEquals('mu-1', $filter->user_id);
  }

  public function testFailoverThrowStrategyReraises()
  {
    Castle::setFailoverStrategy('throw');
    Castle_RequestTransport::setError();
    $this->expectException(\Castle\RequestError::class);
    Castle::risk(array(
      'request_token' => 'token',
      'name' => '$login',
      'user' => array('id' => 'abc')
    ));
  }

  public function testClientErrorsAreNotFailedOver()
  {
    Castle_RequestTransport::setResponse(422, '{ "type": "invalid_request_token" }');
    $this->expectException(\Castle\InvalidRequestTokenError::class);
    Castle::risk(array(
      'request_token' => 'token',
      'name' => '$login',
      'user' => array('id' => 'abc')
    ));
  }

  public function testDoNotTrackReturnsAllowWithoutRequest()
  {
    Castle::disableTracking();
    $risk = Castle::risk(array(
      'request_token' => 'token',
      'name' => '$login',
      'user' => array('id' => 'abc')
    ));
    $this->assertTrue($risk->failover);
    $this->assertEquals('allow', $risk->action);
    $this->assertEquals('abc', $risk->user_id);
    $this->assertEquals('Castle is set to do not track.', $risk->failover_reason);
    $this->assertNull(Castle_RequestTransport::getLastRequest());
  }

  public function testSetFailoverStrategyRejectsUnknown()
  {
    $this->expectException(\Castle\ConfigurationError::class);
    Castle::setFailoverStrategy('bogus');
  }
}
