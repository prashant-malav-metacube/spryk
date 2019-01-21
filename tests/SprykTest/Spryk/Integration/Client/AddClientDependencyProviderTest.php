<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykTest\Spryk\Integration\Client;

use Codeception\Test\Unit;

/**
 * Auto-generated group annotations
 * @group SprykerTest
 * @group Spryk
 * @group Integration
 * @group Client
 * @group AddClientDependencyProviderTest
 * Add your own group annotations below this line
 */
class AddClientDependencyProviderTest extends Unit
{
    /**
     * @var \SprykTest\SprykIntegrationTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testAddsClientDependencyProviderFile(): void
    {
        $this->tester->run($this, [
            '--module' => 'FooBar',
        ]);

        static::assertFileExists($this->tester->getModuleDirectory() . 'src/Spryker/Client/FooBar/FooBarDependencyProvider.php');
    }

    /**
     * @return void
     */
    public function testAddsClientDependencyProviderFileOnProjectLayer(): void
    {
        $this->tester->run($this, [
            '--module' => 'FooBar',
            '--mode' => 'project',
        ]);

        static::assertFileExists(
            $this->tester->getProjectModuleDirectory('FooBar', 'Client')
            . 'FooBarDependencyProvider.php'
        );
    }
}
