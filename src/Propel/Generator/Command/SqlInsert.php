<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Generator\Command;


use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;


use Propel\Generator\Config\GeneratorConfig;
use Propel\Generator\Manager\SqlManager;
use Propel\Generator\Util\Filesystem;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class SqlInsert extends AbstractCommand
{
    const DEFAULT_OUTPUT_DIRECTORY  = 'generated-sql';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption('output-dir', null, InputOption::VALUE_REQUIRED,  'The output directory', self::DEFAULT_OUTPUT_DIRECTORY)
            ->addOption('connection', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Connection to use', array())
            ->setName('sql:insert')
            ->setDescription('Insert SQL statements')
            ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = new SqlManager();

        $connections = array();
        foreach ($input->getOption('connection') as $connection) {
            list($name, $dsn)   = $this->parseConnection($connection);
            $connections[$name] = array('dsn' => $dsn);
        }

        $manager->setConnections($connections);
        $manager->setLoggerClosure(function($message) use ($input, $output) {
            if ($input->getOption('verbose')) {
                $output->writeln($message);
            }
        });
        $manager->setWorkingDirectory($input->getOption('output-dir'));

        $manager->insertSql();
    }

    protected function parseConnection($connection)
    {
        $pos  = strpos($connection, '=');
        $name = substr($connection, 0, $pos);
        $dsn  = substr($connection, $pos + 1, strlen($connection));

        return array($name, $dsn);
    }
}