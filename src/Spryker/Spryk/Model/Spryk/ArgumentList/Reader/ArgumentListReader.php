<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Spryk\Model\Spryk\ArgumentList\Reader;

use Spryker\Spryk\Model\Spryk\ArgumentList\Builder\ArgumentListBuilderInterface;
use Spryker\Spryk\Model\Spryk\Dumper\SprykDefinitionDumperInterface;
use Symfony\Component\Yaml\Yaml;

class ArgumentListReader implements ArgumentListReaderInterface
{
    /**
     * @var string
     */
    protected $argumentListFilePath;

    /**
     * @var \Spryker\Spryk\Model\Spryk\ArgumentList\Builder\ArgumentListBuilderInterface
     */
    protected $argumentListBuilder;

    /**
     * @var \Spryker\Spryk\Model\Spryk\Dumper\SprykDefinitionDumperInterface
     */
    protected $definitionDumper;

    /**
     * @param string $argumentListFilePath
     * @param \Spryker\Spryk\Model\Spryk\ArgumentList\Builder\ArgumentListBuilderInterface $argumentListBuilder
     * @param \Spryker\Spryk\Model\Spryk\Dumper\SprykDefinitionDumperInterface $definitionDumper
     */
    public function __construct(
        string $argumentListFilePath,
        ArgumentListBuilderInterface $argumentListBuilder,
        SprykDefinitionDumperInterface $definitionDumper
    ) {
        $this->argumentListFilePath = $argumentListFilePath;
        $this->argumentListBuilder = $argumentListBuilder;
        $this->definitionDumper = $definitionDumper;
    }

    /**
     * @return array
     */
    public function getArgumentList(): array
    {
        if (file_exists($this->argumentListFilePath)) {
            return $this->getCachedArgumentList();
        }

        $sprykDefinition = $this->definitionDumper->dump();

        return $this->argumentListBuilder->buildArgumentList($sprykDefinition);
    }

    /**
     * @return array
     */
    protected function getCachedArgumentList(): array
    {
        $argumentList = Yaml::parseFile($this->argumentListFilePath);

        if ($argumentList === null) {
            return [];
        }

        return $argumentList;
    }
}