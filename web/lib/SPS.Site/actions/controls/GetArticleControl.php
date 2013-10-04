<?php
    /**
     * GetArticleControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetArticleControl extends BaseControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $articleId = Request::getInteger( 'articleId' );
            $articleQueueId = Request::getInteger( 'queueId' );

            if (!$articleQueueId) {
                if (!$articleId) {
                    return; // --------------------- RETURN
                }

                $article = ArticleFactory::GetById($articleId);
                if (!$article) {
                    return; // --------------------- RETURN
                }

                $feedId = $article->sourceFeedId;
            } else {
                $articleQueue = ArticleQueueFactory::GetById($articleQueueId);
                if (!$articleQueue) {
                    return; // --------------------- RETURN
                }

                $feedId = $articleQueue->targetFeedId;
                $articleId = $articleQueue->articleId;
            }

            // проверим доступ


            $articleRecord = ArticleRecordFactory::GetOne(array('articleId' => $articleId));
            if (empty($articleRecord)) {
                return; // ------------------------- RETURN
            }

            $photos = array();
            if (!empty($articleRecord->photos)) {
                foreach($articleRecord->photos as $photoItem) {
                    $photo = $photoItem;
                    $photo['path'] = MediaUtility::GetArticlePhoto($photoItem, 'small');
                    $photos[] = $photo;
                }
            }

            $result = array(
                'id' => $articleId,
                'text' => nl2br($articleRecord->content),
                'photos' => ObjectHelper::ToJSON($photos),
                'link' => $articleRecord->link,
                'repostExternalId' => $articleRecord->repostExternalId,
            );

            echo ObjectHelper::ToJSON($result);
        }
    }
?>