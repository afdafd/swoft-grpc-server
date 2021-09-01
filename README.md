## swoft grpc server 端

### 1. 下载组件
````
composer下载：composer require hzwz/grpc-server dev-master
````

### 2. 创建Grpc和相关目录
````
1. 在 app/ 目录下创建一个Grpc目录
2. 在 Grpc/ 目录里分别创建protos和Services两个目录
````
- 说明：protos目录里是存放 *.proto相关文件。Services目录里是实现接口转换的
#### *.proto文件定义实例：
````text
syntax = "proto3";

package Brother;

service Agents {
  rpc initProfitPersonInfo(initProfitPersonRequest) returns(initProfitPersonResponse);  //把运营商初始化为默认的分成人员
  rpc getGroupIdsFromShare(getShareGroupListByShareUserRequest)  returns(getShareGroupListByShareUserResponse);  //根据合伙人ID返回对应的分组ID集
  rpc getCompositeUserInfo(getCompositeUserInfoRequest) returns(getCompositeUserInfoResponse);  //通过用户类型和手机号获取对应用户信息
}

message baseField {
    int64 u_id       = 1;
    int32 u_type     = 2;
    int32 company_id = 3;
}

//把运营商初始化为默认的分成人员
message initProfitPersonRequest {
    int32 group_id       = 1;
    baseField baseFields = 2;
}

message initProfitPersonResponse {}

//根据合伙人ID返回对应的分组ID集
message getShareGroupListByShareUserRequest {
  baseField baseFields = 1;
}

message getShareGroupListByShareUserResponse {
    repeated string data = 1;
}

//通过用户类型和手机号获取对应用户信息
message getCompositeUserInfoRequest {
  int32 u_type     = 1;
  int32 company_id = 2;
  string phone     = 3;
}

message getCompositeUserInfoResponse {
  string id         = 1;
  string nickname   = 2;
  string phone      = 3;
  string headImgUrl = 4;
}

````

- 通过定义好的*.proto文件生成实际的代码：

``protoc --php_out=. --grpc_out=. --plugin=protoc-gen-grpc=/usr/local/bin/grpc_php_plugin *.proto
``

````text  
  说明：
  1. --php_out 这个是生成php代码的一个指令
  2. --grpc_out 这个是生成php grpc客户端代码的指令
  3. 指令后面的 =. 这个是表示为：在当前目录下生成，也可以指定具体的目录。
  4. --plugin=protoc-gen-grpc=/usr/local/bin/grpc_php_plugin 
     这个是表示：生成grpc代码时所使用的grpc插件和对应插件所在的位置
  5. --proto_path 指令可以指定 *.proto文件的具体路径
  6. 除了--proto_path外还有一个别名 -I 和--proto_path作用是一样的
````
- 更详细的可以查看对应的文档：
- 1. https://segmentfault.com/a/1190000020386857
- 2. https://developers.google.com/protocol-buffers/docs/proto3
- 3. https://grpc.io/docs/languages/go/generated-code/
  
#### Servierce说明：

- 哪些服务类和接口是需要提供给兄弟服务调用的，可以在这个目下处理：
- 比如汽车桩用户中心里的：运营商，C端用户两个服务的一些接口需要提供给兄弟服务调用，目录定义参考如下：
````text 
   1. Services/Brother/OperatorService 运营商服务类
   2. Services/Brother/UserService     C端用户服务类
````

- 服务类定义好以后就需要定义服务路由了。GRPC路由和HTTP路由定义形式不太一样，所以单独的提供了一个 @GrpcService() 注解指令来设置GRPC路由。
- 设置格式是：包名+服务类名。如下：
````PHP
<?php

/**
 * @GrpcService(prefix="Brother/Agents")
 */
class BrotherService {}
````
- 说明：Brother就是在定义proto文件时指定的package包名，Agents就是proto里定义的service名。如下：

````text
syntax = "proto3";

//包名称（对应的实际就是PHP的namespace命名空间地址）
package Brother;

//rpc服务（就是客户端需要调用服务端的接口服务类）
service Agents {}
````
- 注：完整路由的处理大概逻辑是：获取到每个服务类的前缀路由后，然后通过PHP的反射动态的获取到每个类里的服务方法，最后在组装成实际的GRPC路由格式。

#### 通过protoc等相关指令生成grpc代码后，其客户端对应的路由格式如下：
````PHP
<?php

class AgentsClient extends \Grpc\BaseStub {
    public function initProfitPersonInfo(\Brother\AgentsRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/Brother.Agents/initProfitPersonInfo', //这个就是Grpc路由
        $argument,
        ['\Brother\AgentResponse', 'decode'],
        $metadata, $options);
    }
}
````
- /Brother.Agents/initProfitPersonInfo' 这个就是路由。最后生成的就是这个格式

#### 一个完整的GrpcService实例如下：
- 例：设备中心通过grpc方式调用汽车桩用户中心里的 [运营商初始化为默认的分成人员]接口：
````PHP
<?php

/**
 * Class BrotherService
 * @package App\Grpc\Services\Brother
 *
 * @GrpcService(prefix="Brother/Agents")
 */
class BrotherService
{
     /**
     * 把运营商初始化为默认的分成人员
     *
     * @Middleware(AddDefaultProfitValidationMiddleware::class)
     *
     * @param AgentsRequest $agentsRequest
     * @return AgentResponse
     */
    public function initProfitPersonInfo(AgentsRequest $agentsRequest): AgentResponse
    {
        //获取swoft/Reuqest类
        $request = context()->getRequest();
        
        //获取调用接口时所传递的参数（如果这里需要的话。不需要可以不用获取）
        $requestParams = $request->input();
       
        //调用实际控制器的方法，并传递Request类
        $result = bean(BrotherController::class)->initProfitPersonInfo($request);

        //初始化响应类（这个响应类是通过proto文件定义好，然后生成的响应类）
        $response = new AgentResponse();
        
        //设置响应参数（这里可能会有多个，我这里只是写了一个实例）
        $response->setData(json_encode($result, 1));

        //返回
        return $response;
    }
}
````

### Middleware说明

- 我们已经写好的Controller类里可能已经有实现自己的中间件，但是由于Http和Grpc的方式不一样，所以无法完全兼容。不过有一个折中的处理方式是：如果在Service服务类里在调用Controller类里的某个方法之前需要先去执行已实现的某个中间件里的代码时，可以在Service类上或Service类里的某个方法上定义一下@middleware()这个注解指令，引入的use路径是：use Hzwz\Grpc\Server\Annotation\Mapping\Middleware;然后把中间件类名称或者别名放进去就好了。
- 实例如下：

````PHP
<?php

use Hzwz\Grpc\Server\Annotation\Mapping\Middleware; //引入这个路径

class BrotherService
{
    /**
     * 把运营商初始化为默认的分成人员
     *
     * @Middleware(AddDefaultProfitValidationMiddleware::class) //这里定义
     *
     * @param AgentsRequest $agentsRequest
     * @return AgentResponse
     */
    public function initProfitPersonInfo(AgentsRequest $agentsRequest): AgentResponse {}
}
````
- 底层实现的是和Http同一个Middleware接口。

### 其他事件（例如listenr()等）都正常执行。

### thanks ^_^ 