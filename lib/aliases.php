<?php

/**
 * Exposes the library under the `Castle\` namespace while keeping the historic
 * global class names (`Castle`, `Castle_*`, `RestModel`) fully functional.
 *
 * The legacy classes remain the canonical definitions; each namespaced name is
 * registered as an alias to its legacy counterpart on first use. Aliasing is
 * lazy (resolved through an autoloader) so nothing extra is loaded unless a
 * `Castle\` class is actually referenced.
 *
 * Works on every supported runtime (PHP 7.4 through 8.x).
 */

if (!function_exists('castle_legacy_alias_map')) {
  function castle_legacy_alias_map()
  {
    return array(
      'Castle\\Castle' => 'Castle',
      'Castle\\Request' => 'Castle_Request',
      'Castle\\RequestContext' => 'Castle_RequestContext',
      'Castle\\RequestTransport' => 'Castle_RequestTransport',
      'Castle\\CookieStore' => 'Castle_CookieStore',
      'Castle\\CookieStoreInterface' => 'Castle_iCookieStore',
      'Castle\\Webhook' => 'Castle_Webhook',
      'Castle\\Authenticate' => 'Castle_Authenticate',
      'Castle\\Context' => 'Castle_Context',
      'Castle\\RestModel' => 'RestModel',
      'Castle\\Resource' => 'Castle_Resource',
      'Castle\\Error' => 'Castle_Error',
      'Castle\\RequestError' => 'Castle_RequestError',
      'Castle\\ConfigurationError' => 'Castle_ConfigurationError',
      'Castle\\CurlOptionError' => 'Castle_CurlOptionError',
      'Castle\\ApiError' => 'Castle_ApiError',
      'Castle\\BadRequest' => 'Castle_BadRequest',
      'Castle\\UnauthorizedError' => 'Castle_UnauthorizedError',
      'Castle\\ForbiddenError' => 'Castle_ForbiddenError',
      'Castle\\NotFoundError' => 'Castle_NotFoundError',
      'Castle\\InvalidParametersError' => 'Castle_InvalidParametersError',
      'Castle\\InvalidRequestTokenError' => 'Castle_InvalidRequestTokenError',
      'Castle\\WebhookVerificationError' => 'Castle_WebhookVerificationError',
    );
  }
}

spl_autoload_register(function ($class) {
  $map = castle_legacy_alias_map();

  if (!isset($map[$class])) {
    return;
  }

  $legacy = $map[$class];

  // class_exists()/interface_exists() trigger the legacy class to load (via
  // Composer's classmap or the bundled require chain) before we alias it.
  if (class_exists($legacy) || interface_exists($legacy)) {
    class_alias($legacy, $class);
  }
});
