---
name: Release
about: Prepare code for a new release
labels: 'release'

---

## Pre-release checks

- [ ] Review the documentation issues for any pending tasks that relate to the issues resolved; if any have not been completed, put this issue on hold & resolve those documentation tasks

## Release pull request tasks

None; this project does not have a version number in code, so no code changes are involved in the release process.

## Release completion tasks

This project does not use Gitflow, so releases are created directly from master.

- [ ] [Create a new release](https://github.austin.utexas.edu/eis1-wcs/pantheon_saml_integration/releases/new) (version number and release title should be the same (e.g., `1.0.0-alpha.5`)
- [ ] Use [gren](https://github.com/github-tools/github-release-notes) generate the release notes `gren release --api-url=https://github.austin.utexas.edu/api/v3 --repo=pantheon_saml_integration --username=eis1-wcs --ignore-issues-with="wontfix,release,duplicate,invalid" --override`
