<?php

class CastleWebhookTest extends Castle_TestCase
{
  private $apiSecret = 'secretkey';

  public function setUp(): void
  {
    Castle::setApiKey($this->apiSecret);
    $_SERVER = array();
  }

  private function sign($body)
  {
    return base64_encode(hash_hmac('sha256', $body, $this->apiSecret, true));
  }

  public function testVerifyValidSignature()
  {
    $body = '{"type":"$incident.confirmed"}';
    $this->assertTrue(Castle_Webhook::verify($body, $this->sign($body)));
  }

  public function testVerifyReadsSignatureFromHeader()
  {
    $body = '{"type":"$incident.confirmed"}';
    $_SERVER['HTTP_X_CASTLE_SIGNATURE'] = $this->sign($body);
    $this->assertTrue(Castle_Webhook::verify($body));
  }

  public function testVerifyInvalidSignature()
  {
    $this->expectException(Castle_WebhookVerificationError::class);
    Castle_Webhook::verify('{"type":"$incident.confirmed"}', 'invalid-signature');
  }

  public function testVerifyMissingSignature()
  {
    $this->expectException(Castle_WebhookVerificationError::class);
    Castle_Webhook::verify('{"type":"$incident.confirmed"}');
  }

  public function testVerifyEmptyBody()
  {
    $this->expectException(Castle_WebhookVerificationError::class);
    Castle_Webhook::verify('', $this->sign(''));
  }
}
