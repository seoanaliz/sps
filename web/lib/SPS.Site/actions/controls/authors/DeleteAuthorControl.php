<?php
Package::Load('SPS.Site/base');

/**
 * Удаление авторов
 * TODO тоже переделать
 * @package    SPS
 * @subpackage Site
 * @author     eugeneshulepin
 */
class DeleteAuthorControl extends BaseControl {

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
            return;
        }

        if (!empty($o->vkId)) {
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
    }
}

?>