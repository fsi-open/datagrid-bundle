<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\Loader;

use FSi\Component\DataGrid\DataGridInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Parser as YamlParser;

class YamlFileLoader extends FileLoader
{
    /**
     * @var \Symfony\Component\Yaml\Parser
     */
    private $yamlParser;

    /**
     * @var \FSi\Component\DataGrid\DataGridInterface
     */
    private $dataGrid;

    /**
     * @param \FSi\Component\DataGrid\DataGridInterface $dataGrid
     */
    public function setDataGrid(DataGridInterface $dataGrid)
    {
        $this->dataGrid = $dataGrid;
    }

    /**
     * Loads a resource.
     *
     * @param mixed $file The resource
     * @param string $type The resource type
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
     * Returns true if this class supports the given resource.
     *
     * @param mixed $resource A resource
     * @param string $type The resource type
     *
     * @return bool    true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo($resource, PATHINFO_EXTENSION);
    }

    /**
     * Loads a YAML file.
     *
     * @param string $file
     *
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
     * Parses all imports
     *
     * @param array  $content
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
