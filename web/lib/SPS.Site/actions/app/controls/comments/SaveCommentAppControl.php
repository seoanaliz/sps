<?php
    Package::Load( 'SPS.Site' );

    /**
     * SaveCommentAppControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SaveCommentAppControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $result = array(
                'success' => false
            );

            /** @var $author Author */
            $author = Session::getObject('Author');

            $article = ArticleFactory::GetById(Request::getInteger('id'));

            if (empty($article) || $article->sourceFeedId != -1) {
                $result['message'] = 'accessError';
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            if (!in_array($article->targetFeedId, Session::getArray('targetFeedIds'))) {
                $result['message'] = 'accessError';
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            $comment = new Comment();
            $comment->text = Request::getString('text');
            $comment->articleId = $article->articleId;
            $comment->createdAt = DateTimeWrapper::Now();
            $comment->authorId = $author->authorId;
            $comment->statusId = 1;

            $errors = CommentFactory::Validate($comment);
            if (!empty($errors)) {
                $result['message'] = 'saveError';
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            CommentFactory::Add($comment, array(BaseFactory::WithReturningKeys => true));

            $comment = CommentFactory::GetById($comment->commentId);
            Response::setParameter( 'comment', $comment );
        }
    }
?>