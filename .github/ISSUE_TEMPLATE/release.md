---
name: Release
about: Prepare code for a new release
labels: 'release'

---

## Pre-release checks

- [ ] Review the [documentation issues](https://github.austin.utexas.edu/eis1-wcs/utdk_docs/issues) for any pending tasks that relate to the issues resolved; if any have not been completed, put this issue on hold & resolve those documentation tasks
- [ ] If changes have been staged for `utexas/utexas_saml_auth_helper` during the last release cycle, a new release has been tagged. Then bump the version number requirement in this plugin's `composer.json`.
- [ ] Contributed module dependencies have been updated, if updates are available
    - (script available at [utdk3_release_packaging](https://github.austin.utexas.edu/eis1-wcs/utdk3_release_packaging/blob/main/releases/utdk_contrib_updater.sh))

```
git clone git@github.austin.utexas.edu:eis1-wcs/pantheon_saml_integration.git
cd pantheon_saml_integration
composer config repositories.drupal composer https://packages.drupal.org/8
composer install
composer outdated --direct
```

## Release pull request tasks

- [ ] Create release branch from develop, e.g. `release/3.0.0`
- [ ] Since this plugin does not have an `info.yml` file, there may be no code changes for the actual release. Request a review of the issue by a team member before completing the release, below.

## Release completion tasks

- [ ] After approval, merge release branch to develop & master:
- Merge using the Gitflow strategy:

```
git fetch && git checkout develop && git pull origin develop && git merge --no-ff release/3.0.0
git fetch && git checkout master && git pull origin master && git merge --no-ff release/3.0.0
git tag 3.0.0
git push origin develop && git push origin master && git push origin git tag 3.0.0
```

- [ ] [Create a new release](https://github.austin.utexas.edu/eis1-wcs/pantheon_saml_integration/releases/new) (version number and release title should be the same (e.g., `3.0.0`)
- [ ] Use [gren](https://github.com/github-tools/github-release-notes) generate the release notes `gren release --api-url=https://github.austin.utexas.edu/api/v3 --repo=pantheon_saml_integration --username=eis1-wcs --ignore-issues-with="wontfix,release,duplicate,invalid" --override`
