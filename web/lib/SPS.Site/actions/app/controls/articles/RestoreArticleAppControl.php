<?php
    Package::Load( 'SPS.Site/base' );

    /**
     * RestoreArticleAppControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class RestoreArticleAppControl extends BaseControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $id = Request::getInteger( 'id' );
            if ($id) {
                $author = $this->getAuthor();

                $existsCount = ArticleQueueFactory::Count(
                    array('articleId' => $id)
                );

                $o = new Article();
                $o->statusId = !empty($existsCount) ? 2 : 1;
                ArticleFactory::UpdateByMask($o, array('statusId'), array('articleId' => $id, 'authorId' => $author->authorId, 'statusId' => 3));
            }
        }
    }
?>