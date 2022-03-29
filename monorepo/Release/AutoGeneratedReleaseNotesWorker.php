<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\Release;

use EonX\EasyMonorepo\Git\GitManager;
use GuzzleHttp\ClientInterface;
use MonorepoBuilder20220316\Symplify\SmartFileSystem\SmartFileSystem;
use PharIo\Version\Version;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;

final class AutoGeneratedReleaseNotesWorker implements ReleaseWorkerInterface
{
    public function __construct(
        private ClientInterface $httpClient,
        private GitManager $gitManager,
        private SmartFileSystem $smartFileSystem
    ) {
        // The body is not required
    }

    public function getDescription(Version $version): string
    {
        return \sprintf('Use github to generate release notes for %s', $version->getVersionString());
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function work(Version $version): void
    {
        $filename = \sprintf(__DIR__ . '/../../secret/release_%s.md', $version->getVersionString());
        $content = $this->getAutoGeneratedReleaseNotes($version);

        // Remove title generated by GitHub
        $content = \str_replace("## What's Changed\n", '', $content);

        $this->smartFileSystem->dumpFile($filename, $content);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getAutoGeneratedReleaseNotes(Version $version): string
    {
        $currentBranch = $this->gitManager->getCurrentBranch();
        $githubToken = \getenv('GITHUB_TOKEN');

        \var_dump($githubToken);

        $url = 'https://api.github.com/repos/eonx-com/easy-monorepo/releases/generate-notes';

        $options = [
            'headers' => [
                'accept' => 'application/vnd.github.v3+json',
                'authorization' => \sprintf('Token %s', $githubToken),
            ],
            'body' => \json_encode([
                'tag_name' => $version->getVersionString(),
                'target_commitish' => $currentBranch,
            ]),
        ];

        $response = $this->httpClient->request('POST', $url, $options);
        $responseArray = \json_decode($response->getBody()->getContents(), true);

        return $responseArray['body'];
    }
}
