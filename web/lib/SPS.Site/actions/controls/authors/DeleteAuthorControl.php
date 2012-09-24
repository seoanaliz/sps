<?php
    Package::Load( 'SPS.Site' );

    /**
     * DeleteAuthorControl Action
     * @package    SPS
     * @subpackage Site
     * @author     eugeneshulepin
     */
    class DeleteAuthorControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $o = new Author();
            $o->vkId = Request::getInteger('vkId');

            $targetFeedId = Request::getInteger( 'targetFeedId' );
            if (!AccessUtility::HasAccessToTargetFeedId($targetFeedId)) {
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
        }
    }

?>