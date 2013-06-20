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

            if ($GridLine->repeat) {
                // ограничим repeat датой нажатой ячейки
                $dayPressed = date('Y-m-d', $timestamp); // возьмём только дату (отбросим время)
                $dt = new DateTimeWrapper($dayPressed);
                $dt->modify('+1 day');
                $dt->modify('-30 seconds');
                $yearToQueue = $dt->format('Y');
                if ($yearToQueue > 3000) { // не больше 3000-го года
                    echo ObjectHelper::ToJSON($result);
                    return false;
                }

                $articlesQueue = ArticleQueueFactory::Get(
                    array(
                        'targetFeedId' => $targetFeedId,
                        'startDateFrom' => $dt,
                        'type' => ($type == GridLineUtility::TYPE_ALL) ? null : $type,
                    )
                    , array(
                        BaseFactory::WithoutPages => true,
                        BaseFactory::OrderBy => ' "startDate" ASC ',
                    )
                );
                $queuedArticles = array();
                $i = 0;
                $wasBreak = false;
                foreach ($articlesQueue as $articlesQueueItem) {
                    $time = new DateTimeWrapper($GridLine->time->format('H:i:s'));
                    if ($time >= new DateTimeWrapper($articlesQueueItem->startDate->format('H:i:s')) &&
                        $time <= new DateTimeWrapper($articlesQueueItem->endDate->format('H:i:s'))
                    ) {
                        $queuedArticles []= $articlesQueueItem->startDate->format('j M');
                        $i++;
                        if ($i > 4) {
                            $wasBreak = true;
                            break;
                        }
                    }
                }

                if ($queuedArticles) {
                    $message = 'В этой ячейке в будущем есть запланированные посты: ' . join(', ', $queuedArticles) . ($wasBreak ? ' и т.д.' : '.');
                    $result = array(
                        'success' => false,
                        'message' => $message,
                    );
                    echo ObjectHelper::ToJSON($result);
                    return false; // -------------- RETURN
                }

                $GridLine->repeat = false;
                $GridLine->endDate = new DateTimeWrapper($dayPressed);
                $result['endDate'] = $GridLine->endDate->format('U');
            } else {
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
    }
?>