<?php
    Package::Load( 'SPS.Site' );

    /**
     * RestoreCommentAppControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class RestoreCommentAppControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $id = Request::getInteger( 'id' );
            if ($id) {
                /** @var $author Author */
                $author = Session::getObject('Author');

                $o = new Comment();
                $o->statusId = 1;
                CommentFactory::UpdateByMask($o, array('statusId'), array('commentId' => $id, 'authorId' => $author->authorId, 'statusId' => 3));
            }
        }
    }
?>