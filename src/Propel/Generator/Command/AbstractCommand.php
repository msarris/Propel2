<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Generator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Finder\Finder;

use Propel\Generator\Exception\RuntimeException;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
abstract class AbstractCommand extends Command
{
    const DEFAULT_INPUT_DIRECTORY   = '.';

    const DEFAULT_PLATFORM          = 'MysqlPlatform';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption('platform',  null, InputOption::VALUE_REQUIRED,  'The platform', self::DEFAULT_PLATFORM)
            ->addOption('input-dir', null, InputOption::VALUE_REQUIRED,  'The input directory', self::DEFAULT_INPUT_DIRECTORY)
            ;
    }

    protected function getBuildProperties($file)
    {
        $properties = array();

        if (false === $lines = @file($file)) {
            throw new RuntimeException(sprintf('Unable to parse contents of "%s".', $file));
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ('' == $line || in_array($line[0], array('#', ';'))) {
                continue;
            }

            $pos = strpos($line, '=');
            $properties[trim(substr($line, 0, $pos))] = trim(substr($line, $pos + 1));
        }

        return $properties;
    }

    protected function getSchemas($directory)
    {
        $finder = new Finder();

        return iterator_to_array($finder
            ->name('*schema.xml')
            ->in($directory)
            ->depth(0)
            ->files()
        );
    }

    protected function parseConnection($connection)
    {
        $pos  = strpos($connection, '=');
        $name = substr($connection, 0, $pos);
        $dsn  = substr($connection, $pos + 1, strlen($connection));

        $extras = array();
        foreach (explode(';', $dsn) as $element) {
            $parts = preg_split('/=/', $element);

            if (2 === count($parts)) {
                $extras[strtolower($parts[0])] = $parts[1];
            }
        }

        return array($name, $dsn, $extras);
    }
}
