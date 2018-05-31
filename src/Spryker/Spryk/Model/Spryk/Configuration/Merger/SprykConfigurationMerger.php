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

        $sprykDefinition = $this->doMerge($rootConfiguration, $sprykDefinition);
        $sprykDefinition = $this->doMergeSubSpryks($rootConfiguration, $sprykDefinition);

        return $sprykDefinition;
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

        $sprykDefinition['arguments'] = $this->addRootArguments(
            $sprykDefinition['arguments'],
            $rootConfiguration['arguments']
        );

        return $sprykDefinition;
    }

    /**
     * @param array $rootConfiguration
     * @param array $sprykDefinition
     *
     * @return array
     */
    protected function doMergeSubSpryks(array $rootConfiguration, array $sprykDefinition): array
    {
        if (isset($sprykDefinition['postSpryks'])) {
            $sprykDefinition['postSpryks'] = $this->mergeSubSprykArguments($sprykDefinition['postSpryks'], $rootConfiguration);
        }

        if (isset($sprykDefinition['preSpryks'])) {
            $sprykDefinition['preSpryks'] = $this->mergeSubSprykArguments($sprykDefinition['preSpryks'], $rootConfiguration);
        }

        return $sprykDefinition;
    }

    /**
     * @param array $subSpryks
     * @param array $rootConfiguration
     *
     * @return array
     */
    protected function mergeSubSprykArguments(array $subSpryks, array $rootConfiguration): array
    {
        $mergedSubSpryks = [];
        foreach ($subSpryks as $subSpryk) {
            if (!is_array($subSpryk)) {
                $mergedSubSpryks[] = $subSpryk;

                continue;
            }
            $sprykName = array_keys($subSpryk)[0];
            $subSprykDefinition = $subSpryk[$sprykName];

            if (isset($subSprykDefinition['arguments'])) {
                $mergedArguments = $this->mergeArguments($subSprykDefinition['arguments'], $rootConfiguration['arguments']);
                $subSpryk[$sprykName]['arguments'] = $mergedArguments;
            }

            $mergedSubSpryks[] = $subSpryk;
        }

        return $mergedSubSpryks;
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

    /**
     * @param array $arguments
     * @param array $rootArguments
     *
     * @return array
     */
    protected function addRootArguments(array $arguments, array $rootArguments): array
    {
        $mergedArguments = $arguments;
        foreach ($rootArguments as $argumentName => $argumentDefinition) {
            if (isset($arguments[$argumentName])) {
                continue;
            }

            $mergedArguments[$argumentName] = $argumentDefinition;
        }

        return $mergedArguments;
    }
}
