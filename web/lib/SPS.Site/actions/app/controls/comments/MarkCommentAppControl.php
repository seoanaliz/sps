<?php
Package::Load('SPS.Site');

/**
 * MarkCommentAppControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class MarkCommentAppControl extends AppBaseControl
{

    /**
     * Entry Point
     */
    public function Execute()
    {
        $articleId = Request::getInteger('articleId');
        $commentId = Request::getInteger('commentId');

        if (empty($articleId) || empty($commentId)) {
            return;
        }

        $author = $this->getAuthor();

        $article = ArticleFactory::GetById(
            $articleId
            , array('authorId' => $author->authorId)
            , array(BaseFactory::WithoutDisabled => false)
        );

        if (!empty($article)) {
            AuthorEventUtility::EventCommentRemove($article, $commentId);
        }
    }
}

?>