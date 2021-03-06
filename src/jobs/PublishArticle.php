<?php

namespace craft\applenews\jobs;

use Craft;
use craft\applenews\ArticleManager;
use craft\applenews\Plugin;
use craft\applenews\records\Article;
use craft\elements\Entry;
use craft\queue\BaseJob;

class PublishArticle extends BaseJob
{
    /**
     * @var int
     */
    public $entryId;

    /**
     * @var @var int
     */
    public $siteId;

    /**
     * @var string
     */
    public $channelId;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        // Make sure the article is still queued
        $queued = Article::find()
            ->where([
                'entryId' => $this->entryId,
                'channelId' => $this->channelId,
                'state' => [ArticleManager::STATE_QUEUED, ArticleManager::STATE_QUEUED_UPDATE],
            ])
            ->exists();

        if (!$queued) {
            return;
        }

        $entry = Entry::find()
            ->id($this->entryId)
            ->siteId($this->siteId)
            ->anyStatus()
            ->one();

        if (!$entry) {
            return;
        }

        $this->setProgress($queue, 0, $entry->title);
        Plugin::getInstance()->articleManager->publish($entry, $this->channelId);
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription()
    {
        return Craft::t('apple-news', 'Publishing to Apple News');
    }
}
