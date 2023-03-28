<?php
namespace Medienreaktor\Import\Domain\Model;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("session")
 */
class Import
{

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string
     */
    protected $parentNodeIdentifier = '';

    /**
     * @var string
     */
    protected $targetNodeType = '';

    /**
     * @var string
     */
    protected $targetWorkspace = '';

    /**
     * Get data
     *
     * @return array
     * @Flow\Session(autoStart = TRUE)
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data
     *
     * @param array $data
     * @return void
     * @Flow\Session(autoStart = TRUE)
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Set data from CSV
     *
     * @param string $csv
     * @param string $delimiter
     * @return void
     * @Flow\Session(autoStart = TRUE)
     */
    public function setDataFromCSV($csv, $delimiter = ';')
    {
        $data = [];
        while ($row = fgetcsv($csv, 0, $delimiter, '"')) {
            $data[] = $row;
        }

        $this->data = $data;
    }

    /**
     * Get parentNodeIdentifier
     *
     * @return string
     * @Flow\Session(autoStart = TRUE)
     */
    public function getParentNodeIdentifier()
    {
        return $this->parentNodeIdentifier;
    }

    /**
     * Set parentNodeIdentifier
     *
     * @param string $parentNodeIdentifier
     * @return void
     * @Flow\Session(autoStart = TRUE)
     */
    public function setParentNodeIdentifier($parentNodeIdentifier)
    {
        $this->parentNodeIdentifier = $parentNodeIdentifier;
    }

    /**
     * Get targetNodeType
     *
     * @return string
     * @Flow\Session(autoStart = TRUE)
     */
    public function getTargetNodeType()
    {
        return $this->targetNodeType;
    }

    /**
     * Set targetNodeType
     *
     * @param string $targetNodeType
     * @return void
     * @Flow\Session(autoStart = TRUE)
     */
    public function setTargetNodeType($targetNodeType)
    {
        $this->targetNodeType = $targetNodeType;
    }

    /**
     * Get targetWorkspace
     *
     * @return string
     * @Flow\Session(autoStart = TRUE)
     */
    public function getTargetWorkspace()
    {
        return $this->targetWorkspace;
    }

    /**
     * Set targetWorkspace
     *
     * @param string $targetWorkspace
     * @return void
     * @Flow\Session(autoStart = TRUE)
     */
    public function setTargetWorkspace($targetWorkspace)
    {
        $this->targetWorkspace = $targetWorkspace;
    }

    /**
     * Remove all import data
     *
     * @return void
     */
    public function flush()
    {
        unset($this->data);
        unset($this->parentNodeIdentifier);
        unset($this->targetNodeType);
        unset($this->targetWorkspace);
    }
}
