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
            if (empty($articleId)) return;

            $article = ArticleFactory::GetById($articleId);

            if (empty($article)) return;

            $SourceAccessUtility = new SourceAccessUtility($this->vkId);

            //check access
            if (!$SourceAccessUtility->hasAccessToSourceFeed($article->sourceFeedId)) {
                return;
            }

            $articleRecord = ArticleRecordFactory::GetOne(array('articleId' => $articleId));

            if (empty($articleRecord)) return;

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
                'link' => $articleRecord->link
            );

            echo ObjectHelper::ToJSON($result);
        }
    }
?>