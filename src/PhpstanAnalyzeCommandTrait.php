<?php

declare(strict_types=1);

namespace Drupal\marvin_phpstan;

use Drupal\marvin\LintCommandTrait;
use Robo\Contract\TaskInterface;
use Sweetchuck\Robo\Phpstan\PhpstanTaskLoader;
use Symfony\Component\Filesystem\Path;

/**
 * @property \Symfony\Component\Filesystem\Filesystem $fs
 * @property \Drupal\marvin\Utils $utils
 * @property \Drush\Config\DrushConfig $drushConfig
 * @property \Psr\Log\LoggerInterface $logger
 */
trait PhpstanAnalyzeCommandTrait {

  use LintCommandTrait;
  use PhpstanTaskLoader;

  /**
   * @param ?\Robo\Symfony\ConsoleIO $io
   *
   * @return \Robo\Collection\CollectionBuilder
   */
  abstract protected function collectionBuilder($io = NULL);

  protected function getLinterInfo(): array {
    return [
      'id' => 'phpstan',
    ];
  }

  protected function getTaskLintPhpstanAnalyzeExtension(
    string $workingDirectory,
    string $composerBinDir,
    string $reportsDir,
    ?string $gitHookName,
  ): TaskInterface {
    if (!$this->hasPhpstanConfiguration($workingDirectory)) {
      // @todo Provide default configuration.
      return $this
        ->collectionBuilder()
        ->addCode(function () use ($workingDirectory): int {
          $this->logger->warning(
            'Phpstan is not supported in directory: {dir}',
            [
              'dir' => $workingDirectory,
            ],
          );

          return 0;
        });
    }

    $backToBinDir = $this->utils->makeRelativePathToComposerBinDir(
      $workingDirectory,
      $this->utils->getProjectRootDir($this->drushConfig),
      $composerBinDir,
    );

    $options['phpstanExecutable'] = Path::join($backToBinDir, 'phpstan');
    $options['workingDirectory'] = $workingDirectory;
    $options += ['lintReporters' => []];
    $options['lintReporters'] += $this->getLintReporters([
      'reportsDir' => $reportsDir,
    ]);
    $options['errorFormat'] = 'json';

    $task = $this->taskPhpstanAnalyze($options);
    if ($gitHookName === 'pre-commit') {
      // @todo Do something better.
      return $this
        ->collectionBuilder()
        ->addCode(function () use ($workingDirectory): int {
          // @todo Add this message to the $state['summaryMessages'] array.
          $this->logger->warning(
            'The actual files and the staged Git content might be different. Directory: {dir}',
            [
              'dir' => $workingDirectory,
            ],
          );

          return 0;
        })
        ->addTask($task);
    }

    return $task;
  }

  protected function hasPhpstanConfiguration(string $workingDirectory): bool {
    return $this->fs->exists("$workingDirectory/phpstan.neon.dist")
      || $this->fs->exists("$workingDirectory/phpstan.dist.neon")
      || $this->fs->exists("$workingDirectory/phpstan.neon");
  }

}
