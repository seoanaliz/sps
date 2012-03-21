<?php
    Package::Load( 'SPS.Site' );

    /**
     * DeleteAricleControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class DeleteAricleControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $id = Request::getInteger( 'id' );
            if ($id) {
                $o = new Article();
                $o->statusId = 3;
                ArticleFactory::UpdateByMask($o, array('statusId'), array('articleId' => $id));
            }
        }
    }
?>