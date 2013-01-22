<?php
Package::Load('SPS.Site');

/**
 * DeleteArticleAppControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class DeleteArticleAppControl extends AppBaseControl
{

    /**
     * Entry Point
     */
    public function Execute()
    {
        $id = Request::getInteger('id');

        if (empty($id)) {
            return;
        }

        $author = $this->getAuthor();

        $o = new Article();
        $o->statusId = 3;
        ArticleFactory::UpdateByMask($o, array('statusId'), array('articleId' => $id, 'authorId' => $author->authorId));
    }
}

?>