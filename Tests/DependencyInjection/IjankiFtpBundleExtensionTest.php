<?php

namespace Ijanki\Bundle\FtpBundle\Tests\DependencyInjection;

use Ijanki\Bundle\FtpBundle\DependencyInjection\IjankiFtpExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class IjankiFtpBundleExtensionTest extends \PHPUnit_Framework_Testcase
{
    public function testDefault()
    {
        $container = new ContainerBuilder();
        $loader = new IjankiFtpExtension();
        $loader->load(array(array()), $container);
        $this->assertTrue($container->hasDefinition('ijanki_ftp'), 'The extension is loaded');
    }
}
