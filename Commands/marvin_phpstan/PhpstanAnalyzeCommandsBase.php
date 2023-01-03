<?php

declare(strict_types = 1);

namespace Drush\Commands\marvin_phpstan;

use Drush\Commands\marvin\LintCommandsBase;
use Robo\Contract\TaskInterface;
use Sweetchuck\Robo\Phpstan\PhpstanTaskLoader;
use Symfony\Component\Filesystem\Path;

class PhpstanAnalyzeCommandsBase extends LintCommandsBase {

  use PhpstanTaskLoader;

  protected static string $classKeyPrefix = 'marvin.phpstan';

  protected string $customEventNamePrefix = 'marvin:phpstan';

  protected function getTaskLintPhpstanAnalyzeExtension(string $workingDirectory): TaskInterface {
    if (!$this->hasPhpstanConfiguration($workingDirectory)) {
      // @todo Provide default configuration.
      return $this
        ->collectionBuilder()
        ->addCode(function () use ($workingDirectory): int {
          $this
            ->getLogger()
            ->warning(
              'Phpstan is not supported in directory: {dir}',
              [
                'dir' => $workingDirectory,
              ],
            );

          return 0;
        });
    }

    $config = $this->getConfig();
    $gitHook = $config->get('marvin.gitHook');

    $presetName = $this->getPresetNameByEnvironmentVariant();
    $options = $this->getConfigValue("preset.$presetName", []);
    $options['phpstanExecutable'] = Path::join(
      $this->makeRelativePathToComposerBinDir($workingDirectory),
      'phpstan',
    );
    $options['workingDirectory'] = $workingDirectory;
    $options += ['lintReporters' => []];
    $options['lintReporters'] += $this->getLintReporters();

    $task = $this->taskPhpstanAnalyze($options);
    if ($gitHook === 'pre-commit') {
      // @todo Do something better.
      return $this
        ->collectionBuilder()
        ->addCode(function () use ($workingDirectory): int {
          $this
            ->getLogger()
            ->warning(
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
    return file_exists("$workingDirectory/phpstan.neon.dist")
      || file_exists("$workingDirectory/phpstan.dist.neon")
      || file_exists("$workingDirectory/phpstan.neon");
  }

}
