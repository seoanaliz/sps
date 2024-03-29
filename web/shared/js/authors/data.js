var Data = {};

Data.users = [
    {
        id: 0,
        name: '...',
        photo: 'http://vk.com/images/camera_c.gif',
        isOnline: false
    }, {
        id: 1,
        name: 'Vanya',
        photo: 'http://vk.cc/S6Zn1',
        isOnline: true
    }, {
        id: 2,
        name: 'Zhenya',
        photo: 'http://vk.cc/S6ZWT',
        isOnline: true
    }, {
        id: 3,
        name: 'Petya',
        photo: 'http://vk.cc/S6ZDq',
        isOnline: true
    }, {
        id: 4,
        name: 'Vova',
        photo: 'http://vk.cc/S6ZM3',
        isOnline: true
    }
];

Data.messages = [
    {
        id: 1,
        text: 'Hello!!!',
        user: Data.users[0],
        timestamp: 1234567890
    }, {
        id: 2,
        text: 'Hello!!!',
        user: Data.users[2],
        timestamp: 1234567890
    }, {
        id: 4,
        text: 'Hello!!!',
        user: Data.users[0],
        timestamp: 1234567890
    }, {
        id: 5,
        text: 'Hello!!!',
        user: Data.users[1],
        timestamp: 1234567890
    }, {
        id: 6,
        text: 'Hello!!!',
        user: Data.users[3],
        timestamp: 1234567890
    }, {
        id: 7,
        text: 'Hello!!!',
        user: Data.users[1],
        timestamp: 1234567890
    }, {
        id: 8,
        text: 'Hello!!!',
        user: Data.users[1],
        timestamp: 1234567890
    }, {
        id: 9,
        text: 'Hello!!!',
        user: Data.users[1],
        timestamp: 1234567890
    }, {
        id: 10,
        text: 'Hello!!!',
        user: Data.users[1],
        timestamp: 1234567890
    }, {
        id: 11,
        text: 'Hello!!!',
        user: Data.users[1],
        timestamp: 1234567890
    }, {
        id: 12,
        text: 'Hello!!!',
        user: Data.users[1],
        timestamp: 1234567890,
        isNew: true
    }, {
        id: 3,
        text: 'Hello!!!',
        user: Data.users[4],
        timestamp: 1234567890,
        isNew: true
    }
];

Data.dialogs = [
    {
        id: 1,
        user: Data.users[0],
        lastMessage: Data.messages[0],
        isNew: true
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
        id: 999999,
        title: 'Не в списке'
    }, {
        id: 1,
        title: 'Друзья'
    }, {
        id: 2,
        title: 'Коллеги'
    }, {
        id: 3,
        title: 'Одноклассники'
    }
];
