<?php

declare(strict_types=1);

namespace Rector\Jack\FileSystem;

use Boundwize\JsonRecast\JsonRecast;
use Boundwize\JsonRecast\Node\NodeJson;
use Boundwize\JsonRecast\Node\StringNode;
use Boundwize\JsonRecast\NodePath\NodeJsonPath;
use Boundwize\JsonRecast\NodeVisitor\NodeJsonVisitorAbstract;

final class ComposerJsonPackageVersionUpdater
{
    public static function update(string $composerJsonContents, string $packageName, string $newVersion): string
    {
        $jsonDocument = JsonRecast::parse($composerJsonContents);

        $jsonRecastResult = JsonRecast::traverse(
            $jsonDocument,
            new class($packageName, $newVersion) extends NodeJsonVisitorAbstract {
                public function __construct(
                    private readonly string $packageName,
                    private readonly string $newVersion,
                ) {
                }

                public function enterNode(NodeJson $nodeJson, NodeJsonPath $nodeJsonPath): ?NodeJson
                {
                    if (! $nodeJson instanceof StringNode) {
                        return null;
                    }

                    foreach (['require', 'require-dev'] as $composerPackageSection) {
                        if (! $nodeJsonPath->matches([$composerPackageSection, $this->packageName])) {
                            continue;
                        }

                        return new StringNode($this->newVersion);
                    }

                    return null;
                }
            }
        );

        return JsonRecast::print($jsonRecastResult);
    }
}
