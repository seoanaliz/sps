<?php
    Package::Load( 'SPS.Site' );

    /*
     * возвращает массив id постов, которые не нужно отображать в левой ленте на вкладке предложенных постов
     */
    class GetQueuedSuggestsControl extends BaseControl {

        /**
         * @var TargetFeedAccessUtility
         */
        private $TargetFeedAccessUtility;

        public function __construct()
        {
            parent::__construct();
            $this->TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        }

        /**
         * Entry Point
         */
        public function Execute() {
            $result = array('success' =>  false );

            $targetFeedId = Request::getInteger('targetFeedId');
            if( !$targetFeedId ) {
                $result['message'] = 'Wrong data';
                echo ObjectHelper::ToJSON($result);
                return;
            }
            if( !$this->TargetFeedAccessUtility->hasAccessToTargetFeed($targetFeedId)) {
                $result['message'] = 'Access denied';
                echo ObjectHelper::ToJSON($result);
                return;
            }

            $sql = <<<sql
                SELECT "externalId"
                FROM "articles"
                WHERE "targetFeedId" = @targetFeedId
                      AND  "statusId" = 2
                      AND "isSuggested" = true
                      AND "queuedAt" IS NOT NULL
                      and "sentAt"   IS NULL
sql;
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get());
            $cmd->SetInteger( '@targetFeedId', $targetFeedId);
            $ds = $cmd->Execute();
            $post_ids = array();
            while($ds->Next()) {
                $externalId = $ds->GetValue( 'externalId');
                $post_id = explode( '_', $externalId );
                if( isset( $post_id[1] )) {
                    $post_ids[] = $post_id[1];
                }
            }

            $result['success'] = true;
            $result['result']  = $post_ids;
            echo ObjectHelper::ToJSON($result);

        }
    }
?>