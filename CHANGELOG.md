# Changelog
## 4.0.0
**BREAKING CHANGES:**

* the library is now defined under the `Castle\` namespace (`Castle\Castle`, `Castle\Webhook`, `Castle\RequestContext`, `Castle\ApiError`, ...), which is the canonical API ([#40](https://github.com/castle/castle-php/issues/40))

Other changes:

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
