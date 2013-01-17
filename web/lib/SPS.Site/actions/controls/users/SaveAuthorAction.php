<?php
    /**
     * Save Author Action
     * 
     * @package SPS
     * @subpackage App
     * @property Author originalObject
     * @property Author currentObject
     */
    class SaveAuthorAction extends SaveVkUserAction  {
        
        /**
         * Constructor
         */
        public function __construct() {
            parent::__construct();
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
         * Add Object
         *
         * @param Author $object
         * @return bool
         */
        protected function add( $object ) {
            AuthorFactory::$mapping['view'] = 'authors';
            $exists = AuthorFactory::GetOne(array('vkId' => $object->vkId), array(BaseFactory::WithoutDisabled => false));

            if (empty($exists)) {
                $result = parent::$factory->Add($object);
            } else {
                //update
                $object->authorId = $exists->authorId;
                $result = parent::$factory->Update($object);
            }

            if ($result){
                $this->afterSaveAuthor($object);
            }
            
            return $result;
        }
    }
?>