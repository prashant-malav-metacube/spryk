<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Spryk\Model\Spryk\Definition;

use Spryker\Spryk\Model\Spryk\Definition\Argument\Collection\ArgumentCollectionInterface;

interface SprykDefinitionInterface
{
    /**
     * @return string
     */
    public function getBuilder(): string;

    /**
     * @param string $builder
     *
     * @return \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface
     */
    public function setBuilder(string $builder): self;

    /**
     * @return string
     */
    public function getSprykName(): string;

    /**
     * @param string $sprykName
     *
     * @return \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface
     */
    public function setSprykName(string $sprykName): self;

    /**
     * @param array $config
     *
     * @return $this
     */
    public function setConfig(array $config): self;

    /**
     * @return array
     */
    public function getConfig(): array;

    /**
     * @return \Spryker\Spryk\Model\Spryk\Definition\Argument\Collection\ArgumentCollectionInterface
     */
    public function getArgumentCollection(): ArgumentCollectionInterface;

    /**
     * @param \Spryker\Spryk\Model\Spryk\Definition\Argument\Collection\ArgumentCollectionInterface $argumentCollection
     *
     * @return \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface
     */
    public function setArgumentCollection(ArgumentCollectionInterface $argumentCollection): self;

    /**
     * @return \Spryker\Spryk\Model\Spryk\Definition\SprykDefinition[]
     */
    public function getPreSpryks(): array;

    /**
     * @param \Spryker\Spryk\Model\Spryk\Definition\SprykDefinition[] $preSpryks
     *
     * @return \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface
     */
    public function setPreSpryks(array $preSpryks): self;

    /**
     * @return \Spryker\Spryk\Model\Spryk\Definition\SprykDefinition[]
     */
    public function getPostSpryks(): array;

    /**
     * @param \Spryker\Spryk\Model\Spryk\Definition\SprykDefinition[] $postSpryks
     *
     * @return \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface
     */
    public function setPostSpryks(array $postSpryks): self;

    /**
     * @return string|null
     */
    public function getMode(): ?string;

    /**
     * @param string $mode
     *
     * @return \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface
     */
    public function setMode(string $mode): self;
}
