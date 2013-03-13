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
        private $old_userFeeds = array();

        public function __construct() {
            parent::__construct();
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

        protected function afterAction($result) {
            parent::afterAction($result);

            $targetFeedIds = Request::getArray('targetFeedIds');
            //стираем все роли юзера
            if (is_numeric( $this->currentObject->vkId) &&  $this->currentObject->vkId) {
                UserFeedFactory::DeleteForVkId( $this->currentObject->vkId );
            }
            $all_roles = array();
            foreach( $this->old_userFeeds as $omg ) {
                $all_roles = array_merge($all_roles, $omg );
            }
            $UserFeeds = array();
            foreach ($targetFeedIds as $targetFeedId ) {
                $UserFeed = new UserFeed();
                $UserFeed->role = UserFeed::ROLE_ADMINISTRATOR;
                $UserFeed->targetFeedId = $targetFeedId;
                $UserFeed->vkId = $this->currentObject->vkId;
                $UserFeeds[$targetFeedId] = $UserFeed;
            }

            //восстанавливаем для юзера неадминские роли
            foreach( $all_roles as $old_role ) {
                /** @var $old_role UserFeed*/
               if( !isset($UserFeeds[$old_role->targetFeedId]) && $old_role->role != UserFeed::ROLE_ADMINISTRATOR )
                   $UserFeeds[$old_role->targetFeedId] = $old_role;
            }

            if ($UserFeeds) {
                UserFeedFactory::AddRange($UserFeeds);
            }
            $this->setTargetFeedsList();
        }

        protected function beforeSave() {
            $this->setTargetFeedsList();
        }

        protected function setTargetFeedsList()
        {
            if( isset( $this->currentObject->vkId )) {
                //выбираем паблики, где юзер админит
                $this->old_userFeeds =  UserFeedFactory::GetForVkId( $this->currentObject->vkId );
                foreach( $this->old_userFeeds[UserFeed::ROLE_ADMINISTRATOR] as $userFeed ) {
                    $targetFeedsIds[$userFeed->targetFeedId] = $userFeed->targetFeedId;
                }
            }
            Response::setArray( 'targetFeedsIds', $targetFeedsIds );
        }

    }
?>