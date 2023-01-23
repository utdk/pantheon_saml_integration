<?php

/**
 * This file is part of the Pantheon SAML Integration plugin.
 *
 * Copyright (C) 2019-2021 by The University of Texas at Austin.
 */

namespace Utexas\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\Event;
use Composer\Util\Filesystem;
use Composer\Package\PackageInterface;

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
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->filesystem = new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    /**
     * Method to define all events to trigger when installing the plugin.
     */
    public static function getSubscribedEvents()
    {
        return [
          'post-install-cmd' => 'initializeFiles',
          'post-update-cmd'  => 'initializeFiles',
        ];
    }

    /**
     * Event method to generate symlinks and files.
     *
     * @param Event $event The EventSubscriber object.
     *
     * @return void
     */
    public function initializeFiles(Event $event)
    {
        // @TODO: Evaluate if this can be removed in favor of $_ENV['DOCROOT'].
        // Get webroot.
        $web_root = $this->getWebRoot();
        $web_root_depth = '';
        // Get relative paths.
        if ($web_root === '.') {
            // If webroot is docroot, no depth.
            $web_root_depth = './';
        } elseif (strpos($web_root, '/') === false) {
            // There is one level of depth. Such as /web.
            $web_root_depth = '../';
        } elseif (strpos($web_root, '/') !== false) {
            // If slash present, there are a least 2 levels.
            // of depth.
            $web_root_depth = explode('/', $web_root);
            $web_root_depth = (str_repeat("../", count($web_root_depth)));
        }
        $this->io->write('Webroot located at: ' . $web_root);

        // 1. Remove empty config and metadata directories
        // provided by simplesamlphp, and pantheon symlink.
        $this->io->write('[UTexas Pantheon SAML]: Directory cleanup');
        $directories = [
          './vendor/simplesamlphp/simplesamlphp/config',
          './vendor/simplesamlphp/simplesamlphp/metadata',
          $web_root . '/simplesaml',
        ];
        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $this->filesystem->removeDirectory($dir);
            }
        }

        // 2. Generate symlinks required by Pantheon.
        $this->io->write('[UTexas Pantheon SAML]: Generating symlinks');
        $links = [
          [$web_root_depth . 'vendor/simplesamlphp/simplesamlphp/www', $web_root . '/simplesaml'],
          ['../../../' . $web_root . '/sites/default/files/private/saml/assets/config', './vendor/simplesamlphp/simplesamlphp/config'],
          ['../../../' . $web_root . '/sites/default/files/private/saml/assets/metadata', './vendor/simplesamlphp/simplesamlphp/metadata'],
        ];
        foreach ($links as $link) {
            // Delete symlink before creating new one.
            if (is_link($link[1])) {
                $this->io->write('Recreating symlink: ' . $link[1]);
                unlink($link[1]);
            }
            $this->io->write('Making directory: ' . $link[1] . ' a symlink pointing to: ' . $link[0]);
            symlink($link[0], $link[1]);
        }

        // 3. Copy the saml specific settings file within sites/default.
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
     */
    public function getWebRoot()
    {
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
