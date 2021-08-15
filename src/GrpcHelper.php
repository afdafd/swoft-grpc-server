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

    return $method;
  }

  /**
   * 下划线转驼峰式
   *
   * @param string $words
   * @param string $separator
   * @return string
   */
  public static function camelize(string $words, string $separator = '_'): string
  {
    $words = $separator . str_replace($separator, " ", strtolower($words));
    return ltrim(str_replace(" ", "", ucwords($words)), $separator);
  }

  /**
   * json数据decode处理
   *
   * @param $data
   * @return array|mixed
   */
  public static function jsonDecodeHandle($data)
  {
    $jsonData = \json_decode($data, true);
    if (\json_last_error()) {
      return [\json_last_error_msg()];
    }

    return $jsonData;
  }
}
