<?php
/**
 * User: x100up
 * Date: 28.10.12 13:48
 * In Code We Trust
 */

abstract class AbstractPostLoadDaemon {
    /**
     * @var Daemon
     */
    protected $daemon;

    /**
     * Метод синхронизации постов
     * @param SourceFeed    $source лента откуда получили посты
     * @param array         $posts массив постов
     * @param int           $targetFeedId id ленты отправки
     * @param Author[]      $authors массив авторов( key = VkId)
     * @return bool нужно ли обновить processed у $source
     */
    protected function saveFeedPosts($source, $posts, $targetFeedId = null, $authors = array()) {
        /**
         * Ищем в базе уже возможно сохраненные
         * Попутно собираем id тех, которые надо пропустить
         */
        $externalIds = array();
        $skipIds = array();
        $targetDate = new DateTimeWrapper('-1 day'); //проспускаем посты, которым еще нет 1 суток(к предложенным не относится)
        if( !$targetFeedId) {
            foreach ($posts as $post) {
                $externalId = TextHelper::ToUTF8($post['id']);
                $externalIds[] = $externalId;

                //если пост новее чем $targetDate - пропускаем
                $postDate = new DateTimeWrapper(date('r', $post['time']));
                if ($postDate >= $targetDate) {
                    $skipIds[] = $externalId;
                }
            }
        }

        //ищем тупо во всей базе такие ArticleFactory::$mapping
        $__mapping = ArticleFactory::$mapping;
        ArticleFactory::$mapping['view'] = 'articles';
        $originalObjects = ArticleFactory::Get(

            array('_externalId' => !empty($externalIds) ? $externalIds : array(-1 => -1))
            , array(
                BaseFactory::WithColumns => '"articleId", "externalId"'
                , BaseFactory::WithoutPages => true
                , BaseFactory::WithoutDisabled => false
            )
        );
        ArticleFactory::$mapping = $__mapping;

        if (!empty($originalObjects)) {
            $originalObjects = BaseFactoryPrepare::Collapse($originalObjects, 'externalId', false);
        }
        /**
         * Обходим посты и созраняем их в бд, попутно сливая фотки
         */
        foreach ($posts as $post) {
            $externalId = TextHelper::ToUTF8($post['id']);

            if( !isset ($post['likes_tr'] ))
                continue;
            $article = new Article();
            $article->sourceFeedId = $source->sourceFeedId;
            $article->externalId = $externalId;
            $article->createdAt = new DateTimeWrapper(date('r', $post['time']));
            $article->importedAt = DateTimeWrapper::Now();
            $article->isCleaned = false;
            $article->statusId = 1;
            $article->isSuggested = false;
            // демон загружает уже одобренные посты. ха
            $article->articleStatus = ( $source->type == SourceFeedUtility::Albums ) ?
                Article::STATUS_REVIEW : Article::STATUS_APPROVED;
            if( $targetFeedId && $post['author']) {
                $article->editor = $post['author'];
                $article->targetFeedId = $targetFeedId;
                $article->isSuggested  = true;
                $article->authorId     = $authors[$post['author']]->authorId;
            }

            $articleRecord = new ArticleRecord();
            $articleRecord->content = $post['text'];
            $articleRecord->likes = Convert::ToInteger($post['likes_tr']);
            $articleRecord->link = Convert::ToString($post['link']);
            $articleRecord->photos = array();

            $articleRecord->retweet = Convert::ToArray($post['retweet']);
            $articleRecord->text_links = Convert::ToArray($post['text_links']);
            $articleRecord->video = Convert::ToArray($post['video']);
            $articleRecord->music = Convert::ToArray($post['music']);
            $articleRecord->poll = Convert::ToString($post['poll']);
            $articleRecord->map = Convert::ToString($post['map']);
            $articleRecord->doc = Convert::ToString($post['doc']);
            $articleRecord->createdVia = Convert::ToString($post['createdVia']);

            //rate
            $articleRecord->rate = 0;

            if (strpos($post['likes'], '%') !== false) {
                $articleRecord->rate = Convert::ToInt(str_replace('%', '', $post['likes']));
            }

            $article->rate = $articleRecord->rate;

            if (!empty($originalObjects[$externalId])) {
                //обновляем уже сохраненный пост и только определенные поля

                //проверка на уже существующие дубликаты
                if (!is_object($originalObjects[$externalId])) continue;

                //фильтруем поля
                $fields = array('likes', 'link', 'retweet', 'text_links', 'video', 'music', 'poll', 'map', 'doc', 'rate');

                //обновляем запись
                ArticleRecordFactory::UpdateByMask($articleRecord, $fields, array('articleId' => $originalObjects[$externalId]->articleId));
                ArticleFactory::UpdateByMask($article, array('rate'), array('articleId' => $originalObjects[$externalId]->articleId));
            } else {
                //сохраняем фотки на медиа сервер
                if (!empty($post['photo'])) {
                    try {
                        $articleRecord->photos = $this->savePostPhotos($post['photo']);
                    } catch (Exception $Ex) {
                        //AuditUtility::CreateEvent('importErrors', 'feed', $source->externalId, $Ex->getMessage());

                        /**
                         * Если хоть одна фотка не загрузилась, то мы добавляем $externalId в $skipIds
                         * Из-за этого не обновится processed у источника и мы проапдейтим пост в следующий раз
                         */
                        $skipIds[] = $externalId;
                    }
                }

                //добавляем новый пост
                if (!in_array($externalId, $skipIds)) {
                    $this->addArticle($article, $articleRecord);
                }
            }
        }

        /**
         * если массив $skipIds непуст, то значит по каким-то условиям не сохраняем все посты
         */

        if (empty($skipIds) && $source->sourceFeedId != -1) {
            //обновляем pagesCountProcessed в базе, снимаем лок параллельному потоку
            $source->processed = Convert::ToInt($source->processed) + 1;
            SourceFeedFactory::UpdateByMask($source, array('processed'), array('sourceFeedId' => $source->sourceFeedId));
        }

        $this->daemon->Unlock();
    }

    private function addArticle(Article $article, $articleRecord) {
        //сохраняем в транзакции
        $conn = ConnectionFactory::Get();
        $conn->begin();

        $result = ArticleFactory::Add($article, array(BaseFactory::WithReturningKeys => true));

        if ($result) {
            $articleRecord->articleId = $article->articleId;
            $result = ArticleRecordFactory::Add($articleRecord);
        }

        if ($result) {
            $conn->commit();
        } else {
            $conn->rollback();
        }

        return $result;
    }

    /**
     * Метод сохраняет фотки извне на медиасервер и готовит инфу о них для записи в базу
     * @param $data
     * @return array
     */
    private function savePostPhotos($data) {
        $result = array();

        foreach ($data as $photo) {
            $result[] = array(
                'filename'  => '',
                'title'     => !empty($photo['desc']) ? TextHelper::ToUTF8($photo['desc']) : '',
                'url'       => $photo['url'],
                'id'        => $photo['id'],
            );
        }

        return $result;
    }
}