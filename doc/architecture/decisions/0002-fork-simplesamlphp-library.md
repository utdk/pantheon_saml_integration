# 2. Fork the simplesamlphp library

Date: 2023-05-09

## Status

Accepted

## Context

On the cusp of updating the Drupal Kit to use Drupal 10 / Symfony 6, we learned that there is no tagged version of the `simplesamlphp/simplesamlphp` library that was compatible with Drupal 10, and there was no roadmap for it.

Although the `master` branch was nominally compatible and we had functionally tested it, we didn't want to require a branch, which could result in changes being introduced during updates that we hadn't vetted. We had also recently learned that it was not possible to require a specific commit hash in a Composer dependency; that was only possible in a root-level `composer.json`, and we wanted to avoid modifying many sites' Composer requirements.

We needed a way to provide a stable, idempotent build of the SimpleSAMLphp library that was compatible with Drupal 10 / Symfony 6.

## Decision

We will fork the `simplesamlphp/simplesamlphp` library and host our fork on Github.com and provide a tagged release so that we can control the build of the library.

## Consequences

- We will also need to fork `drupal/simplesamlphp_auth`, since it is that dependency which requires the `master` branch of `simplesamlphp/simplesamlphp` and we need to require a tag.
- We will have to add a [VCS repository](https://getcomposer.org/doc/05-repositories.md#vcs) to the upstream (`utdk-project`) and instruct non-upstream users to do the same so that our version of `simplesamlphp/simplesamlphp` is retrieved rather than the original version on Packagist
- We will not have to modify individual site requirements in any other ways
- If and when a stable, tagged release of `simplesamlphp/simplesamlphp` that is Drupal 10-compatible becomes available, we will be able to back out this approach without any direct code changes to individual sites -- everything can be managed through a new release of `pantheon_saml_integration`
