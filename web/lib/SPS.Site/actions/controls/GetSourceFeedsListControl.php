<?php
/**
 * GetSourceFeedsListControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class GetSourceFeedsListControl extends BaseControl
{

    /**
     * Entry Point
     */
    public function Execute()
    {
        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);

        $targetFeedId = Request::getInteger('targetFeedId');

        $type = Request::getString('type');
        if (empty($type) || empty(SourceFeedUtility::$Types[$type])) {
            $type = $TargetFeedAccessUtility->getDefaultType($targetFeedId);
        }

        if (!$TargetFeedAccessUtility->hasAccessToSourceType($targetFeedId, $type)) {
            // запросили недоступный тип, но мы тогда вернем дефолтный
            $type = $TargetFeedAccessUtility->getDefaultType($targetFeedId);
        }

        $result = array();
        if (!empty($targetFeedId)) {
            if ($type == SourceFeedUtility::Authors) {
                $role = $TargetFeedAccessUtility->getRoleForTargetFeed($targetFeedId);
                $authors = array();
                /**
                 * Если у юзера роль - автор - то мы ему возвращаем в источнике только себя,
                 * Если нет - то всех авторов в этой ленте
                 */
                if ($role == UserFeed::ROLE_AUTHOR) {
                    $authors = AuthorFactory::Get(
                        array('vkId' => $this->vkId),
                        array(BaseFactory::WithoutPages => true)
                    );
                } else {
                    $UserFeeds = UserFeedFactory::Get(array('targetFeedId' => $targetFeedId));
                    if ($UserFeeds){
                        $vkIds = array();
                        foreach ($UserFeeds as $UserFeed) {
                            $vkIds[] = $UserFeed->vkId;
                        }

                        $authors = AuthorFactory::Get(
                            array('vkIdIn' => $vkIds),
                            array(BaseFactory::WithoutPages => true)
                        );
                    }
                }

                foreach ($authors as $author) {
                    $result[] = array(
                        'id' => $author->authorId,
                        'title' => $author->FullName()
                    );
                }
            } else {
                $SourceAccessUtility = new SourceAccessUtility($this->vkId);

                $sourceIds = $SourceAccessUtility->getSourceIdsForTargetFeed($targetFeedId);
                $sourceFeeds = array();
                if ($sourceIds) {
                    $sourceFeeds = SourceFeedFactory::Get(
                        array(
                            '_sourceFeedId' => $sourceIds,
                            'type' => $type)
                        , array(BaseFactory::WithoutPages => true)
                    );
                }

                foreach ($sourceFeeds as $sourceFeed) {
                    $result[] = array(
                        'id' => $sourceFeed->sourceFeedId,
                        'title' => $sourceFeed->title
                    );
                }
            }
        } else {
            echo('Unknown source feed identifier');
        }


        echo ObjectHelper::ToJSON(array(
            'type' => $type,
            'sourceFeeds' => $result,
            'accessibleSourceTypes' => $TargetFeedAccessUtility->getAccessibleSourceTypes($targetFeedId),
            'canShowAuthorsList' => $TargetFeedAccessUtility->canShowAuthorList($targetFeedId),
            'accessibleGridTypes' => array_keys($TargetFeedAccessUtility->getAccessibleGridTypes($targetFeedId)),
            'canAddPlanCell' => $TargetFeedAccessUtility->canAddPlanCell($targetFeedId)
        ));
    }
}

?>