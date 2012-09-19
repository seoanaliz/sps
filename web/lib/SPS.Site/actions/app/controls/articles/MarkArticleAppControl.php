<?php
    Package::Load( 'SPS.Site' );

    /**
     * MarkArticleAppControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class MarkArticleAppControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $id = Request::getInteger( 'id' );
            if ($id) {
                $author = Session::getObject('Author');

                $article = ArticleFactory::GetById(
                    $id
                    , array('authorId' => $author->authorId)
                    , array(BaseFactory::WithoutDisabled => false)
                );

                if (!empty($article)) {
                    if (!empty($article->queuedAt)) {
                        AuthorEventUtility::EventQueueRemove($id);
                    }
                    if (!empty($article->sentAt)) {
                        AuthorEventUtility::EventSentRemove($id);
                    }
                }
            }
        }
    }

?>