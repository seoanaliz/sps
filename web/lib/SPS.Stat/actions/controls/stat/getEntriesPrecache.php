<?php
    class getEntriesPrecache {

        /**
         * Entry Point
         */
        public function Execute() {
            $requestData = Page::$RequestData;
            $slug = isset($requestData[1]) ? $requestData[1] : null;

            $EntryGetter = new EntryGetter();
            $id = null;
            if ($slug) {
                if ($slug === "~update") {
                    $EntryGetter->updateSlugs();
                    die('done');
                }
                $id = $EntryGetter->getGroupIdBySlug($slug);
            }
            Request::setInteger('groupId', $id); // Нужно, т.к. EntryGetter зависит от глобального состояния (Request)
            Response::setString( 'entriesPrecache', ObjectHelper::ToJSON($EntryGetter->getEntriesData()) );
        }
    }
?>