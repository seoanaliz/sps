<?php
    /**
     * Save TargetFeed Action
     * 
     * @package SPS
     * @subpackage Articles
     * @property TargetFeed originalObject
     * @property TargetFeed currentObject
     */
    class SaveTargetFeedAction extends BaseSaveAction  {
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new TargetFeedFactory();
        }

               
        /**
         * Form Object From Request
         *
         * @param TargetFeed $originalObject 
         * @return TargetFeed
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var TargetFeed $object
             */
            $object = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $object->targetFeedId = $originalObject->targetFeedId;
            }

            $data = Request::getArray('targetFeed');
            if (empty($data['startTime'])) {
                $object->startTime = '09:00:00';
            }
            if (empty($object->period)) {
                $object->period = 60;
            }

            return $object;
        }
        
        
        /**
         * Validate Object
         *
         * @param TargetFeed $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );

            if (empty($errors['fields']['period'])) {
                if ($object->period <= 10 || $object->period >= 180) {
                    $errors['fields']['period']['periodVal'] = 'periodVal';
                }
            }
            
            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param TargetFeed $object
         * @return bool
         */
        protected function add( $object ) {
            $result = parent::$factory->Add( $object );
            
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param TargetFeed $object
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
            $publishers = PublisherFactory::Get( null, array( BaseFactory::WithoutPages => true ) );
            Response::setArray( "publishers", $publishers );
        }
    }
?>