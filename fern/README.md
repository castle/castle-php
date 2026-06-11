# Fern scaffold (proposal)

This directory is a proposal for generating the Castle PHP SDK surface with
[Fern](https://buildwithfern.com) from an OpenAPI specification.

## Layout

- `fern.config.json` — organization name and pinned Fern CLI version.
- `generators.yml` — declares the API spec and the `fern-php-sdk` generator group.
- `openapi/openapi.yml` — OpenAPI spec for the Castle scoring, Lists, Privacy
  and Events endpoints used as the generator input.

## Usage

```bash
npm install -g fern-api
fern check
fern generate --group php-sdk --local
```

Generated code is written to `../generated/php` and is not committed.
