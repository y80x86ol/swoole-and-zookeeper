<?php

return [
    //集群名称，只有相同集群的名称的节点才能加入同一个集群
    'cluster' => 'cluster5',

    //节点名称,如果节点名称冲突会随机生成一个节点名字
    'node' => 'node1',

    //zookeeper的连接地址
    'hosts' => 'localhost:2181',

    //zookeeper的acl权限
    'acl' => [
        [
            'perms' => \Zookeeper::PERM_ALL,
            'scheme' => 'world',
            'id' => 'anyone'
        ]
    ]
];