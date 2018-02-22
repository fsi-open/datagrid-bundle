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
use FSi\Component\DataGrid\Column\ColumnInterface;
use FSi\Component\DataGrid\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Options;
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

    public function __construct(UrlGeneratorInterface $urlGenerator, RequestStack $requestStack)
    {
        parent::__construct();

        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
    }

    public function getId(): string
    {
        return 'action';
    }

    public function filterValue(ColumnInterface $column, $value)
    {
        $return = [];
        $actions = $column->getOption('actions');

        foreach ($actions as $name => $options) {
            $return[$name] = [];
            $parameters = [];
            $urlAttributes = $options['url_attr'];
            $content = $options['content'];

            if (isset($options['parameters_field_mapping'])) {
                foreach ($options['parameters_field_mapping'] as $parameterName => $mappingField) {
                    if ($mappingField instanceof \Closure) {
                        $parameters[$parameterName] = $mappingField($value);
                    } else {
                        $parameters[$parameterName] = $value[$mappingField];
                    }
                }
            }

            if (isset($options['additional_parameters'])) {
                foreach ($options['additional_parameters'] as $parameterName => $parameterValue) {
                    $parameters[$parameterName] = $parameterValue;
                }
            }

            if ($urlAttributes instanceof \Closure) {
                $urlAttributes = $urlAttributes($value);

                if (!is_array($urlAttributes)) {
                    throw new UnexpectedTypeException(
                        'url_attr option Closure must return new array with url attributes.'
                    );
                }
            }

            if (!isset($urlAttributes['href'])) {
                $urlAttributes['href'] = $this->urlGenerator->generate(
                    $options['route_name'],
                    $parameters,
                    (int) $options['absolute']
                );
            }

            if (isset($content) && $content instanceof \Closure) {
                $content = (string) $content($value);
            }

            $return[$name]['content']  = isset($content) ? $content : $name;
            $return[$name]['field_mapping_values'] = $value;
            $return[$name]['url_attr'] = $urlAttributes;
        }

        return $return;
    }

    public function initOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'actions' => [],
        ]);

        $optionsResolver->setAllowedTypes('actions', 'array');

        $actionOptionsResolver = new OptionsResolver();

        $actionOptionsResolver->setDefaults([
            'redirect_uri' => true,
            'absolute' => false,
            'url_attr' => [],
            'content' => null,
            'parameters_field_mapping' => [],
            'additional_parameters' => [],
        ]);

        $actionOptionsResolver->setAllowedTypes('url_attr', ['array', 'Closure']);
        $actionOptionsResolver->setAllowedTypes('content', ['null', 'string', 'Closure']);
        $actionOptionsResolver->setAllowedTypes('absolute', ['bool', 'int']);
        $actionOptionsResolver->setAllowedTypes('redirect_uri', ['bool', 'string']);
        $actionOptionsResolver->setAllowedTypes('additional_parameters', ['array']);
        $actionOptionsResolver->setAllowedTypes('parameters_field_mapping', ['array']);
        $actionOptionsResolver->setRequired(['route_name']);

        $actionOptionsResolver->setDefault(
            'additional_parameters',
            function (Options $options, array $previousValue): array {
                if ($options['redirect_uri'] !== false) {
                    if (is_string($options['redirect_uri'])) {
                        $previousValue['redirect_uri'] = $options['redirect_uri'];
                    }

                    if ($options['redirect_uri'] === true) {
                        $previousValue['redirect_uri'] = $this->requestStack->getMasterRequest()->getRequestUri();
                    }
                }

                return $previousValue;
            }
        );
        $optionsResolver->setDefault('action_options_resolver', $actionOptionsResolver);
        $optionsResolver->setAllowedTypes('action_options_resolver', OptionsResolver::class);

        $optionsResolver->setNormalizer('actions', function (Options $options, array $value): array {
            $resolvedActionsOptions = [];
            /** @var OptionsResolver $actionOptionsResolver */
            $actionOptionsResolver = $options['action_options_resolver'];

            foreach ($value as $action => $actionOptions) {
                $resolvedActionsOptions[$action] = $actionOptionsResolver->resolve($actionOptions);
            }

            return $resolvedActionsOptions;
        });
    }
}
