<?php
/**
 * Created by JetBrains PhpStorm.
 * User: x100up
 * Date: 09.01.13
 * Time: 11:24
 * To change this template use File | Settings | File Templates.
 */
class FixUserFeedTools
{
    public function Execute() {
        echo 'FixUserFeedTools';

        $sql = 'SELECT "vkId", "targetFeedId" FROM "userFeed"
                GROUP BY "vkId", "targetFeedId" HAVING COUNT("vkId") > 1';

        $cmd = new SqlCommand($sql, ConnectionFactory::Get());
        $ds = $cmd->Execute();

        $conflictes = array();
        while ($ds->Next()) {
            $conflictes[] = array($ds->GetInteger('vkId'), $ds->GetInteger('targetFeedId'));
        }


        foreach ($conflictes as $conflict){
            list($vkId, $targetFeedId) = $conflict;
            $sql = 'DELETE FROM "userFeed" WHERE "role" < (SELECT MAX("role") AS "max"
                    FROM "userFeed" Where "vkId" = ' . $vkId . ' AND "targetFeedId" = ' . $targetFeedId . ') AND "vkId" = ' . $vkId . ' AND "targetFeedId" = ' . $targetFeedId;
            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $ds = $cmd->Execute();
        }
    }
}
