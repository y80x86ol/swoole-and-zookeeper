## swoole and zookeeper

这是一个通过swoole和zookeeper实现的一个分布式消费端处理应用。

例如：消息队列的消费端，解决了单点故障。


### 功能如下

- 解决单点故障
- http模块
- ping模块
- cluster模块
- 工作节点均衡分布
- 集群节点选举
- 工作进程退出自动重启
- 剥离业务，可自我实现工作进程业务代码


### 功能在逐步完善，欢迎加入