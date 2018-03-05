<?php
namespace Neos\Photon\ContentRepository\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Utility\ObjectAccess;
use Neos\Utility\Arrays;
use Neos\Utility\PositionalArraySorter;
use Neos\Photon\ContentRepository\Domain\Service\StaticNodeTypeManager;

class StaticNodeType
{

    /**
     * Name of this static node type. Example: "ContentRepository:Folder"
     *
     * @var string
     */
    protected $name;

    /**
     * Configuration for this node type, can be an arbitrarily nested array. Does not include inherited configuration.
     *
     * @var array
     */
    protected $localConfiguration;

    /**
     * Full configuration for this node type, can be an arbitrarily nested array. Includes any inherited configuration.
     *
     * @var array
     */
    protected $fullConfiguration;

    /**
     * Is this node type marked abstract
     *
     * @var boolean
     */
    protected $abstract = false;

    /**
     * Is this node type marked final
     *
     * @var boolean
     */
    protected $final = false;

    /**
     * node types this node type directly inherits from
     *
     * @var array<\Neos\ContentRepository\Domain\Model\NodeType>
     */
    protected $declaredSuperTypes;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var StaticNodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * Whether or not this node type has been initialized (e.g. if it has been postprocessed)
     *
     * @var boolean
     */
    protected $initialized = false;

    /**
     * Constructs this node type
     *
     * @param string $name Name of the node type
     * @param array $declaredSuperTypes Parent types of this node type
     * @param array $configuration the configuration for this node type which is defined in the schema
     * @throws \InvalidArgumentException
     */
    public function __construct($name, array $declaredSuperTypes, array $configuration, array $fullConfiguration = null)
    {
        $this->name = $name;

        foreach ($declaredSuperTypes as $type) {
            if ($type !== null && !$type instanceof StaticNodeType) {
                throw new \InvalidArgumentException('$declaredSuperTypes must be an array of NodeType objects', 1291300950);
            }
        }
        $this->declaredSuperTypes = $declaredSuperTypes;

        if (isset($configuration['abstract']) && $configuration['abstract'] === true) {
            $this->abstract = true;
            unset($configuration['abstract']);
        }

        if (isset($configuration['final']) && $configuration['final'] === true) {
            $this->final = true;
            unset($configuration['final']);
        }

        $this->localConfiguration = $configuration;

        $this->fullConfiguration = $fullConfiguration;
        if ($fullConfiguration !== null) {
            $this->initialized = true;
        }
    }

    /**
     * Initializes this node type
     *
     * @return void
     */
    protected function initialize()
    {
        if ($this->initialized === true) {
            return;
        }
        $this->initialized = true;
        $this->buildFullConfiguration();
    }

    /**
     * Builds the full configuration by merging configuration from the supertypes into the local configuration.
     *
     * @return void
     */
    protected function buildFullConfiguration()
    {
        $mergedConfiguration = array();
        $applicableSuperTypes = $this->buildInheritanceChain();
        foreach ($applicableSuperTypes as $key => $superType) {
            $mergedConfiguration = Arrays::arrayMergeRecursiveOverrule($mergedConfiguration, $superType->getLocalConfiguration());
        }
        $this->fullConfiguration = Arrays::arrayMergeRecursiveOverrule($mergedConfiguration, $this->localConfiguration);

        if (isset($this->fullConfiguration['childNodes']) && is_array($this->fullConfiguration['childNodes']) && $this->fullConfiguration['childNodes'] !== array()) {
            $sorter = new PositionalArraySorter($this->fullConfiguration['childNodes']);
            $this->fullConfiguration['childNodes'] = $sorter->toArray();
        }
    }

    /**
     * Returns a flat list of super types to inherit from.
     *
     * @return array
     */
    protected function buildInheritanceChain()
    {
        $superTypes = array();
        foreach ($this->declaredSuperTypes as $superTypeName => $superType) {
            if ($superType !== null) {
                $this->addInheritedSuperTypes($superTypes, $superType);
                $superTypes[$superTypeName] = $superType;
            }
        }

        foreach ($this->declaredSuperTypes as $superTypeName => $superType) {
            if ($superType === null) {
                unset($superTypes[$superTypeName]);
            }
        }

        return array_unique($superTypes);
    }

    /**
     * Recursively add super types
     *
     * @param array $superTypes
     * @param StaticNodeType $superType
     * @return void
     */
    protected function addInheritedSuperTypes(array &$superTypes, StaticNodeType $superType)
    {
        foreach ($superType->getDeclaredSuperTypes() as $inheritedSuperTypeName => $inheritedSuperType) {
            $this->addInheritedSuperTypes($superTypes, $inheritedSuperType);
            $superTypes[$inheritedSuperTypeName] = $inheritedSuperType;
        }

        $superTypesInSuperType = $superType->getConfiguration('superTypes') ?: [];
        foreach ($superTypesInSuperType as $inheritedSuperTypeName => $inheritedSuperType) {
            if (!$inheritedSuperType) {
                unset($superTypes[$inheritedSuperTypeName]);
            }
        }
    }

    /**
     * Returns the name of this node type
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return boolean TRUE if marked abstract
     *
     * @return boolean
     */
    public function isAbstract()
    {
        return $this->abstract;
    }

    /**
     * Return boolean TRUE if marked final
     *
     * @return boolean
     */
    public function isFinal()
    {
        return $this->final;
    }

    /**
     * Returns the direct, explicitly declared super types
     * of this node type.
     *
     * Note: NULL values are skipped since they are used only internally.
     *
     * @return array<NodeType>
     */
    public function getDeclaredSuperTypes()
    {
        return array_filter($this->declaredSuperTypes, function ($value) {
            return $value !== null;
        });
    }

    /**
     * If this node type or any of the direct or indirect super types
     * has the given name.
     *
     * @param string $nodeType
     * @return boolean TRUE if this node type is of the given kind, otherwise FALSE
     */
    public function isOfType($nodeType)
    {
        if ($nodeType === $this->name) {
            return true;
        }
        foreach ($this->declaredSuperTypes as $superType) {
            if ($superType !== null && $superType->isOfType($nodeType) === true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the local configuration of the node type. Should only be used internally.
     *
     * Note: post processing is not applied to this.
     *
     * @return array
     */
    public function getLocalConfiguration()
    {
        return $this->localConfiguration;
    }

    /**
     * Get the full configuration of the node type. Should only be used internally.
     *
     * Instead, use the hasConfiguration()/getConfiguration() methods to check/retrieve single configuration values.
     *
     * @return array
     */
    public function getFullConfiguration()
    {
        $this->initialize();
        return $this->fullConfiguration;
    }

    /**
     * Checks if the configuration of this node type contains a setting for the given $configurationPath
     *
     * @param string $configurationPath The name of the configuration option to verify
     * @return boolean
     */
    public function hasConfiguration($configurationPath)
    {
        return $this->getConfiguration($configurationPath) !== null;
    }

    /**
     * Returns the configuration option with the specified $configurationPath or NULL if it does not exist
     *
     * @param string $configurationPath The name of the configuration option to retrieve
     * @return mixed
     */
    public function getConfiguration($configurationPath)
    {
        $this->initialize();
        return ObjectAccess::getPropertyPath($this->fullConfiguration, $configurationPath);
    }

    /**
     * Get the human-readable label of this node type
     *
     * @return string
     */
    public function getLabel()
    {
        $this->initialize();
        return isset($this->fullConfiguration['ui']['label']) ? $this->fullConfiguration['ui']['label'] : '';
    }

    /**
     * Get additional options (if specified)
     *
     * @return array
     */
    public function getOptions()
    {
        $this->initialize();
        return (isset($this->fullConfiguration['options']) ? $this->fullConfiguration['options'] : array());
    }

    /**
     * Return the array with the defined properties. The key is the property name,
     * the value the property configuration. There are no guarantees on how the
     * property configuration looks like.
     *
     * @return array
     */
    public function getProperties()
    {
        $this->initialize();
        return (isset($this->fullConfiguration['properties']) ? $this->fullConfiguration['properties'] : array());
    }

    /**
     * Returns the configured type of the specified property
     *
     * @param string $propertyName Name of the property
     * @return string
     */
    public function getPropertyType($propertyName)
    {
        if (!isset($this->fullConfiguration['properties']) || !isset($this->fullConfiguration['properties'][$propertyName]) || !isset($this->fullConfiguration['properties'][$propertyName]['type'])) {
            return 'string';
        }
        return $this->fullConfiguration['properties'][$propertyName]['type'];
    }

    /**
     * Return an array with the defined default values for each property, if any.
     *
     * The default value is configured for each property under the "default" key.
     *
     * @return array
     */
    public function getDefaultValuesForProperties()
    {
        $this->initialize();
        if (!isset($this->fullConfiguration['properties'])) {
            return array();
        }

        $defaultValues = array();
        foreach ($this->fullConfiguration['properties'] as $propertyName => $propertyConfiguration) {
            if (isset($propertyConfiguration['defaultValue'])) {
                $type = isset($propertyConfiguration['type']) ? $propertyConfiguration['type'] : '';
                switch ($type) {
                    case 'DateTime':
                        $defaultValues[$propertyName] = new \DateTime($propertyConfiguration['defaultValue']);
                    break;
                    default:
                        $defaultValues[$propertyName] = $propertyConfiguration['defaultValue'];
                }
            }
        }

        return $defaultValues;
    }

    /**
     * Checks if the given NodeType is acceptable as sub-node with the configured constraints,
     * not taking constraints of auto-created nodes into account. Thus, this method only returns
     * the correct result if called on NON-AUTO-CREATED nodes!
     *
     * Otherwise, allowsGrandchildNodeType() needs to be called on the *parent node type*.
     *
     * @param StaticNodeType $nodeType
     * @return boolean TRUE if the $nodeType is allowed as child node, FALSE otherwise.
     */
    public function allowsChildNodeType(StaticNodeType $nodeType)
    {
        $constraints = $this->getConfiguration('constraints.nodeTypes') ?: array();
        return $this->isNodeTypeAllowedByConstraints($nodeType, $constraints);
    }

    /**
     * Internal method to check whether the passed-in $nodeType is allowed by the $constraints array.
     *
     * $constraints is an associative array where the key is the Node Type Name. If the value is "TRUE",
     * the node type is explicitly allowed. If the value is "FALSE", the node type is explicitly denied.
     * If nothing is specified, the fallback "*" is used. If that one is also not specified, we DENY by
     * default.
     *
     * Super types of the given node types are also checked, so if a super type is constrained
     * it will also take affect on the inherited node types. The closest constrained super type match is used.
     *
     * @param StaticNodeType $nodeType
     * @param array $constraints
     * @return boolean
     */
    protected function isNodeTypeAllowedByConstraints(StaticNodeType $nodeType, array $constraints)
    {
        $directConstraintsResult = $this->isNodeTypeAllowedByDirectConstraints($nodeType, $constraints);
        if ($directConstraintsResult !== null) {
            return $directConstraintsResult;
        }

        $inheritanceConstraintsResult = $this->isNodeTypeAllowedByInheritanceConstraints($nodeType, $constraints);
        if ($inheritanceConstraintsResult !== null) {
            return $inheritanceConstraintsResult;
        }

        if (isset($constraints['*'])) {
            return (boolean)$constraints['*'];
        }

        return false;
    }

    /**
     * @param StaticNodeType $nodeType
     * @param array $constraints
     * @return boolean TRUE if the passed $nodeType is allowed by the $constraints
     */
    protected function isNodeTypeAllowedByDirectConstraints(StaticNodeType $nodeType, array $constraints)
    {
        if ($constraints === array()) {
            return true;
        }

        if (array_key_exists($nodeType->getName(), $constraints) && $constraints[$nodeType->getName()] === true) {
            return true;
        }

        if (array_key_exists($nodeType->getName(), $constraints) && $constraints[$nodeType->getName()] === false) {
            return false;
        }

        return null;
    }

    /**
     * This method loops over the constraints and finds node types that the given node type inherits from. For all
     * matched super types, their super types are traversed to find the closest super node with a constraint which
     * is used to evaluated if the node type is allowed. It finds the closest results for true and false, and uses
     * the distance to choose which one wins (lowest). If no result is found the node type is allowed.
     *
     * @param StaticNodeType $nodeType
     * @param array $constraints
     * @return boolean|NULL if no constraint matched
     */
    protected function isNodeTypeAllowedByInheritanceConstraints(StaticNodeType $nodeType, array $constraints)
    {
        $constraintDistanceForTrue = null;
        $constraintDistanceForFalse = null;
        foreach ($constraints as $superType => $constraint) {
            if ($nodeType->isOfType($superType)) {
                $distance = $this->traverseSuperTypes($nodeType, $superType, 0);

                if ($constraint === true && ($constraintDistanceForTrue === null || $constraintDistanceForTrue > $distance)) {
                    $constraintDistanceForTrue = $distance;
                }
                if ($constraint === false && ($constraintDistanceForFalse === null || $constraintDistanceForFalse > $distance)) {
                    $constraintDistanceForFalse = $distance;
                }
            }
        }

        if ($constraintDistanceForTrue !== null && $constraintDistanceForFalse !== null) {
            return $constraintDistanceForTrue < $constraintDistanceForFalse ? true : false;
        }

        if ($constraintDistanceForFalse !== null) {
            return false;
        }

        if ($constraintDistanceForTrue !== null) {
            return true;
        }

        return null;
    }

    /**
     * This method traverses the given node type to find the first super type that matches the constraint node type.
     * In case the hierarchy has more than one way of finding a path to the node type it's not taken into account,
     * since the first matched is returned. This is accepted on purpose for performance reasons and due to the fact
     * that such hierarchies should be avoided.
     *
     * @param StaticNodeType $currentNodeType
     * @param string $constraintNodeTypeName
     * @param integer $distance
     * @return integer or NULL if no NodeType matched
     */
    protected function traverseSuperTypes(StaticNodeType $currentNodeType, $constraintNodeTypeName, $distance)
    {
        if ($currentNodeType->getName() === $constraintNodeTypeName) {
            return $distance;
        }

        $distance++;
        foreach ($currentNodeType->getDeclaredSuperTypes() as $superType) {
            $result = $this->traverseSuperTypes($superType, $constraintNodeTypeName, $distance);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Alias for getName().
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
