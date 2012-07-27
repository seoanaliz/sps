<?php
    /**
     * Save Author Action
     * 
     * @package SPS
     * @subpackage App
     * @property Author originalObject
     * @property Author currentObject
     */
    class SaveAuthorAction extends BaseSaveAction  {
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new AuthorFactory();
        }

               
        /**
         * Form Object From Request
         *
         * @param Author $originalObject 
         * @return Author
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var Author $object
             */
            $object = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $object->authorId = $originalObject->authorId;
            }

            //parse id from input
            $vkId = Request::getString( 'vkId' );
            preg_match('/\d+/im', $vkId, $matches);
            if (!empty($matches)) {
                $object->vkId = current($matches);
            }

            $object->targetFeedIds = Request::getArray( 'targetFeedIds' );

            try {
                if (!empty($object->vkId)) {
                    $profiles = VkAPI::GetInstance()->getProfiles(array('uids' => $object->vkId, 'fields' => 'photo'));
                    $profile = current($profiles);
                    $object->firstName = $profile['first_name'];
                    $object->lastName = $profile['last_name'];
                    $object->avatar = $profile['photo'];
                }
            } catch (Exception $Ex) {}

            return $object;
        }
        
        
        /**
         * Validate Object
         *
         * @param Author $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );
            
            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param Author $object
         * @return bool
         */
        protected function add( $object ) {
            $result = parent::$factory->Add( $object );
            
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param Author $object
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
            $targetFeeds = TargetFeedFactory::Get( null, array( BaseFactory::WithoutDisabled => false ) );
            Response::setArray( 'targetFeeds', $targetFeeds );
        }
    }
?>