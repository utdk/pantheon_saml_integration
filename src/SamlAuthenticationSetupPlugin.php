<?php

namespace Utexas\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Util\Filesystem;

/**
 * Composer plugin to setup SAML authentication for Pantheon hosted sites.
 */
class SamlAuthenticationSetupPlugin implements PluginInterface, EventSubscriberInterface {

  /**
   * @var \Composer\Composer
   */
  protected $composer;

  /**
   * @var IoInterface
   */
  protected $io;

  /**
   * @var \Composer\Script\Event
   */
  protected static $event;

  /**
   * @var \Composer\Util\Filesystem
   */
  protected $filesystem;

  /**
   * The scaffold options in the top-level composer.json's 'extra' section.
   *
   * @var \Drupal\Composer\Plugin\Scaffold\ManageOptions
   */
  protected $manageOptions;

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io) {
    $this->composer = $composer;
    $this->io = $io;
    $this->filesystem = new Filesystem();
  }

  /**
   * {@inheritdoc}
   */
  public function deactivate(Composer $composer, IOInterface $io) {
  }

  /**
   * {@inheritdoc}
   */
  public function uninstall(Composer $composer, IOInterface $io) {
  }

  /**
   * Method to define all events to trigger when installing the plugin.
   */
  public static function getSubscribedEvents() {
    return [
      'post-install-cmd' => 'initializeFiles',
      'post-update-cmd'  => 'initializeFiles',
    ];
  }

    /**
     * Event method to populate settings file.
     *
     * @param \Composer\Script\Event $event
     *   The EventSubscriber object.
     */
  public function initializeFiles(Event $event) {
    // Get webroot.
    $web_root = $this->getWebRoot();
     // Copy SAML-specific settings file within /sites/default.
    $this->io->write('[UTexas Pantheon SAML]: Copying Drupal settings file');
    $settings_file = $web_root . '/sites/default/settings.pantheon.saml.php';
    if (file_exists($settings_file)) {
      $this->filesystem->remove($web_root . '/sites/default/settings.pantheon.saml.php');
    }
    $this->filesystem->copy('./vendor/utexas/pantheon_saml_integration/assets/drupal-settings/settings.pantheon.saml.php', $web_root . '/sites/default/settings.pantheon.saml.php');
  }

  /**
   * Retrieve the path to the web root.
   *
   * @return string
   *   The path to the webroot.
   */
  public function getWebRoot() {
    $drupalCorePackage = $this->composer->getRepositoryManager()->getLocalRepository()->findPackage('drupal/core', '*');
    $installationManager = $this->composer->getInstallationManager();
    $corePath = $installationManager->getInstallPath($drupalCorePackage);
    // Ensure we use a relative path for generating the symlinks.
    $corePath = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $corePath);
    // Webroot is the parent path of the drupal core installation path.
    $webroot = dirname($corePath);
    return $webroot;
  }

}
