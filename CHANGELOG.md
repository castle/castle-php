# Changelog
## 4.0.0
**BREAKING CHANGES:**

* the library is now defined under the `Castle\` namespace (`Castle\Castle`, `Castle\Webhook`, `Castle\RequestContext`, `Castle\ApiError`, ...), which is the canonical API ([#40](https://github.com/castle/castle-php/issues/40))
* removed the legacy `Castle::track`, `Castle::authenticate` and `Castle::impersonate` endpoints (and the `Castle_Authenticate` model); use `Castle::risk`, `Castle::filter` and `Castle::log` instead
* `Castle::risk` and `Castle::filter` now fail over to a configurable decision instead of throwing on network errors, timeouts and `5xx` responses; `Castle::log` returns the same response shape. `Castle::risk`/`filter`/`log` responses now include `failover` and `failover_reason`
* the default request timeout is now 1000 ms (previously 10 s), applied to both connection and transfer; configure it with `Castle::setRequestTimeout`
* removed `Castle::setCurlOpts` / `Castle::getCurlOpts` (and the `Castle\CurlOptionError` exception with its `Castle_CurlOptionError` alias); use `Castle::setRequestTimeout` to configure the connection and transfer timeout
* replaced the separate `Castle::$apiBase` / `Castle::$apiVersion` (and `Castle::getApiVersion` / `setApiVersion`) with a single `Castle::$baseUrl` (default `https://api.castle.io/v1`), configurable via `Castle::getBaseUrl` / `Castle::setBaseUrl`
* removed the configurable token/cookie store (`Castle::$tokenStore`, `Castle::$cookieStore`, `Castle::getTokenStore` / `getCookieStore` / `setTokenStore`) and the `Castle\CookieStore` class (with its `Castle_CookieStore` / `Castle_iCookieStore` aliases); these read the client id from the `__cid` cookie, which the slimmed request context no longer does

Other changes:

* slimmed the default request context built by `Castle\RequestContext::extract` down to `ip`, `headers` and `library`; the `client_id` and `user_agent` fields (and the `RequestContext::extractClientId`, `extractUserAgent` and `normalize` helpers) are removed, as the client id is carried by the `X-Castle-Client-Id` header / `__cid` cookie and resolved server-side
* added a configurable failover strategy (`Castle::setFailoverStrategy` with `Castle\Failover::ALLOW`/`DENY`/`CHALLENGE`/`THROW`) and the `Castle\InternalServerError` exception for `5xx` responses
* added do-not-track support: `Castle::disableTracking`, `Castle::enableTracking` and `Castle::tracked`
* added the Events API: `Castle::eventsSchema`, `Castle::queryEvents`, `Castle::groupEvents`
* the historic global class names (`Castle`, `Castle_*`, `RestModel`) are retained as aliases of their namespaced counterparts, so existing integrations keep working without changes; `catch` and `instanceof` work with either name
* additional PHP 8 compatibility fixes: declared `Castle_Resource::$model`, and avoided passing `null` to `Exception` and `json_decode`

## 3.3.0
* added the Lists API: `Castle::createList`, `Castle::getAllLists`, `Castle::getList`, `Castle::updateList`, `Castle::deleteList`, `Castle::queryList`
* added the List Items API: `Castle::createListItem`, `Castle::createListItems`, `Castle::getListItem`, `Castle::updateListItem`, `Castle::queryListItems`, `Castle::countListItems`, `Castle::archiveListItem`, `Castle::unarchiveListItem`
* added the Privacy API: `Castle::requestUserData`, `Castle::deleteUserData`
* added webhook signature verification: `Castle_Webhook::verify` and the `Castle_WebhookVerificationError` exception
* the request context is now attached automatically to `risk`, `filter` and `log` requests
* a `sent_at` timestamp is now attached automatically to `risk`, `filter` and `log` requests ([#23](https://github.com/castle/castle-php/issues/23))
* improved PHP 8 compatibility: declared the `Castle_ApiError` properties and made API error handling tolerant of empty response bodies
* migrated CI to GitHub Actions and now test against PHP 7.2 through 8.5

## 3.2.0 (2022-03-28)
* updated ca-certs file

## 3.1.0 (2022-03-02)
- [#47](https://github.com/castle/castle-php/pull/47)
  * added `Castle_InvalidRequestTokenError` exception

## 3.1.0 (2022-03-02)
- [#47](https://github.com/castle/castle-php/pull/47)
  * added `Castle_InvalidRequestTokenError` exception

## 3.0.0 (2021-03-15)
**BREAKING CHANGES:**

- [#42](https://github.com/castle/castle-php/pull/42)
  * remove `identify` and `review` commands - they are no longer supported
  * renamed setUseWhitelist with setUseAllowlist

**Enhancements:**

- [#41](https://github.com/castle/castle-php/pull/41)
  * added risk, log, filter methods

- [#37](https://github.com/castle/castle-php/pull/37) add new CircleCI workflow
