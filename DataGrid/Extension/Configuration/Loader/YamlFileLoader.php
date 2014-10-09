<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\Loader;

use Symfony\Component\Yaml\Parser as YamlParser;

class YamlFileLoader extends FileLoader
{
    /**
     * @var \Symfony\Component\Yaml\Parser
     */
    private $yamlParser;

    /**
     * @inheritdoc
     */
    public function load($file, $type = null)
    {
        if (null === $this->dataGrid) {
            throw new \BadMethodCallException(sprintf('setDataGrid() must be called before load() on %s', __CLASS__));
        }

        $path = $this->locator->locate($file);

        $content = $this->loadFile($path);

        if (null === $content) {
            return;
        }

        $this->parseImports($content, $path);

        $this->addColumns($content);
    }

    /**
     * @inheritdoc
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo($resource, PATHINFO_EXTENSION);
    }

    /**
     * @param string $file
     * @return array The file content
     */
    private function loadFile($file)
    {
        if (!stream_is_local($file)) {
            throw new \InvalidArgumentException(sprintf('This is not a local file "%s".', $file));
        }

        if (!file_exists($file)) {
            throw new \InvalidArgumentException(sprintf('The config file "%s" is not valid.', $file));
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }

        return $this->yamlParser->parse(file_get_contents($file));
    }

    /**
     * @param array $content
     * @param string $file
     */
    private function parseImports($content, $file)
    {
        if (!isset($content['imports'])) {
            return;
        }

        foreach ($content['imports'] as $import) {
            $this->setCurrentDir(dirname($file));
            $this->import($import['resource'], null, isset($import['ignore_errors']) ? (bool) $import['ignore_errors'] : false, $file);
        }
    }

    /**
     * @param array $content
     */
    private function addColumns(array $content)
    {
        foreach ($content['columns'] as $name => $column) {
            $type = array_key_exists('type', $column)
                ? $column['type']
                : 'text';
            $options = array_key_exists('options', $column)
                ? $column['options']
                : array();

            $this->dataGrid->addColumn($name, $type, $options);
        }
    }
}
