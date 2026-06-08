<?php

class AliasesTest extends Castle_TestCase
{
  public function testNamespacedClassesResolveToLegacyClasses()
  {
    $map = castle_legacy_alias_map();
    foreach ($map as $namespaced => $legacy) {
      $this->assertTrue(
        class_exists($namespaced) || interface_exists($namespaced),
        "Expected {$namespaced} to be available"
      );
    }
  }

  public function testNamespacedFacadeSharesLegacyDefinition()
  {
    $this->assertSame(Castle::VERSION, \Castle\Castle::VERSION);
    $this->assertTrue(is_a('Castle\\Castle', 'Castle', true));
  }

  public function testNamespacedExceptionsAreLegacyExceptions()
  {
    $error = new \Castle\WebhookVerificationError('boom');
    $this->assertInstanceOf('Castle_WebhookVerificationError', $error);
    $this->assertInstanceOf('Castle_Error', $error);
    $this->assertEquals('boom', $error->getMessage());
  }

  public function testNamespacedApiErrorCarriesMetadata()
  {
    $error = new \Castle\BadRequest('bad', 'invalid', 400);
    $this->assertInstanceOf('Castle_BadRequest', $error);
    $this->assertInstanceOf('Castle_ApiError', $error);
    $this->assertEquals('invalid', $error->type);
    $this->assertEquals(400, $error->httpStatus);
  }

  public function testNamespacedWebhookVerifies()
  {
    $secret = 'secretkey';
    Castle::setApiKey($secret);
    $body = '{"type":"$incident.confirmed"}';
    $signature = base64_encode(hash_hmac('sha256', $body, $secret, true));

    $this->assertTrue(\Castle\Webhook::verify($body, $signature));
  }
}
