<?php

/**
 * Keeps the historic global class names (`Castle`, `Castle_*`, `RestModel`)
 * working after the library moved to the `Castle\` namespace.
 *
 * The namespaced classes are the canonical definitions; each legacy name is
 * registered as an alias to its namespaced counterpart on first use. Aliasing
 * is lazy (resolved through an autoloader) so it never eagerly loads the library
 * and stays compatible with test suites that substitute their own classes.
 *
 * Autoloading covers the common compatibility paths (`new`, `catch`, `extends`,
 * `class_exists`). PHP does not autoload the right-hand operand of `instanceof`,
 * so a bare `instanceof Castle_Foo` only resolves once the legacy name has been
 * referenced elsewhere; prefer the namespaced names in new code.
 *
 * Works on every supported runtime (PHP 7.4 through 8.x).
 */

if (!function_exists('castle_legacy_alias_map')) {
  function castle_legacy_alias_map()
  {
    return array(
      'Castle' => 'Castle\\Castle',
      'Castle_Request' => 'Castle\\Request',
      'Castle_RequestContext' => 'Castle\\RequestContext',
      'Castle_RequestTransport' => 'Castle\\RequestTransport',
      'Castle_CookieStore' => 'Castle\\CookieStore',
      'Castle_iCookieStore' => 'Castle\\CookieStoreInterface',
      'Castle_Webhook' => 'Castle\\Webhook',
      'Castle_Context' => 'Castle\\Context',
      'RestModel' => 'Castle\\RestModel',
      'Castle_Resource' => 'Castle\\Resource',
      'Castle_Error' => 'Castle\\Error',
      'Castle_RequestError' => 'Castle\\RequestError',
      'Castle_ConfigurationError' => 'Castle\\ConfigurationError',
      'Castle_CurlOptionError' => 'Castle\\CurlOptionError',
      'Castle_ApiError' => 'Castle\\ApiError',
      'Castle_BadRequest' => 'Castle\\BadRequest',
      'Castle_UnauthorizedError' => 'Castle\\UnauthorizedError',
      'Castle_ForbiddenError' => 'Castle\\ForbiddenError',
      'Castle_NotFoundError' => 'Castle\\NotFoundError',
      'Castle_InvalidParametersError' => 'Castle\\InvalidParametersError',
      'Castle_InvalidRequestTokenError' => 'Castle\\InvalidRequestTokenError',
      'Castle_WebhookVerificationError' => 'Castle\\WebhookVerificationError',
    );
  }
}

spl_autoload_register(function ($class) {
  $map = castle_legacy_alias_map();

  if (!isset($map[$class])) {
    return;
  }

  $target = $map[$class];

  // class_exists()/interface_exists() trigger the namespaced class to load (via
  // Composer's classmap or the bundled require chain) before we alias it.
  if (class_exists($target) || interface_exists($target)) {
    class_alias($target, $class);
  }
});

/**
 * Eagerly alias the exception hierarchy. PHP does not autoload the class named
 * in a `catch` clause, so without this a consumer's `catch (Castle_ApiError $e)`
 * would silently fail to catch the namespaced exception thrown by the library.
 * The exception classes have no dependencies, so aliasing them here only loads
 * `Errors.php` and never the (test-overridable) transport or cookie classes.
 */
call_user_func(function () {
  $eager = array(
    'Castle\\Error',
    'Castle\\RequestError',
    'Castle\\ConfigurationError',
    'Castle\\CurlOptionError',
    'Castle\\ApiError',
    'Castle\\BadRequest',
    'Castle\\UnauthorizedError',
    'Castle\\ForbiddenError',
    'Castle\\NotFoundError',
    'Castle\\InvalidParametersError',
    'Castle\\InvalidRequestTokenError',
    'Castle\\WebhookVerificationError',
  );

  $legacyByTarget = array_flip(castle_legacy_alias_map());

  foreach ($eager as $target) {
    if (!isset($legacyByTarget[$target])) {
      continue;
    }
    $legacy = $legacyByTarget[$target];
    if (class_exists($legacy, false)) {
      continue;
    }
    if (class_exists($target)) {
      class_alias($target, $legacy);
    }
  }
});
