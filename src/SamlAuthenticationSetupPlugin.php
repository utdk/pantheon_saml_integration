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
        // $this->io->write('Webroot located at: ' . $web_root);

        // 1. Remove default config and metadata directories from simplesamlphp.
        $this->io->write('[UTexas Pantheon SAML]: Directory cleanup');
        $directories = [
          './vendor/simplesamlphp/simplesamlphp/config',
          './vendor/simplesamlphp/simplesamlphp/metadata',
        ];
        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $this->filesystem->removeDirectory($dir);
            }
        }

        // 2. Generate symlinks required by Pantheon.
        $this->io->write('[UTexas Pantheon SAML]: Generating symlinks');
        $links = [];
        $links[] = [
            'content' => $web_root_depth . 'vendor/simplesamlphp/simplesamlphp/www',
            'symlink' => $web_root . '/simplesaml',
        ];
        $links[] = [
            'content' => '../../../' . $web_root . 'wp-content/uploads/private/saml/assets/config',
            'symlink' => './vendor/simplesamlphp/simplesamlphp/config',
        ];
        $links[] = [
            'content' => '../../../' . $web_root . '/wp-content/uploads/private/saml/assets/metadata ',
            'symlink' => './vendor/simplesamlphp/simplesamlphp/metadata'
        ];
        foreach ($links as $link) {
            // Delete symlink before creating new one.
            if (is_link($link['symlink'])) {
                $this->io->write('[UTexas Pantheon SAML]: Deleting symlink: ' . $link['symlink']);
                unlink($link['symlink']);
            }
            $this->io->write('[UTexas Pantheon SAML]: Making directory: ' . $link['symlink'] . ' a symlink pointing to: ' . $link['content']);
            symlink($link['content'], $link['symlink']);
        }
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
