<?php
/**
 * DeleteAuthorControl Action
 * @package    SPS
 * @subpackage Site
 * @author     eugeneshulepin
 */
class DeleteAuthorControl extends BaseControl
{

    /**
     * Entry Point
     */
    public function Execute()
    {
        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);

        $o = new Author();
        $o->vkId = Request::getInteger('vkId');

        $targetFeedId = Request::getInteger('targetFeedId');
        if (!$TargetFeedAccessUtility->canDeleteAuthor($targetFeedId)) {
            echo ObjectHelper::ToJSON(array('success' => false, 'accessDenied' => true));
            return;
        }

        if (!empty($o->vkId)) {

            $UserFeed = UserFeedFactory::GetOne(array('vkId' => $o->vkId, 'targetFeedId' => $targetFeedId, 'role' => UserFeed::ROLE_AUTHOR));
            if ($UserFeed) {
                UserFeedFactory::DeleteByMask(array('vkId' => $o->vkId, 'targetFeedId' => $targetFeedId, 'role' => UserFeed::ROLE_AUTHOR));
            }

            // TODO выпилить
            $sql = <<<sql
                  UPDATE "authors" SET "targetFeedIds" = array_remove_sql(CAST("targetFeedIds" as int8[]), CAST('{@targetFeedId}' as int8[]))
                  WHERE "vkId" = @vkId
sql;
            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $cmd->SetInt('@targetFeedId', $targetFeedId);
            $cmd->SetInt('@vkId', $o->vkId);
            $cmd->ExecuteNonQuery();
        }

        $manageEvent = new AuthorManage();
        $manageEvent->createdAt = DateTimeWrapper::Now();
        $manageEvent->authorVkId = $o->vkId;
        $manageEvent->editorVkId = AuthUtility::GetCurrentUser('Editor')->vkId;
        $manageEvent->action = 'delete';
        $manageEvent->targetFeedId = $targetFeedId;
        AuthorManageFactory::Add($manageEvent);

        echo ObjectHelper::ToJSON(array('success' => true));
    }
}

?>