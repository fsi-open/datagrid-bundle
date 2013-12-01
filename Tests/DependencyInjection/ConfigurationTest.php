<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\Tests\DependencyInjection;

use FSi\Bundle\DataGridBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

/**
 * @author Norbert Orzechowicz <norbert@fsi.pl>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultOptions()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), array('fsi_data_grid' => array()));

        $this->assertSame(
            $config,
            self::getBundleDefaultOptions()
        );
    }

    public static function getBundleDefaultOptions()
    {
        return array(
            'yaml_configuration' => true,
            'twig' => array(
                'enabled' => true,
                'template' => 'datagrid.html.twig'
            )
        );
    }
}
