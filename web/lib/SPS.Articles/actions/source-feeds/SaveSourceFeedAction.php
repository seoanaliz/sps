<?php
    /**
     * Save SourceFeed Action
     * 
     * @package SPS
     * @subpackage Articles
     * @property SourceFeed originalObject
     * @property SourceFeed currentObject
     */
    class SaveSourceFeedAction extends BaseSaveAction  {

        public $onlyOursTargetFeeds ;
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new SourceFeedFactory();
            $this->onlyOursTargetFeeds = Request::getString('onlyOurs');
        }

               
        /**
         * Form Object From Request
         *
         * @param SourceFeed $originalObject 
         * @return SourceFeed
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var SourceFeed $SourceFeed
             */
            $SourceFeed = parent::$factory->GetFromRequest();

            if ( $originalObject != null ) {
                $SourceFeed->sourceFeedId = $originalObject->sourceFeedId;
                $SourceFeed->processed = $originalObject->processed;
            } else {
                $SourceFeed->processed = null;
            }

            $targetFeedIds = Request::getArray( 'targetFeedIds' );
            $targetFeedIds = !empty($targetFeedIds) ? $targetFeedIds : array();
            $SourceFeed->targetFeedIds = implode(',', $targetFeedIds);

            if (   $SourceFeed->type == SourceFeedUtility::Source
                || $SourceFeed->type == SourceFeedUtility::Topface) {
                // do nothing
            } elseif ($SourceFeed->type == SourceFeedUtility::Albums)   {
                preg_match('/(\d+)_(\d+)$/', $SourceFeed->externalId, $matches);
                if (count($matches) == 3) {
                    $SourceFeed->externalId = $matches[0];
                } else {
                    $SourceFeed->externalId = '-';
                }
            } else {
                $SourceFeed->externalId = '-';
            }

            return $SourceFeed;
        }
        
        
        /**
         * Validate Object
         *
         * @param SourceFeed $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );

            if (!empty($object->externalId) && ($object->externalId != '-')) {
                $duplicates = SourceFeedFactory::Count(
                    array('externalId' => $object->externalId),
                    array(BaseFactory::WithoutDisabled => false, BaseFactory::CustomSql => ' and "sourceFeedId" != ' . PgSqlConvert::ToString((int)$object->sourceFeedId))
                );

                if (!empty($duplicates)) {
                    $errors['fields']['externalId']['unique'] = 'unique';
                }
            }

            if ($object->type != SourceFeedUtility::Source && !empty($errors['fields']['externalId'])) {
                unset($errors['fields']['externalId']);
            }

            if (empty($errors['fields'])) {
                unset($errors['fields']);
            }

            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param SourceFeed $object
         * @return bool
         */
        protected function add( $object ) {
            $result = parent::$factory->Add( $object );
            
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param SourceFeed $object
         * @return bool
         */
        protected function update( $object ) {
            $result = parent::$factory->Update( $object );
            
            return $result;
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {
            Response::setBoolean( 'onlyOuers', $this->onlyOursTargetFeeds );
            $search = array();
            if( $this->onlyOursTargetFeeds ) {
                $search['isOur'] = true;
            }

            $targetFeeds = TargetFeedFactory::Get( $search, array( BaseFactory::WithoutDisabled => false ) );
            Response::setArray( 'targetFeeds', $targetFeeds );
        }

        protected function afterAction($result) {
            if ($result && $this->currentObject->type == SourceFeedUtility::Source) {
                SourceFeedUtility::SaveRemoteImage($this->currentObject->externalId);
            }
        }
    }
?>