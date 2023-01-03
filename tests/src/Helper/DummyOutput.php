<?php

declare(strict_types = 1);

namespace Drupal\Tests\marvin_phpstan\Helper;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * @method $this getErrorOutput()
 */
class DummyOutput extends ConsoleOutput {

  protected static int $instanceCounter = 0;

  public string $output = '';

  public int $instanceId = 0;

  /**
   * {@inheritdoc}
   */
  public function __construct($verbosity = self::VERBOSITY_NORMAL, $decorated = NULL, OutputFormatterInterface $formatter = NULL, bool $isStdError = FALSE) {
    parent::__construct($verbosity, $decorated, $formatter);
    $this->instanceId = static::$instanceCounter++;

    $errorOutput = $isStdError ?
      $this
      : new static($verbosity, $decorated, $formatter, TRUE);
    $this->setErrorOutput($errorOutput);
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-param string $message
   * @phpstan-param bool $newline
   */
  protected function doWrite($message, $newline) {
    $this->output .= $message . ($newline ? PHP_EOL : '');
  }

}
