<?php
    /**
     * CommentUtility
     * @package    SPS
     * @subpackage App
     * @author     Shuler
     */
    class CommentUtility {

        const LAST_COUNT = 3;

        public static function GetLastComments($articleIds, $limit = true) {
            $result = array();

            $sql = <<<eof
                SELECT * FROM (
                    SELECT *, row_number() OVER w as "num", count(*) OVER()
                    FROM "getComments"
                    WHERE "statusId" = 1 AND "articleId" IN @articleIds
                    WINDOW w AS (
                        PARTITION BY "articleId"
                        ORDER BY "createdAt" DESC, "articleId" DESC
                    )
                    ORDER BY "createdAt" ASC
                ) foo
eof;

            if ($limit) {
                $sql .= ' WHERE "num" < ' . (self::LAST_COUNT + 1);
            }

            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $cmd->SetList('@articleIds', $articleIds, TYPE_INTEGER);

            $ds = $cmd->Execute();

            $structure = BaseFactory::GetObjectTree( $ds->Columns );

            while ($ds->Next()) {
                /** @var $object Comment */
                $object = BaseFactory::getObject($ds, CommentFactory::$mapping, $structure);
                $result[$object->articleId]['comments'][] = $object;
                $result[$object->articleId]['count'] = $ds->GetInteger('count');
            }

            return $result;
        }
    }
?>