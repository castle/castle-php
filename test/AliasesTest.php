<?php

class AliasesTest extends Castle_TestCase
{
  public function testLegacyAndNamespacedNamesResolveToTheSameClass()
  {
    $map = castle_legacy_alias_map();
    foreach ($map as $legacy => $namespaced) {
      $this->assertTrue(
        class_exists($namespaced) || interface_exists($namespaced),
        "Expected {$namespaced} to be available"
      );
      $this->assertTrue(
        class_exists($legacy) || interface_exists($legacy),
        "Expected legacy {$legacy} to be available"
      );
      $this->assertTrue(
        is_a($legacy, $namespaced, true),
        "Expected {$legacy} to be an alias of {$namespaced}"
      );
    }
  }

  public function testLegacyFacadeSharesNamespacedDefinition()
  {
    $this->assertSame(\Castle\Castle::VERSION, Castle::VERSION);
    $this->assertTrue(is_a('Castle', 'Castle\\Castle', true));
  }

  public function testLegacyExceptionsAreNamespacedExceptions()
  {
    $error = new Castle_WebhookVerificationError('boom');
    $this->assertInstanceOf('Castle\\WebhookVerificationError', $error);
    $this->assertInstanceOf('Castle\\Error', $error);
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
