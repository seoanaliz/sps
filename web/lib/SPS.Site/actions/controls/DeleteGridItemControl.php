<?php
    /**
     * ArticleQueueToggleRepeat Action
     * @package    SPS
     * @subpackage Site
     * @author     Eugene Kulikov
     */
    class DeleteGridItemControl extends BaseControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $gridLineId     =   Request::getInteger('gridLineId');
            $timestamp      =   Request::getString('timestamp');
            $targetFeedId   =   Request::getString('targetFeedId');
            $result = array(
                'success' => false
            );

            if (!$gridLineId || !$timestamp || !is_numeric($timestamp) || !$targetFeedId ) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
            if (!$TargetFeedAccessUtility->canSaveGridLine($targetFeedId)) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            $GridLine = GridLineFactory::GetById($gridLineId);
            if (!$GridLine ) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            $dayPressed = date('Y-m-d', $timestamp); // возьмём только дату (отбросим время)
//            $dayPressed = '2013-09-23'; // возьмём только дату (отбросим время)
            $dt = new DateTimeWrapper($dayPressed);


            if ( $GridLine->endDate < $dt || $GridLine->startDate > $dt ) {
                echo ObjectHelper::ToJSON($result);
                return false;
            } elseif ( $GridLine->endDate == $dt ) {//если удаляемая ячейка - первая в линии
                $GridLine->endDate->modify('-1 day');
            } elseif ( $GridLine->startDate == $dt ) {//если удаляемая ячейка - последняя в линии
                $GridLine->startDate->modify('+1 day');
            } else {//если удаляемая ячейка в середине линии
                $newGridLine  = clone( $GridLine);
                $newGridLine->startDate = (new DateTimeWrapper($dayPressed))->modify('+1 day');//новая линия начинается со следующего после удаляемого дня
                $GridLine->endDate = (new DateTimeWrapper($dayPressed))->modify('-1 day');//старая линия заканчивается предыдущим от удаляемого днем
                $GridLine->repeat  = false;
                $newGridLine->gridLineId = null;

                GridLineFactory::Add( $newGridLine,array( BaseFactory::WithReturningKeys => true));
                if (!$newGridLine->gridLineId ) {
                    echo ObjectHelper::ToJSON($result);
                    return false;
                }

                //меняем gridLineId на новый у итемов из промежутка (дата удаленной ячейки;бесконечность)
                GridLineUtility::RebindGridLineItems(
                    $newGridLine->startDate,
                    $GridLine->gridLineId,
                    $newGridLine->gridLineId
                );
;            }

            //удаляем все итемы начальной линии на этот день(откуда они берутся - вы не поверите)
            $this->deleteGridLineItem($GridLine->gridLineId, $dayPressed);
            $result['success'] = GridLineFactory::Update($GridLine);
            echo ObjectHelper::ToJSON($result);
        }

        public function deleteGridLineItem( $gridLineId, $date ) {
            $sql = 'DELETE FROM "gridLineItems"
                    WHERE "gridLineId" = @gridLine
                    AND date::date = @date';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get());
            $cmd->SetDate('@date', $date );
            $cmd->SetInt('@gridLine', $gridLineId);
            return $cmd->Execute();
        }
    }
?>