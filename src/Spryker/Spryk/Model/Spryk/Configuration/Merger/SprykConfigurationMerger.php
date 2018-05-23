<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Spryk\Model\Spryk\Configuration\Merger;

use Spryker\Spryk\Model\Spryk\Configuration\Finder\SprykConfigurationFinderInterface;
use Symfony\Component\Yaml\Yaml;

class SprykConfigurationMerger implements SprykConfigurationMergerInterface
{
    /**
     * @var \Spryker\Spryk\Model\Spryk\Configuration\Finder\SprykConfigurationFinderInterface
     */
    protected $configurationFinder;

    /**
     * @var string
     */
    protected $rootSprykName;

    /**
     * @param \Spryker\Spryk\Model\Spryk\Configuration\Finder\SprykConfigurationFinderInterface $configurationFinder
     * @param string $rootSprykName
     */
    public function __construct(SprykConfigurationFinderInterface $configurationFinder, string $rootSprykName = 'spryk')
    {
        $this->configurationFinder = $configurationFinder;
        $this->rootSprykName = $rootSprykName;
    }

    /**
     * @param array $sprykDefinition
     *
     * @return array
     */
    public function merge(array $sprykDefinition): array
    {
        $rootConfiguration = $this->configurationFinder->find($this->rootSprykName);
        $rootConfiguration = Yaml::parse($rootConfiguration->getContents());

        return $this->doMerge($rootConfiguration, $sprykDefinition);
    }

    /**
     * @param array $rootConfiguration
     * @param array $sprykDefinition
     *
     * @return array
     */
    protected function doMerge(array $rootConfiguration, array $sprykDefinition): array
    {
        $sprykDefinition['arguments'] = $this->mergeArguments(
            $sprykDefinition['arguments'],
            $rootConfiguration['arguments']
        );

        return $sprykDefinition;
    }

    /**
     * @param array $arguments
     * @param array $rootArguments
     *
     * @return array
     */
    protected function mergeArguments(array $arguments, array $rootArguments): array
    {
        $mergedArguments = [];
        foreach ($arguments as $argumentName => $argumentDefinition) {
            if (!isset($rootArguments[$argumentName]) || $rootArguments[$argumentName] === null) {
                $mergedArguments[$argumentName] = $argumentDefinition;
                continue;
            }

            $mergedArgumentDefinition = $this->getMergedArgumentDefinition($rootArguments, $argumentName, $argumentDefinition);
            $mergedArguments[$argumentName] = $mergedArgumentDefinition;
        }

        return $mergedArguments;
    }

    /**
     * @param array $rootArguments
     * @param string $argumentName
     * @param array $argumentDefinition
     *
     * @return array
     */
    protected function getMergedArgumentDefinition(array $rootArguments, string $argumentName, array $argumentDefinition): array
    {
        $mergeType = $rootArguments[$argumentName]['type'];
        $mergeValue = $rootArguments[$argumentName]['value'];

        $mergedArgumentDefinition = [];

        foreach ($argumentDefinition as $definitionKey => $definitionValue) {
            if ($mergeType === 'prepend') {
                $mergedArgumentDefinition[$definitionKey] = $mergeValue . $definitionValue;
            }
        }

        return $mergedArgumentDefinition;
    }
}