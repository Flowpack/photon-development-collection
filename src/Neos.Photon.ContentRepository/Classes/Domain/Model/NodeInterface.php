<?php
namespace Neos\Photon\ContentRepository\Domain\Model;

interface NodeInterface
{

    public function getNodeName(): string;

    public function getNodeType(): StaticNodeType;

    public function getParent(): ?NodeInterface;

    public function getProperties(): array;

    public function getChildNodes(): array;

    public function getChildNode(string $nodeName): ?NodeInterface;
}
