<?php
Package::Load('SPS.Site/base');

/**
 * Возвращает список авторов для ленты
 * @package    SPS
 * @subpackage Site
 * @author     shuler
 */
class GetAuthorsListControl extends BaseControl
{

    /**
     * Entry Point
     */
    public function Execute() {
        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        $targetFeedId = Request::getInteger('targetFeedId');

        if (!$TargetFeedAccessUtility->canShowAuthorList($targetFeedId)) {
            return;
        }

        $authors = array();

        if (!empty($targetFeedId)) {
            $UserFeeds = UserFeedFactory::Get(array('targetFeedId' => $targetFeedId, 'role' => UserFeed::ROLE_AUTHOR));
            if ($UserFeeds) {
                $vkIds = array();
                foreach ($UserFeeds as $UserFeed){
                    $vkIds[] = $UserFeed->vkId;
                }

                $authors = AuthorFactory::Get(
                    array(
                        'vkIdIn' => $vkIds
                    )
                    , array(
                        BaseFactory::WithoutPages => true,
                        BaseFactory::OrderBy => ' "firstName", "lastName" ',
                    )
                );
            }
        }

        Response::setArray('authors', $authors);
    }
}

?>