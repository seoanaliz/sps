<?php
    Package::Load( 'SPS.Site' );
    
    /**
     * GetArticlesQueueTimelineControl Action
     * @package SPS
     * @subpackage Site
     * @author eugeneshulepin
     */
    class GetArticlesQueueTimelineControl extends BaseControl {

        /**
         * Колво ячеек, которое мы собираемся подгрузить
         */
        const TARGET_ITEMS_COUNT = 25;

        /**
         * Дата остчета в формате 'd.m.Y'
         * @var string
         */
        private $date;

        /**
         * Тип подгружаемой очереди
         * @var string
         */
        private $type;

        /**
         * Направление подгрузки
         * @var string
         */
        private $direction = 'down';

        /**
         * @var TargetFeed
         */
        private $targetFeed;

        /**
         * Сетка
         * @var array
         */
        private $grid = array();

        /**
         * Начальная дата сетки
         * @var DateTimeWrapper
         */
        private $startDate;

        /**
         * Конечная дата сетки
         * @var DateTimeWrapper
         */
        private $endDate;

        /**
         * @var ArticleQueue[]
         */
        public $articleQueues;

        /**
         * @var ArticleRecord[]
         */
        public $articleRecords;
   /**
         * @var ArticleRecord[]
         */
        public $repostArticleRecords;

        /**
         * Entry Point
         */
        public function Execute() {
            /**
             * request
             */
            $this->processRequest();

            /**
             * access
             */
            if (empty($this->targetFeed)) {
                return;
            }
            $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
            $role = $TargetFeedAccessUtility->getRoleForTargetFeed($this->targetFeed->targetFeedId);
            if (is_null($role)){
                return;
            }
            Response::setBoolean('canEditQueue', $role != UserFeed::ROLE_AUTHOR);

            /**
             * grid
             */
            $this->buildGrid();

            /**
             * Подставляем данные в ячейки
             */
            $this->setArticles();

            /**
             * response
             */
            Response::setArray('repostArticleRecords', $this->repostArticleRecords);
            Response::setArray('articleRecords', $this->articleRecords);
            Response::setArray('articlesQueue', $this->articleQueues);
            Response::setArray('gridData', $this->grid);
        }

        /**
         * Обрабатываем реквест
         */
        private function processRequest() {
            $this->type = Request::getString('type');
            if (empty($this->type) || empty(GridLineUtility::$Types[$this->type])) {
                $this->type = GridLineUtility::TYPE_ALL;
            }

            $this->direction = Request::getString('direction');
            if (empty($this->direction) || !in_array($this->direction, array('up', 'down', 'range'))) {
                $this->direction = 'down';
            }

            if ($this->direction === 'range') {
                $timestampBegin = Request::getString('timestampBegin');
                $timestampEnd = Request::getString('timestampEnd');
                if (!is_numeric($timestampBegin)) {
                    $timestampBegin = 0;
                }
                if (!is_numeric($timestampEnd)) {
                    $timestampEnd = 0;
                }
                $this->date = date('d.m.Y', $timestampBegin);
                $this->endDate = new DateTimeWrapper(date('d.m.Y', $timestampEnd));
            } else {
                $timestamp = Request::getInteger( 'timestamp' );
                if (!is_numeric($timestamp)) {
                    $timestamp = 0;
                }
                $this->date = date('d.m.Y', $timestamp);
            }
            $this->startDate = new DateTimeWrapper($this->date);
            
            $targetFeedId       = Request::getInteger( 'targetFeedId' );
            $this->targetFeed   = TargetFeedFactory::GetById($targetFeedId);
        }

        /**
         * Строим сетку
         * Зная направление и дату остчета попатаемся получить столько дней,
         * чтобы суммарное кол-во ячеек превысило пороговое значение
         */
        private function buildGrid() {
            $iterator = new DateTimeWrapper($this->date);

            $loadedCount = 0;
            while ($loadedCount < self::TARGET_ITEMS_COUNT) {
                // подгружаем сетку на конкретный день
                $grid = GridLineUtility::GetGrid($this->targetFeed->targetFeedId, $iterator->DefaultDateFormat(), $this->type);

                $timestamp = $iterator->format('U');
                $loadedCount += empty($grid) ? 1 : count($grid);
                switch ($this->direction) {
                    case 'up':
                        $this->grid = array($timestamp => $grid) + $this->grid;
                        $iterator->modify('+1 day');
                    break;
                
                    case 'range':
                        $this->grid = array($timestamp => $grid) + $this->grid;
                        if ($iterator >= $this->endDate) {
                            break 2; // ------------------------ BREAK
                        } else {
                            $iterator->modify('+1 day');
                        }
                    break;
                
                    default:
                        $this->grid = $this->grid + array($timestamp => $grid);
                        $iterator->modify('-1 day');
                }
            }

            $this->endDate = new DateTimeWrapper($iterator->DefaultDateFormat());
        }

        /**
         * Подставляем данные в ячейки
         */
        private function setArticles() {
            if ($this->direction == 'up') {
                $this->startDate->modify('-30 seconds');
                $this->endDate->modify('+1 day -31 seconds');
            } else {
                $this->startDate->modify('+1 day -31 seconds');
                $this->endDate->modify('-30 seconds');
            }

            //вытаскиваем очередь на полученную сетку
            $this->articleQueues = ArticleQueueFactory::Get(
                array(
                    'targetFeedId' => $this->targetFeed->targetFeedId,
                    'startDateFrom' => ($this->direction == 'up') ? $this->startDate : $this->endDate,
                    'startDateTo' => ($this->direction == 'up') ? $this->endDate : $this->startDate,
                    'type' => ($this->type == GridLineUtility::TYPE_ALL) ? null : $this->type,
                )
                , array(
                    BaseFactory::WithoutPages => true,
                    BaseFactory::OrderBy => ' "startDate" DESC ',
                )
            );

            if (empty($this->articleQueues)) {
                return;
            }

            //load articles data
            $articleIds = array();
            foreach ($this->articleQueues as $articlesQueue) {
                $articleIds[$articlesQueue->articleQueueId] = $articlesQueue->articleId;
            }

            $articles = $authorIds = array();
            if ($articleIds){
                $authorIds = array();
                ArticleFactory::$mapping['view'] = 'articles';
                foreach (ArticleFactory::Get(array('_articleId' => $articleIds), array(BaseFactory::WithoutPages => true, BaseFactory::WithoutDisabled => false)) as $article){
                    $articles[$article->articleId] = $article;
                    $authorIds[] = $article->authorId;
                }
            }

            if ($articles && $authorIds) {
                $authors = array();
                foreach (AuthorFactory::Get(array('_authorId' => $authorIds), array(BaseFactory::WithoutPages => true)) as $author){
                    $authors[$author->authorId] = $author;
                }

                foreach ($this->articleQueues as $articlesQueue) {

                    if (isset($articles[$articlesQueue->articleId])
                        && isset($authors[$articles[$articlesQueue->articleId]->authorId])) {
                        $articlesQueue->articleAuthor = $authors[$articles[$articlesQueue->articleId]->authorId];
                    }
                }
            }

            // load art queue editors
            $vkIds = array();
            foreach ($this->articleQueues as $articlesQueue) {
                if ($articlesQueue->author) {
                    $vkIds[] = $articlesQueue->author;
                }
            }

            if ($vkIds) {
                $editors = array();
                foreach (AuthorFactory::Get(array('vkIdIn' => $vkIds), array(BaseFactory::WithoutPages => true)) as $author) {
                    $editors[$author->vkId] = $author;
                }

                if ($editors) {
                    foreach ($this->articleQueues as $articlesQueue) {
                        if (isset($editors[$articlesQueue->author])) {
                            $articlesQueue->articleQueueCreator = $editors[$articlesQueue->author];
                        }
                    }
                }
            }

            // load art records
            $this->articleRecords = ArticleRecordFactory::Get(
                array('_articleQueueId' => array_keys($this->articleQueues))
            );
            if (!empty($this->articleRecords)) {
                $this->articleRecords = BaseFactoryPrepare::Collapse($this->articleRecords, 'articleQueueId', false);
                $repostArticleRecordsIds = array_filter( ArrayHelper::GetObjectsFieldValues($this->articleRecords, array('repostArticleRecordId')));
                if (!empty($repostArticleRecordsIds)) {
                    $repostRecords = ArticleRecordFactory::Get(
                        array('_articleRecordId' => array_unique($repostArticleRecordsIds))
                    );
                    $this->repostArticleRecords = BaseFactoryPrepare::Collapse($repostRecords, 'articleRecordId', false);
                }
            }



            /**
             * Распихиваем элементы в grid
             */
            $now = DateTimeWrapper::Now();
            foreach($this->articleQueues as $articlesQueueItem) {
                //ищем место в grid для текущей $articlesQueueItem
                $targetKey = null;
                $targetTimestamp = null;
                foreach ($this->grid as $timestamp => $gridData) {
                    foreach ($gridData as $key => $gridItem) {
                        if ($gridItem['dateTime'] >= $articlesQueueItem->startDate && $gridItem['dateTime'] <= $articlesQueueItem->endDate) {
                            if (empty($gridItem['queue'])) {
                                $targetTimestamp = $timestamp;
                                $targetKey = $key;
                            }
                        }
                    }
                }

                if ($targetKey !== null) {
                    $this->grid[$targetTimestamp][$targetKey]['queue'] = $articlesQueueItem;
                    $this->grid[$targetTimestamp][$targetKey]['blocked'] = ($articlesQueueItem->statusId != 1 || $articlesQueueItem->endDate <= $now);
                    $this->grid[$targetTimestamp][$targetKey]['failed'] = ($articlesQueueItem->statusId != StatusUtility::Finished && $articlesQueueItem->endDate <= $now);
                }
            }
        }
    }
?>