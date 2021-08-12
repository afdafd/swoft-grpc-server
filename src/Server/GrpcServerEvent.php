<?php declare(strict_types=1);


namespace Swoft\Grpc\Server;


class GrpcServerEvent
{
  /**
   * Before connect
   */
  public const BEFORE_CONNECT = 'swoft.grpc.server.connect.before';

  /**
   * Connect
   */
  public const CONNECT = 'swoft.grpc.server.connect';

  /**
   * After connect
   */
  public const AFTER_CONNECT = 'swoft.grpc.server.connect.after';

  /**
   * Before close
   */
  public const BEFORE_CLOSE = 'swoft.grpc.server.close.before';

  /**
   * Close
   */
  public const CLOSE = 'swoft.grpc.server.close';

  /**
   * After close
   */
  public const AFTER_CLOSE = 'swoft.grpc.server.close.after';

  /**
   * Before request
   */
  public const BEFORE_REQUEST = 'swoft.grpc.server.request.before';

  /**
   * After request
   */
  public const AFTER_REQUEST = 'swoft.grpc.server.request.after';
}
