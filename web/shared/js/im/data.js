var Data = {};

Data.users = [
    {
        id: 1,
        name: 'Vanya',
        photo: 'http://vk.com/images/camera_c.gif',
        isOnline: true
    }, {
        id: 2,
        name: 'Zhenya',
        photo: 'http://vk.com/images/camera_c.gif',
        isOnline: true
    }, {
        id: 3,
        name: 'Petya',
        photo: 'http://vk.com/images/camera_c.gif',
        isOnline: true
    }, {
        id: 4,
        name: 'Vova',
        photo: 'http://vk.com/images/camera_c.gif',
        isOnline: true
    }, {
        id: 4718705,
        name: 'Artyom Kohver',
        photo: 'http://vk.com/images/camera_c.gif',
        isOnline: true
    }
];

Data.messages = [
    {
        id: 1,
        text: 'Hello!!!',
        user: Data.users[0],
        timestamp: 1234567890
    }
];

Data.dialogs = [
    {
        id: 1,
        user: Data.users[0],
        lastMessage: Data.messages[0]
    }, {
        id: 2,
        user: Data.users[1],
        lastMessage: Data.messages[0]
    }, {
        id: 3,
        user: Data.users[2],
        lastMessage: Data.messages[0]
    }, {
        id: 4,
        user: Data.users[3],
        lastMessage: Data.messages[0]
    }, {
        id: 5,
        user: Data.users[4],
        lastMessage: Data.messages[0]
    }
];

Data.lists = [
    {
        id: 1,
        title: 'Друзья',
        icon: 'http://vk.com/images/camera_c.gif',
        dialogs: Data.dialogs
    }, {
        id: 2,
        title: 'Коллеги',
        icon: 'http://vk.com/images/camera_c.gif',
        dialogs: Data.dialogs
    }
];
