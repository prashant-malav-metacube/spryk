<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Spryk\Model\Spryk\Builder\Template;

use SprykerSdk\Spryk\Exception\YmlException;
use SprykerSdk\Spryk\Model\Spryk\Builder\Resolver\FileResolverInterface;
use SprykerSdk\Spryk\Model\Spryk\Builder\SprykBuilderInterface;
use SprykerSdk\Spryk\Model\Spryk\Builder\Template\Renderer\TemplateRendererInterface;
use SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface;
use SprykerSdk\Spryk\Style\SprykStyleInterface;
use Symfony\Component\Yaml\Yaml;

class UpdateYmlSpryk implements SprykBuilderInterface
{
    /**
     * @var string
     */
    public const ARGUMENT_TARGET_PATH = 'targetPath';

    /**
     * @var string
     */
    public const ARGUMENT_TEMPLATE = 'template';

    /**
     * @var string
     */
    public const ARGUMENT_AFTER_ELEMENT = 'afterElement';

    /**
     * @var string
     */
    public const ARGUMENT_ADD_TO_ELEMENT = 'addToElement';

    /**
     * @var string
     */
    public const ARGUMENT_CONTENT = 'content';

    /**
     * @var int
     */
    public const YAML_START_INLINE_LEVEL = 10;

    /**
     * @var \SprykerSdk\Spryk\Model\Spryk\Builder\Template\Renderer\TemplateRendererInterface
     */
    protected TemplateRendererInterface $renderer;

    /**
     * @var \SprykerSdk\Spryk\Model\Spryk\Builder\Resolver\FileResolverInterface
     */
    protected FileResolverInterface $fileResolver;

    /**
     * @var string
     */
    protected string $rootDirectory;

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Builder\Template\Renderer\TemplateRendererInterface $renderer
     * @param \SprykerSdk\Spryk\Model\Spryk\Builder\Resolver\FileResolverInterface $fileResolver
     * @param string $rootDirectory
     */
    public function __construct(TemplateRendererInterface $renderer, FileResolverInterface $fileResolver, string $rootDirectory)
    {
        $this->renderer = $renderer;
        $this->fileResolver = $fileResolver;
        $this->rootDirectory = $rootDirectory;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'update-yml';
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return bool
     */
    public function shouldBuild(SprykDefinitionInterface $sprykDefinition): bool
    {
        return true;
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     * @param \SprykerSdk\Spryk\Style\SprykStyleInterface $style
     *
     * @throws \SprykerSdk\Spryk\Exception\YmlException
     *
     * @return void
     */
    public function build(SprykDefinitionInterface $sprykDefinition, SprykStyleInterface $style): void
    {
        /** @var \SprykerSdk\Spryk\Model\Spryk\Builder\Resolver\Resolved\ResolvedYmlInterface $resolved */
        $resolved = $this->fileResolver->resolve($this->getTargetPath($sprykDefinition));

        if (empty($resolved->getDecodedYml())) {
            throw new YmlException(sprintf('The YML file "%s" is empty or it was not able to parse it.', $this->getTargetPath($sprykDefinition)));
        }

        $targetYml = $this->prepareTargetYaml($sprykDefinition, $resolved->getDecodedYml());
        $resolved->setDecodedYml($this->updateYaml($sprykDefinition, $targetYml));

        $style->report(sprintf('Updated <fg=green>%s</>', $this->getTargetPath($sprykDefinition)));
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     * @param array $targetYaml
     *
     * @return array
     */
    protected function prepareTargetYaml(SprykDefinitionInterface $sprykDefinition, array $targetYaml): array
    {
        $addToElement = $this->getAddToElement($sprykDefinition);
        $afterElement = $this->getAfterElement($sprykDefinition);

        $newTargetYaml = [];

        if (!isset($targetYaml[$addToElement])) {
            foreach ($targetYaml as $key => $value) {
                $newTargetYaml[$key] = $value;
                if ($key === $afterElement) {
                    $newTargetYaml[$addToElement] = [];
                }
            }
            $targetYaml = $newTargetYaml;
        }

        return $targetYaml;
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return string
     */
    protected function getTargetPath(SprykDefinitionInterface $sprykDefinition): string
    {
        $targetPath = $sprykDefinition
            ->getArgumentCollection()
            ->getArgument(static::ARGUMENT_TARGET_PATH)
            ->getValue();

        return $this->rootDirectory . $targetPath;
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return mixed
     */
    protected function getYamlToAdd(SprykDefinitionInterface $sprykDefinition)
    {
        if ($sprykDefinition->getArgumentCollection()->hasArgument(static::ARGUMENT_CONTENT)) {
            return $sprykDefinition->getArgumentCollection()->getArgument(static::ARGUMENT_CONTENT)->getValue();
        }

        return $this->getDataForYml($sprykDefinition);
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return array
     */
    protected function getDataForYml(SprykDefinitionInterface $sprykDefinition): array
    {
        $content = $this->getContent($sprykDefinition, $this->getTemplateName($sprykDefinition));
        $yaml = Yaml::parse($content);

        return $yaml;
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return string
     */
    protected function getTemplateName(SprykDefinitionInterface $sprykDefinition): string
    {
        $templateName = $sprykDefinition
            ->getArgumentCollection()
            ->getArgument(static::ARGUMENT_TEMPLATE)
            ->getValue();

        return $templateName;
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     * @param string $templateName
     *
     * @return string
     */
    protected function getContent(SprykDefinitionInterface $sprykDefinition, string $templateName): string
    {
        return $this->renderer->render(
            $templateName,
            $sprykDefinition->getArgumentCollection()->getArguments(),
        );
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     * @param array $targetYaml
     *
     * @return array
     */
    protected function updateYaml(SprykDefinitionInterface $sprykDefinition, array $targetYaml): array
    {
        $content = $this->getYamlToAdd($sprykDefinition);
        $addToElement = $this->getAddToElement($sprykDefinition);

        if (is_array($content)) {
            $targetYaml[$addToElement] = array_merge($targetYaml[$addToElement], $content);

            return $targetYaml;
        }

        if (in_array($content, $targetYaml[$addToElement], true)) {
            return $targetYaml;
        }

        $targetYaml[$addToElement][] = $content;

        return $targetYaml;
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return string
     */
    protected function getAddToElement(SprykDefinitionInterface $sprykDefinition): string
    {
        $addToElement = $sprykDefinition
            ->getArgumentCollection()
            ->getArgument(static::ARGUMENT_ADD_TO_ELEMENT)
            ->getValue();

        return $addToElement;
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return string
     */
    protected function getAfterElement(SprykDefinitionInterface $sprykDefinition): string
    {
        $afterElement = $sprykDefinition
            ->getArgumentCollection()
            ->getArgument(static::ARGUMENT_AFTER_ELEMENT)
            ->getValue();

        return $afterElement;
    }
}