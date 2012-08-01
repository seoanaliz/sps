<?php
    Package::Load( 'SPS.Site' );

    /**
     * DeleteArticleAppControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class DeleteArticleAppControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $id = Request::getInteger( 'id' );

            if (empty($id)) {
                return;
            }

            $author = Session::getObject('Author');

            $o = new Article();
            $o->statusId = 3;
            ArticleFactory::UpdateByMask($o, array('statusId'), array('articleId' => $id, 'authorId' => $author->authorId));
        }
    }
?>