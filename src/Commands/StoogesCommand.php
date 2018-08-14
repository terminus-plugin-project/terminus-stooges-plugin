<?php

namespace Pantheon\TerminusFiler\Commands;

use Pantheon\Terminus\Collections\Domains;
use Pantheon\Terminus\Collections\Environments;
use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Models\Environment;
use Pantheon\Terminus\Models\Site;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;
use Pantheon\Terminus\Exceptions\TerminusProcessException;

/**
 * Class StoogesCommand
 * Searches for and destroys (replaces) all instances of a pantheonsite.io domain.
 */
class StoogesCommand extends TerminusCommand implements SiteAwareInterface
{
  
  use SiteAwareTrait;

  /**
   * Replaces platform and vanity domains with the chosen custom domain.
   *
   * @authorize
   *
   * @command site:stooges
   * @aliases sar
   * @aliases search-and-replace
   * @aliases search-replace
   * @aliases search-and-destroy
   * @aliases stooges
   *
   * @param string $site_env Site & environment in the format `site-name.env`
   *
   * @usage terminus site:stooges <site>.<env>
   */
  public function stooges($site_env) {

    /** @var Environment $env */
    /** @var Site $site */
    list($site, $env) = $this->getSiteEnv($site_env);
    if ($site->get('framework') != 'wordpress') {
      $this->logger->notice('Refusing to act on site as it is not WordPress.');
      return;
    }
    /** @var Domains $domains */
    $domains = $env->getDomains()->serialize();

    $replaceable_domains = [];
    $target_domains = [];
    foreach ($domains as $domain => $data) {
      if ('custom' == $data['type']) {
        $target_domains[] = $domain;
      }
      else {
        $replaceable_domains[] = $domain;
      }
    }

    if (count($target_domains) < 1) {
      $this->logger->notice('Refusing to act on site as it has no custom domains available.');
      return;
    }

    $target_domain = $this->io()->choice('What domain would you like to use as the replacement domain?', $target_domains);

    $echoOutputFn = function ($type, $buffer) {
    };

    foreach ($replaceable_domains as $replaceable_domain) {
      $command_line = 'wp search-replace ' . $replaceable_domain . ' ' . $target_domain;
      $this->io()->text('Replacing domain ' . $replaceable_domain . ' with ' . $target_domain);
      $result = $env->sendCommandViaSsh($command_line, $echoOutputFn, false);
      $output = $result['output'];
      $exit = $result['exit_code'];

      if ($exit != 0) {
        throw new TerminusProcessException($output);
      }
    }

  }

  /**
   * Replaces platform and vanity domains from all environments with the chosen
   * custom domain.
   *
   * @authorize
   *
   * @command site:stooges:sparky
   * @aliases sparky
   *
   * @param string $site_id Site in the format `site-name`
   *
   * @usage terminus site:stooges:sparky <site>
   */
  public function sparky($site_id) {
    /** @var Site $site */
    $site = $this->getSite($site_id);

    if ($site->get('framework') != 'wordpress') {
      $this->logger->notice('Refusing to act on site as it is not WordPress.');
      return;
    }

    /** @var Environments $environments */
    $environments = $site->getEnvironments()->fetch();
    $environment_ids = $environments->ids();
    /** @var Environment $live_environment */
    $live_environment = $environments->get('live');
    $replaceable_domains = [];
    $target_domains = [];
    foreach ($environment_ids as $environment_id) {
      /** @var Environment $environment */
      $environment = $environments->get($environment_id);
      /** @var Domains $domains */
      $domains = $environment->getDomains()->serialize();
      foreach ($domains as $domain => $data) {
        if ('custom' == $data['type']) {
          $target_domains[] = $domain;
        }
        else {
          $replaceable_domains[] = $domain;
        }
      }
    }

    if (count($target_domains) == 1) {
      $target_domain = array_pop($target_domains);
    }
    elseif (count($target_domains) == 0) {
      $this->logger->notice('Refusing to act on site as their are no custom domains.');
      return;
    }
    else {
      $target_domain = $this->io()->choice('What domain would you like to use as the replacement domain?', $target_domains);
    }

    $echoOutputFn = function ($type, $buffer) {
    };

    foreach ($replaceable_domains as $replaceable_domain) {
      $command_line = 'wp search-replace ' . $replaceable_domain . ' ' . $target_domain;
      $this->io()->text('Replacing domain ' . $replaceable_domain . ' with ' . $target_domain);
      $result = $live_environment->sendCommandViaSsh($command_line, $echoOutputFn, false);
      $output = $result['output'];
      $exit = $result['exit_code'];

      if ($exit != 0) {
        throw new TerminusProcessException($output);
      }
    }

  }

}
