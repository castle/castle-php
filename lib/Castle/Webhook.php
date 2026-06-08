<?php

class Castle_Webhook
{
  const SIGNATURE_HEADER = 'HTTP_X_CASTLE_SIGNATURE';

  /**
   * Verify the authenticity of an incoming Castle webhook.
   *
   * @param  String $requestBody The raw request body. Defaults to php://input.
   * @param  String $signature   The signature to compare against. Defaults to
   *                             the X-Castle-Signature request header.
   * @return Boolean
   * @throws Castle_WebhookVerificationError
   * @throws Castle_ConfigurationError
   */
  public static function verify($requestBody = null, $signature = null)
  {
    if ($requestBody === null) {
      $requestBody = file_get_contents('php://input');
    }

    if ($signature === null && array_key_exists(self::SIGNATURE_HEADER, $_SERVER)) {
      $signature = $_SERVER[self::SIGNATURE_HEADER];
    }

    if ($requestBody === null || $requestBody === '') {
      throw new Castle_WebhookVerificationError('Invalid webhook from Castle API');
    }

    $expectedSignature = self::computeSignature($requestBody);

    if (!is_string($signature) || !hash_equals($expectedSignature, $signature)) {
      throw new Castle_WebhookVerificationError('Signature not matching the expected signature');
    }

    return true;
  }

  private static function computeSignature($requestBody)
  {
    $key = Castle::getApiKey();
    if (empty($key)) {
      throw new Castle_ConfigurationError();
    }
    return base64_encode(hash_hmac('sha256', $requestBody, $key, true));
  }
}
