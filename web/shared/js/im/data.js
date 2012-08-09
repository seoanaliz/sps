var Data = {
    users: [
        {
            id: 4718705,
            name: 'Artyom Kohver',
            photo: 'http://vk.com/images/camera_c.gif',
            isOnline: true
        }
    ],
    dialogs: [
        {
            id: 1,
            user: Data.users[0],
            lastMessage: {
                user: Data.users[0],
                text: 'Hello!',
                timestamp: 1234567890
            }
        }
    ],
    lists: [
        {
            id: 1,
            title: 'Item',
            icon: 'http://vk.com/images/camera_c.gif',
            dialogs: [
                {
                    id: 1,
                    user: Data.users[0]
                }
            ]
        }, {
            id: 2,
            title: 'Test',
            icon: 'http://vk.com/images/camera_c.gif',
            dialogs: [
                {
                    id: 1,
                    user: Data.users[0]
                }
            ]
        }
    ]
};