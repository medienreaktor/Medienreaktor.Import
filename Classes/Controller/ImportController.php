<?php
namespace Medienreaktor\Import\Controller;

use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Domain\Model\NodeTemplate;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Error\Messages\Message;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;

/**
 * Controller for import module
 */
class ImportController extends ActionController
{

    /**
     * @Flow\Inject
     * @var \Medienreaktor\Import\Domain\Model\Import
     */
    protected $import;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * Import module index action
     *
     * @return void
     */
    public function indexAction()
    {
        $nodeTypes = $this->nodeTypeManager->getNodeTypes(false);

        $nodeTypeOptions = [];
        foreach ($nodeTypes as $nodeType) {
            if($nodeType->getName() != 'unstructured') {
                $nodeTypeOptions[$nodeType->getName()] = $nodeType->getName();
            }
        }
        $this->view->assign('nodeTypes', $nodeTypeOptions);
    }

    /**
     * Upload action
     *
     * @return void
     */
    public function uploadAction()
    {
        $args = $this->request->getArguments();

        $this->import->flush();

        if (isset($args['asset'])) {
            $filePath = $_FILES['moduleArguments']['tmp_name']['asset']['resource'];
        }

        if (isset($filePath)) {
            $file = fopen($filePath, 'r');
            $this->import->setDataFromCSV($file, $args['delimiter']);
            $this->import->setParentNodeIdentifier($args['parentNodeIdentifier']);
            $this->import->setTargetNodeType($args['targetNodeType']);
            $this->import->setTargetWorkspace($args['targetWorkspace']);

            $this->redirect('mapping');
        }

        $this->redirect('index');
    }

    /**
     * Mapping action
     *
     * @return void
     */
    public function mappingAction() {
        $data = $this->import->getData();
        $targetNodeType = $this->import->getTargetNodeType();

        $this->view->assign('data', $data);
        $this->view->assign('targetNodeType', $targetNodeType);

        $nodeType = $this->nodeTypeManager->getNodeType($targetNodeType);
        $nodeTypeConfiguration = $nodeType->getFullConfiguration();

        $properties = [];
        $properties['___'] = '';
        if (isset($nodeTypeConfiguration['properties'])) {
            foreach ($nodeTypeConfiguration['properties'] as $key => $value) {
                $properties[$key] = $key;
            }
        }
        $this->view->assign('properties', $properties);
    }

    /**
     * Import action
     *
     * @return void
     */
    public function importAction() {
        $args = $this->request->getArguments();
        $mapping = $args['mapping'];

        $data = $this->import->getData();
        $parentNodeIdentifier = $this->import->getParentNodeIdentifier();
        $targetNodeType = $this->nodeTypeManager->getNodeType($this->import->getTargetNodeType());

        $workspace = $this->import->getTargetWorkspace();

        /** @var ContentContext $contentContext */
        $contentContext = $this->contextFactory->create([
            'workspaceName' => $workspace,
        ]);

        $q = new FlowQuery([$contentContext->getCurrentSiteNode()]);
        $parentNode = $q->find($parentNodeIdentifier)->get(0);

        $nodeTemplate = new NodeTemplate();
        $nodeTemplate->setNodeType($targetNodeType);

        array_shift($data);
        foreach ($data as $row) {
            $node = $parentNode->createNodeFromTemplate($nodeTemplate);

            $i = 0;
            foreach ($row as $col) {
                if (isset($mapping[$i]) && $mapping[$i] != '___') {
                    if ($node->getNodeType()->getPropertyType($mapping[$i]) === 'reference') {
                        $refNode = $q->find($col)->get(0);
                        $node->setProperty($mapping[$i], $refNode);
                    } else {
                        $node->setProperty($mapping[$i], $col);
                    }
                }
                $i++;
            }

            $this->persistenceManager->persistAll();
        }

        $this->import->flush();
        $this->addFlashMessage("Imported $i entries successfully.", "", Message::SEVERITY_OK);
        $this->redirect('index');
    }
}
