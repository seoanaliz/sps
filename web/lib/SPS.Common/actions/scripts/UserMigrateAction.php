<?php
/**
 * User: x100up
 * Date: 09.12.12 12:45
 * In Code We Trust
 */
class UserMigrateAction extends BaseGetAction {

     public function Execute() {
         echo '<pre>';
         echo 'EDITORS -> AUTHORS <br/>';
         $this->fromEditorsToAuthors();
         echo 'AUTHORS -> EDITORS <br/>';
         $this->fromAuthorsToEditors();

         echo '</pre>';
     }

    private function fromEditorsToAuthors(){
        // из editors в authors с сохранением всех связанных сообществ (где был редактором, в приложении стал автором)
        $editors = EditorFactory::Get(array('pageSize' => 100000));
        foreach ($editors as $editor) {
            /** @var $editor Editor */
            $feeds = UserFeedFactory::GetForVkId($editor->vkId);
            $newAuthor = false;
            if (isset($feeds[UserFeed::ROLE_EDITOR])) {
                foreach ($feeds[UserFeed::ROLE_EDITOR] as $feed) {
                    /** @var $feed TargetFeed */
                    $authors = AuthorFactory::Get(array('vkId' => $editor->vkId, 'targetFeedIds' => $feed->targetFeedId));
                    if ($authors) {
                        echo $editor->getName(), '[', $editor->vkId, ']',' уже в таблице Authors для targetFeed[', $feed->targetFeedId ,']', PHP_EOL;
                    } else {
                        echo $editor->getName(), '[', $editor->vkId, ']',' нет в таблице Authors для targetFeed[', $feed->targetFeedId ,']', PHP_EOL;

                        if (!$newAuthor) {
                            $newAuthor = new Author();
                            $newAuthor->avatar = $editor->avatar;
                            $newAuthor->firstName = $editor->firstName;
                            $newAuthor->lastName = $editor->lastName;
                            $newAuthor->vkId = $editor->vkId;
                            $newAuthor->statusId = $editor->statusId;
                        }
                        $newAuthor->targetFeedIds[] = $feed->targetFeedId;
                    }
                }
            }

            if ($newAuthor) {
                echo $newAuthor->FullName(), ' добавлен ', PHP_EOL;
                AuthorFactory::Add($newAuthor);
                $newAuthor = false;
            }
        }
        unset($editors);
    }

    private function fromAuthorsToEditors(){
        /** @var $editor Editor */

        // из authors в editors с ограниченными правами доступа - Автор
        $authors = AuthorFactory::Get(array('pageSize' => 100000));
        foreach ($authors as $author) {
            /** @var $author Author */

            // находим редактора с тем же vkId
            $editor = EditorFactory::GetOne(array('vkId' => $author->vkId));

            if ($editor) {
                // если нашли
                echo $author->FullName(), ' уже есть в редакторах ', PHP_EOL;

                // берем все ленты автора и ставим роль
                // если роль уже есть - то выпадет варнинг
                foreach ($author->targetFeedIds as $targetFeedId) {
                    $UserFeed = new UserFeed();
                    $UserFeed->role = UserFeed::ROLE_AUTHOR;
                    $UserFeed->targetFeedId = $targetFeedId;
                    $UserFeed->vkId = $editor->vkId;
                    UserFeedFactory::Add($UserFeed);
                }

            } else {
                // если нет
                echo $author->FullName(), ' нет в редакторах ', PHP_EOL;
                // делаем нового
                $editor = new Editor();
                $editor->avatar = $author->avatar;
                $editor->firstName = $author->firstName;
                $editor->lastName = $author->lastName;
                $editor->vkId = $author->vkId;
                $editor->statusId = $author->statusId;
                $result = EditorFactory::Add($editor);
                // берем все ленты автора и ставим роль
                foreach ($author->targetFeedIds as $targetFeedId) {
                    $UserFeed = new UserFeed();
                    $UserFeed->role = UserFeed::ROLE_AUTHOR;
                    $UserFeed->targetFeedId = $targetFeedId;
                    $UserFeed->vkId = $editor->vkId;
                    UserFeedFactory::Add($UserFeed);
                }
            }

        }
    }
}
