<?php
    /**
     * ArticleQueueToggleRepeat Action
     * @package    SPS
     * @subpackage Site
     * @author     Eugene Kulikov
     */
    class ArticleQueueToggleRepeat extends BaseControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $gridLineId = Request::getInteger('gridLineId');
            $timestamp = Request::getString('timestamp');
            $type = Request::getString('type');
            $targetFeedId = Request::getInteger('targetFeedId');

            $result = array(
                'success' => false
            );

            if (!$gridLineId || !$timestamp || !is_numeric($timestamp) || !$type || !$targetFeedId || !isset(GridLineUtility::$Types[$type])) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            $targetFeed = TargetFeedFactory::GetById($targetFeedId);
            if (empty($targetFeed)) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
            if (!$TargetFeedAccessUtility->canSaveGridLine($targetFeedId)) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            $GridLine = GridLineFactory::GetById($gridLineId);
            if (!$GridLine) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            //проверим, есть ли в будущем посты на это время. есть - мимо
            $dayPressed = date('Y-m-d', $timestamp); // возьмём только дату (отбросим время)
            $dt = new DateTimeWrapper($dayPressed);
            $dt->modify('+1 day');
            $dt->modify('-30 seconds');
            $queuedArticles = $this->checkForExistingQueues($targetFeedId, $dt, $GridLine->time );
            if ($queuedArticles) {
                $message = 'В этой ячейке в будущем есть запланированные посты: ' . join(', ', $queuedArticles) . '.';
                $result = array(
                    'success' => false,
                    'message' => $message,
                );
                echo ObjectHelper::ToJSON($result);
                return false; // -------------- RETURN
            }

            if ($GridLine->repeat) {
                // ограничим repeat датой нажатой ячейки

                $yearToQueue = $dt->format('Y');
                if ($yearToQueue > 3000) { // не больше 3000-го года
                    echo ObjectHelper::ToJSON($result);
                    return false;
                }

                $GridLine->repeat = false;
                $GridLine->endDate = new DateTimeWrapper($dayPressed);
                $result['endDate'] = $GridLine->endDate->format('U');
            } else {
                //проверим, вдруг цикл на это время уже есть
                $check = GridLineFactory::Get ( array(
                    'time'          =>  $GridLine->time,
                    'type'          =>  $GridLine->type,
                    'targetFeedId'  =>  $targetFeedId,
                    'startDateL'    =>  $GridLine->startDate,
                    'repeat'        =>  true
                ));
                if ( !empty( $check )) {
                    $result['message'] = 'Cycle for this time already exists';
                    echo ObjectHelper::ToJSON($result);
                    return false;
                }

                //склеиваем все циклы в один
                $killGridLines = GridLineFactory::Get ( array( //выбираем похожие циклы в будущем...
                    'time'          =>  $GridLine->time,
                    'type'          =>  $GridLine->type,
                    'targetFeedId'  =>  $targetFeedId,
                    'startDateGE'   =>  $GridLine->startDate,
                ));

                $killGridLineIds = array_keys( $killGridLines );
                $killGridLineIds = array_diff( $killGridLineIds, array($GridLine->gridLineId));

                if ( !empty( $killGridLineIds )) {
                    GridLineUtility::RebindGridLineItems(//..переписываем их итемы на новый цикл...
                        new DateTimeWrapper('01-01-1970'),
                        $killGridLineIds,
                        $GridLine->gridLineId
                    );

                    GridLineFactory::DeleteByMask( array(//.. и удаляем их
                       '_gridLineId' => $killGridLineIds,
                    ));
                }

                $GridLine->repeat = true;
                $GridLine->endDate = new DateTimeWrapper('3000-01-01');
            }

            $updateResult = GridLineFactory::Update($GridLine, array(BaseFactory::WithReturningKeys => true));
            if ($updateResult) {
                $result['success'] = true;
                $result['repeat'] = $GridLine->repeat;
            }

            echo ObjectHelper::ToJSON($result);
        }

        public function checkForExistingQueues( $targetFeedId, $dateFrom, $cellTime ) {
            $articlesQueue = ArticleQueueFactory::Get(
                array(
                    'targetFeedId' => $targetFeedId,
                    'startDateFrom' => $dateFrom
                )
                , array(
                    BaseFactory::WithoutPages => true,
                    BaseFactory::OrderBy => ' "startDate" ASC ',
                )
            );
            $queuedArticles = array();
            $i = 0;
            $wasBreak = false;
            $time = new DateTimeWrapper($cellTime->format('H:i:s'));
            foreach ($articlesQueue as $articlesQueueItem) {
                if ($time == new DateTimeWrapper($articlesQueueItem->startDate->format('H:i:s'))) {
                    $queuedArticles[]= $articlesQueueItem->startDate->format('j M');
                    $i++;
                    if ($i > 4) {
                        $wasBreak = true;
                        break;
                    }
                }
            }
            return $queuedArticles;
        }
    }
?>
