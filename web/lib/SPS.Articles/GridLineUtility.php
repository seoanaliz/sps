<?php
    /**
     * GridLineUtility
     * @package    SPS
     * @subpackage Articles
     * @author     Shuler
     */
    class GridLineUtility {
        const TYPE_CONTENT = 'content';
        const TYPE_ADS = 'ads';

        public static $types = array(
            self::TYPE_CONTENT => self::TYPE_CONTENT,
            self::TYPE_ADS => self::TYPE_ADS,
        );

        public static function GetGrid($targetFeedId, $date = null, $type = self::TYPE_CONTENT) {
            $now = DateTimeWrapper::Now();

            $result = array();
            if (empty($date)) {
                $date = DateTimeWrapper::Now();
            }

            $sql = <<<sql
                SELECT
                  gl."gridLineId",
                  gl."startDate",
                  gl."endDate",
                  COALESCE(CAST(gli."date" as TIME), gl."time") as "time",
                  gli."gridLineItemId"
                FROM "gridLines" gl
                LEFT JOIN "gridLineItems" gli ON (
                    gl."gridLineId" = gli."gridLineId"
                    AND CAST(gli."date" as DATE) = CAST(@date as DATE)
                )
                WHERE "startDate" <= CAST(@date as DATE)
                AND "endDate" >= CAST(@date as DATE)
                AND "targetFeedId" = @targetFeedId
                AND "type" = @type
                ORDER BY "time" DESC
sql;

            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $cmd->SetInt('@targetFeedId', $targetFeedId);
            $cmd->SetDate('@date', $date);
            $cmd->SetString('@type', $type);

            $ds = $cmd->Execute();


            while ($ds->Next()) {
                $item = array(
                    'gridLineId' => $ds->GetInteger('gridLineId'),
                    'startDate' => $ds->GetDateTime('startDate'),
                    'endDate' => $ds->GetDateTime('endDate'),
                    'dateTime' => $ds->GetDateTime('time'),
                    'gridLineItemId' => $ds->GetDateTime('gridLineItemId'),
                );

                $item['blocked'] = ($item['dateTime'] <= $now);

                $result[] = $item;
            }

            return $result;
        }
    }
?>