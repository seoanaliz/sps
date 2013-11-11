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
        const TYPE_ALL = 'all';

        public static $Types = array(
            self::TYPE_CONTENT => self::TYPE_CONTENT,
            self::TYPE_ADS => self::TYPE_ADS,
        );

        public static $TitleTypes = array(
            self::TYPE_CONTENT => 'Контент',
            self::TYPE_ADS => 'Реклама',
        );

        public static function GetGrid($targetFeedId, $date = null, $type = self::TYPE_CONTENT) {
            $result = array();
            $now = DateTimeWrapper::Now();

            if (empty($date)) {
                $date = DateTimeWrapper::Now();
            } else {
                $date = new DateTimeWrapper($date);
            }

            if ($type == self::TYPE_ALL) {
                return $result;
            }

            $sql = <<<sql
                SELECT
                  gl."gridLineId",
                  gl."startDate",
                  gl."endDate",
                  COALESCE(CAST(gli."date" as TIME), gl."time") as "time",
                  gli."gridLineItemId",
                  gl.repeat
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
                    'gridLineItemId' => $ds->GetInteger('gridLineItemId'),
                    'startDate' => $ds->GetDateTime('startDate'),
                    'endDate' => $ds->GetDateTime('endDate'),
                    'repeat' => $ds->GetBoolean('repeat'),
                );

                $item['dateTime'] = new DateTimeWrapper($date->DefaultDateFormat() . ' ' . $ds->GetDateTime('time')->DefaultTimeFormat());
                $item['blocked'] = ($item['dateTime'] <= $now);

                $result[] = $item;
            }

            return $result;
        }

        //обновляем итемы старой линии после удаляемой ячейки, привязываем их к новой линии
        /**
         * @param $fromDate
         * @param int|int[] $oldGridLineId
         * @param $newGridLineId  int
         */
        public static function RebindGridLineItems( $fromDate, $oldGridLineId, $newGridLineId ) {
            if (!is_array($oldGridLineId)) {
                $oldGridLineId = array( $oldGridLineId );
            }
            $item = new GridLineItem();
            $item->gridLineId = $newGridLineId;
            GridLineItemFactory::UpdateByMask(
                $item,
                array('gridLineId'),
                array(
                    '_gridLineId' => $oldGridLineId,
                    'dateGE'      => $fromDate,
                )
            );
        }

        //создаем новую ячейку
        public static function make_grids( $target_feed_id, $sent_at )
        {
            $date = $sent_at->format('d.m.Y');
            $grid_line = new GridLine();
            $grid_line->startDate = $date;
            $grid_line->endDate = $date;
            $grid_line->targetFeedId = $target_feed_id;
            $grid_line->time = $sent_at->format('H:i:s');
            $grid_line->repeat = false;
            $grid_line->type = GridLineUtility::TYPE_CONTENT;
            $result = GridLineFactory::Add( $grid_line, array(BaseFactory::WithReturningKeys => true));

            if (!$result )
                return false;

            $grid_line_item = new GridLineItem();
            $grid_line_item->gridLineId = $grid_line->gridLineId;
            $grid_line_item->date = $sent_at;

            return GridLineItemFactory::Add( $grid_line_item );
        }

    }
?>