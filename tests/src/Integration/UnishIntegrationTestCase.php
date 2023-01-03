<?php

declare(strict_types = 1);

namespace Drupal\Tests\marvin_phpstan\Integration;

use Drush\TestTraits\DrushTestTrait;
use Symfony\Component\Filesystem\Path;
use weitzman\DrupalTestTraits\ExistingSiteBase;

class UnishIntegrationTestCase extends ExistingSiteBase {

  use DrushTestTrait;

  protected string $projectName = 'project_01';

  /**
   * {@inheritdoc}
   */
  protected function convertKeyValueToFlag(string $key, mixed $value): string {
    if (!isset($value) || $value === TRUE) {
      return "--$key";
    }

    if (!is_array($value)) {
      return "--$key=" . static::escapeshellarg((string) $value);
    }

    $result = [];
    foreach ($value as $v) {
      $result[] = "--$key=" . static::escapeshellarg((string) $v);
    }

    return implode(' ', $result);
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  protected function getCommonCommandLineOptions(): array {
    return [
      'config' => [
        Path::join($this->getDrupalRoot(), '..', 'drush'),
      ],
    ];
  }

  /**
   * @phpstan-return array<string, string>
   */
  protected function getCommonCommandLineEnvVars(): array {
    return [
      'HOME' => '/dev/null',
    ];
  }

  protected function getProjectRootDir(): string {
    return dirname($this->getDrupalRoot());
  }

  public function getMarvinRootDir(): string {
    return dirname(__DIR__, 3);
  }

  public function getDrupalRoot(): string {
    return Path::join($this->getMarvinRootDir(), "tests/fixtures/{$this->projectName}/docroot");
  }

}
