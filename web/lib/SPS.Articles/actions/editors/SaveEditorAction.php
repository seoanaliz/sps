<?php
    /**
     * Save Editor Action
     * 
     * @package SPS
     * @subpackage Articles
     * @property Editor originalObject
     * @property Editor currentObject
     */
    class SaveEditorAction extends SaveVkUserAction  {
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new EditorFactory();
        }

               
        /**
         * Form Object From Request
         *
         * @param Editor $originalObject 
         * @return Editor
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var Editor $object
             */
            $object = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $object->editorId = $originalObject->editorId;
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
         * @param Editor $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );
            
            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param Editor $object
         * @return bool
         */
        protected function add($object) {
            EditorFactory::$mapping['view'] = 'editors';
            $exists = EditorFactory::GetOne(array('vkId' => $object->vkId), array(BaseFactory::WithoutDisabled => false));

            if (empty($exists)) {
                $result = parent::$factory->Add( $object );
            } else {
                //update
                $object->editorId = $exists->editorId;
                $result = parent::$factory->Update( $object );
            }

            if ($result) {
                $this->afterSaveEditor($object);
            }
            
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param Editor $object
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