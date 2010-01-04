<?php

namespace Symfony\Components\CLI\Output;

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * StreamOutput writes the output to a given stream.
 *
 * Usage:
 *
 * $output = new StreamOutput(fopen('php://stdout', 'w'));
 *
 * As `StreamOutput` can use any stream, you can also use a file:
 *
 * $output = new StreamOutput(fopen('/path/to/output.log', 'a', false));
 *
 * @package    symfony
 * @subpackage cli
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class StreamOutput extends Output
{
  protected $stream;

  /**
   * Constructor.
   *
   * @param mixed   $stream    A stream resource
   * @param integer $verbosity The verbosity level (self::VERBOSITY_QUIET, self::VERBOSITY_NORMAL, self::VERBOSITY_VERBOSE)
   * @param Boolean $decorated Whether to decorate messages or not (null for auto-guessing)
   */
  public function __construct($stream, $verbosity = self::VERBOSITY_NORMAL, $decorated = null)
  {
    if (!is_resource($stream) || 'stream' !== get_resource_type($stream))
    {
      throw new \InvalidArgumentException('The StreamOutput class needs a stream as its first argument.');
    }

    $this->stream = $stream;

    if (null === $decorated)
    {
      $decorated = $this->hasColorSupport($decorated);
    }

    parent::__construct($verbosity, $decorated);
  }

  /**
   * Gets the stream attached to this StreamOutput instance.
   *
   * @return resource A stream resource
   */
  public function getStream()
  {
    return $this->stream;
  }

  /**
   * Writes a message to the output.
   *
   * @param string $message A message to write to the output
   */
  public function doWrite($message)
  {
    if (false === @fwrite($this->stream, $message.PHP_EOL))
    {
      // @codeCoverageIgnoreStart
      // should never happen
      throw new \RuntimeException('Unable to write output.');
      // @codeCoverageIgnoreEnd
    }

    flush();
  }

  /**
   * Returns true if the stream supports colorization.
   *
   * Colorization is disabled if not supported by the stream:
   *
   *  -  windows without ansicon
   *  -  non tty consoles
   *
   * @return Boolean true if the stream supports colorization, false otherwise
   */
  protected function hasColorSupport()
  {
    // @codeCoverageIgnoreStart
    if (DIRECTORY_SEPARATOR == '\\')
    {
      return false !== getenv('ANSICON');
    }
    else
    {
      return function_exists('posix_isatty') && @posix_isatty($this->stream);
    }
    // @codeCoverageIgnoreEnd
  }
}
