# 2. Switch from simpleSAMLphp to OneLogin

Date: 2025-01-24

## Status

Accepted

## Context

The Web Content Management Services team integrates SSO sign-in via Enterprise Authentication for 38 WordPress websites and 256 Drupal
websites. We currently use simpleSAMLphp for this integration. While this library is widely-used, it has a number of disadvantages for us: its
codebase, which provides software for both SP and IdP roles, is far more complex than we need as purely an SP integrator; its software
dependencies are numerous and these dependencies have, on multiple occasions, presented problematic conflicts with other dependencies we
use.

To provide a long-term sustainable SSO integration, we need a different API.

## Decision

Quest owns the OneIdentity portfolio for Identity Access Management tools, which includes an SP library called OneLogin. OneLogin is broadly
used, including by HigherEd institutions who support Drupal and WordPress sites. Our team’s initial research suggests that it will support all the requirements we have, such as mapping user attributes and affiliations. Its documentation and testing tools are robust. Its lighter software footprint would allow us to retire in-house middleware and simplify our package management.

## Consequences

- We will likely have fewer issues with updates and dependencies
- Sites with existing Enterprise Authentication integrations will need to do some minimal reconfiguration (enabling the `samlauth` module)

