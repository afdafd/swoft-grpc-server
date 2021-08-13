<?php declare(strict_types=1);

namespace Hzwz\Grpc\Server;


/**
 * Class GrpcHelper
 * @package Hzwz\Grpc\Server
 */
class GrpcHelper
{
  /**
   * 获取grpc请求的类方法
   *
   * @param string $path
   * @return false|string
   */
  public static function getRequestClassMethod(string $path)
  {
    $method = '';
    if (strripos($path, '/') !== false) {
      $method = substr($path, strripos($path, '/') + 1);
    }

    if (!empty($method)) {
      return lcfirst($method);
    }

    return $method;
  }
}
