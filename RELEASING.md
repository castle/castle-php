# Releasing

1. Create branch `release/X.Y.Z` from `main`.
2. Update the `VERSION` constant in `lib/Castle/Castle.php` to the new version.
3. Update the `CHANGELOG.md` for the impending release.
4. `git commit -am "release X.Y.Z"` (where X.Y.Z is the new version).
5. Push to Github, make a PR to the `main` branch, and when approved, merge.
6. Make a release on Github from the `main` branch, specify tag as `X.Y.Z` to create a tag.

[Packagist](https://packagist.org/packages/castle/castle-php) is configured to
auto-update from the repository, so pushing the `X.Y.Z` tag publishes the new
version. If the webhook is not configured, trigger an update manually from the
package page on Packagist.
