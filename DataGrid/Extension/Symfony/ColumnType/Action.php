<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\ColumnType;

use FSi\Component\DataGrid\Column\ColumnAbstractType;
use FSi\Component\DataGrid\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Action extends ColumnAbstractType
{
    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var OptionsResolver
     */
    protected $actionOptionsResolver;

    public function __construct(UrlGeneratorInterface $urlGenerator, RequestStack $requestStack)
    {
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
        $this->actionOptionsResolver = new OptionsResolver();
    }

    public function getId(): string
    {
        return 'action';
    }

    public function filterValue($value)
    {
        $return = [];
        $actions = $this->getOption('actions');

        foreach ($actions as $name => $options) {
            $options = $this->actionOptionsResolver->resolve((array) $options);
            $return[$name] = [];
            $parameters = [];
            $urlAttributes = $options['url_attr'];
            $content = $options['content'];

            if (isset($options['parameters_field_mapping'])) {
                foreach ($options['parameters_field_mapping'] as $parameterName => $mappingField) {
                    if ($mappingField instanceof \Closure) {
                        $parameters[$parameterName] = $mappingField($value, $this->getIndex());
                    } else {
                        $parameters[$parameterName] = $value[$mappingField];
                    }
                }
            }

            if (isset($options['additional_parameters'])) {
                foreach ($options['additional_parameters'] as $parameterValueName => $parameterValue) {
                    $parameters[$parameterValueName] = $parameterValue;
                }
            }

            if ($options['redirect_uri'] !== false) {
                if (is_string($options['redirect_uri'])) {
                    $parameters['redirect_uri'] = $options['redirect_uri'];
                }

                if ($options['redirect_uri'] === true) {
                    $parameters['redirect_uri'] = $this->requestStack->getMasterRequest()->getRequestUri();
                }
            }

            if ($urlAttributes instanceof \Closure) {
                $urlAttributes = $urlAttributes($value, $this->getIndex());

                if (!is_array($urlAttributes)) {
                    throw new UnexpectedTypeException(
                        'url_attr option Closure must return new array with url attributes.'
                    );
                }
            }

            $url = $this->urlGenerator->generate($options['route_name'], $parameters, $options['absolute']);

            if (!isset($urlAttributes['href'])) {
                $urlAttributes['href'] = $url;
            }

            if (isset($content) && $content instanceof \Closure) {
                $content = (string) $content($value, $this->getIndex());
            }

            $return[$name]['content']  = isset($content) ? $content : $name;
            $return[$name]['field_mapping_values'] = $value;
            $return[$name]['url_attr'] = $urlAttributes;
        }

        return $return;
    }

    public function initOptions(): void
    {
        $this->getOptionsResolver()->setDefaults([
            'actions' => [],
        ]);

        $this->getOptionsResolver()->setAllowedTypes('actions', 'array');

        $this->actionOptionsResolver->setDefaults([
            'redirect_uri' => true,
            'absolute' => UrlGeneratorInterface::ABSOLUTE_PATH,
            'url_attr' => [],
            'content' => null,
            'parameters_field_mapping' => [],
            'additional_parameters' => [],
        ]);

        $this->actionOptionsResolver->setAllowedTypes('url_attr', ['array', 'Closure']);
        $this->actionOptionsResolver->setAllowedTypes('content', ['null', 'string', 'Closure']);

        $this->actionOptionsResolver->setRequired([
            'route_name',
        ]);
    }

    public function getActionOptionsResolver(): OptionsResolver
    {
        return $this->actionOptionsResolver;
    }
}
